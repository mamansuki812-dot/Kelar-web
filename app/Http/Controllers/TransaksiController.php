<?php

namespace App\Http\Controllers;

use App\Services\TransaksiService;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function __construct(
        protected TransaksiService $transaksiService
    ) {}

    /**
     * Simpan transaksi baru dari halaman POS (dipanggil via AJAX/JSON).
     * Mengembalikan JSON dengan data transaksi + struk.
     */
    public function store(Request $request)
    {
        // Guard FASE 1: kasir wajib punya shift 'buka' aktif (admin dikecualikan).
        if (Auth::user()->role === 'kasir') {
            $shift = app(\App\Services\ShiftKasirService::class)->shiftAktif(Auth::id());
            if ($shift === null) {
                return response()->json([
                    'message' => 'Anda belum membuka shift kasir. Buka shift terlebih dahulu untuk mulai bertransaksi.',
                    'redirect_shift' => route('shift.buka'),
                ], 403);
            }
        }

        $validated = $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.produk_id'    => 'required|integer|exists:produk,id',
            'items.*.jumlah'       => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.tipe_diskon'  => 'sometimes|nullable|in:nominal,persen,free-packaging',
            'items.*.nilai_diskon' => 'sometimes|nullable|numeric|min:0',
            'diskon'               => 'sometimes|numeric|min:0',
            'metode_pembayaran'    => 'required|in:tunai,transfer,qris',
            'jumlah_bayar'         => 'required|numeric|min:0',
            'relakan_kembalian'    => 'sometimes|boolean',
        ], [
            'items.required'    => 'Keranjang tidak boleh kosong.',
            'items.min'         => 'Minimal 1 item di keranjang.',
            'metode_pembayaran' => 'Metode pembayaran tidak valid.',
        ]);

        // Diskonto selalu dihitung ulang server-side dari aturan_diskon aktif
        // (nilai diskon dari browser diabaikan — anti manipulasi request).
        $items = $this->transaksiService->normalkanDiskonOtomatis($validated['items']);

        // Hitung total_bayar untuk validasi jumlah_bayar (header diskon = agregat diskon baris)
        $totalHarga  = collect($items)->sum(fn($i) => $i['harga_satuan'] * $i['jumlah']);
        $totalDiskon = collect($items)->sum(fn($i) => $this->transaksiService->hitungDiskonEfektif($i));
        $totalBayar  = max(0, $totalHarga - $totalDiskon);

        if ($validated['jumlah_bayar'] <= 0) {
            return response()->json([
                'message' => 'Jumlah bayar harus lebih dari 0.',
            ], 422);
        }

        if ($validated['metode_pembayaran'] === 'tunai' && $validated['jumlah_bayar'] < $totalBayar) {
            return response()->json([
                'message' => 'Jumlah bayar tunai tidak boleh kurang dari total. Total: Rp ' . number_format($totalBayar, 0, ',', '.') . ', Diterima: Rp ' . number_format($validated['jumlah_bayar'], 0, ',', '.'),
            ], 422);
        }

        try {
            $transaksi = $this->transaksiService->proses(
                items: $items,
                diskon: $totalDiskon,
                metode_pembayaran: $validated['metode_pembayaran'],
                jumlah_bayar: $validated['jumlah_bayar'],
                user_id: Auth::id(),
                relakanKembalian: (bool) ($validated['relakan_kembalian'] ?? false)
            );

            // Susun response untuk struk
            return response()->json([
                'kode_transaksi'    => $transaksi->kode_transaksi,
                'tanggal_transaksi' => $transaksi->tanggal_transaksi,
                'nama_kasir'        => $transaksi->user->name,
                'total_harga'       => $transaksi->total_harga,
                'diskon'            => $transaksi->diskon,
                'total_bayar'       => $transaksi->total_bayar,
                'jumlah_bayar'      => $transaksi->jumlah_bayar,
                'kembalian'         => $transaksi->kembalian,
                'donasi'            => $transaksi->donasi,
                'metode_pembayaran' => $transaksi->metode_pembayaran,
                'details'           => $transaksi->details->map(fn($d) => [
                    'nama_produk' => $d->produk->nama_produk,
                    'jumlah'      => $d->jumlah,
                    'harga_satuan'=> $d->harga_satuan,
                    'subtotal'    => $d->subtotal,
                ]),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Tampilkan riwayat transaksi (kasir hanya melihat transaksinya sendiri).
     */
    public function index(Request $request)
    {
        $query = Transaksi::with('user')->orderByDesc('tanggal_transaksi');

        if (Auth::user()->role === 'kasir') {
            $query->where('user_id', Auth::id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_transaksi', $request->tanggal);
        }

        $transaksis = $query->paginate(20)->withQueryString();

        return view('transaksi.index', compact('transaksis'));
    }

    /**
     * Detail satu transaksi (untuk mencetak ulang struk).
     */
    public function show(Transaksi $transaksi)
    {
        // Kasir hanya boleh lihat transaksi miliknya
        if (Auth::user()->role === 'kasir' && $transaksi->user_id !== Auth::id()) {
            abort(403);
        }

        $transaksi->load('details.produk', 'user');
        return view('transaksi.show', compact('transaksi'));
    }

    /**
     * [AJAX] Batalkan transaksi pending Midtrans.
     * RBAC: admin boleh semua; kasir hanya transaksi miliknya.
     * Hanya mengubah status DB → 'dibatalkan' (tidak memanggil API cancel Midtrans).
     */
    public function batalPending(Transaksi $transaksi)
    {
        $user = Auth::user();
        if ($user->role === 'kasir' && $transaksi->user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak berhak membatalkan transaksi ini.'], 403);
        }

        try {
            $this->transaksiService->batalkanPending($transaksi);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Transaksi pending berhasil dibatalkan.'], 200);
    }
}
