<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Services\MidtransService;
use App\Services\TransaksiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
        protected TransaksiService $transaksiService,
    ) {}

    /**
     * [AJAX, auth kasir/admin] Inisiasi pembayaran online dari POS.
     * Buat transaksi pending (status 'pending', belum kurangi stok/jurnal),
     * lalu minta snap_token ke Midtrans Snap API.
     */
    public function createTransaction(Request $request)
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

        if (!$this->midtransService->isConfigured()) {
            return response()->json([
                'message' => 'Pembayaran online belum dikonfigurasi. Hubungi administrator.',
            ], 503);
        }

        $validated = $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.produk_id'    => 'required|integer|exists:produk,id',
            'items.*.jumlah'       => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.tipe_diskon'  => 'sometimes|nullable|in:nominal,persen,free-packaging',
            'items.*.nilai_diskon' => 'sometimes|nullable|numeric|min:0',
            'diskon'               => 'sometimes|numeric|min:0',
        ], [
            'items.required' => 'Keranjang tidak boleh kosong.',
            'items.min'      => 'Minimal 1 item di keranjang.',
        ]);

        // Diskonto dihitung ulang server-side dari aturan_diskon aktif (anti manipulasi).
        $items = $this->transaksiService->normalkanDiskonOtomatis($validated['items']);
        $totalDiskon = collect($items)->sum(fn($i) => $this->transaksiService->hitungDiskonEfektif($i));

        try {
            // Simpan pending transaksi + detail (atomik, validasi stok + locking)
            $transaksi = $this->transaksiService->createPendingTransaksi(
                items: $items,
                diskon: $totalDiskon,
                user_id: Auth::id()
            );

            // Minta snap_token ke Midtrans API (di luar DB transaction)
            $snapToken = $this->midtransService->createSnapToken(
                transactionDetails: [
                    'order_id'     => $transaksi->kode_transaksi,
                    'gross_amount' => (int) round($transaksi->total_bayar),
                ],
                itemDetails: $transaksi->details->map(fn($d) => [
                    'id'       => (string) $d->produk_id,
                    'price'    => (int) round($d->harga_satuan),
                    'quantity' => $d->jumlah,
                    'name'     => $d->produk->nama_produk,
                ])->values()->all(),
                customerName: $transaksi->user->name
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'snap_token'     => $snapToken,
            'kode_transaksi' => $transaksi->kode_transaksi,
            'total_bayar'    => $transaksi->total_bayar,
        ], 201);
    }

    /**
     * [AJAX, auth kasir/admin] Cek status pembayaran online.
     * Dipanggil setelah callback onSuccess Snap.js. WAJIB re-check ke Midtrans API
     * (jangan percaya parameter dari browser), finalisasi + baru izinkan struk
     * jika status API = settlement/capture. Idempoten.
     */
    public function status(Request $request)
    {
        $orderId = $request->query('order_id');
        if (!$orderId) {
            return response()->json(['message' => 'Parameter order_id wajib diisi.'], 422);
        }

        $transaksi = Transaksi::where('midtrans_order_id', $orderId)->first();
        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        // Sudah dibatalkan → langsung kembalikan tanpa panggil API
        if ($transaksi->status === 'dibatalkan') {
            return response()->json([
                'status'          => 'dibatalkan',
                'midtrans_status' => $transaksi->midtrans_status,
                'message'         => 'Transaksi dibatalkan.',
            ]);
        }

        // Sudah final (dari webhook sebelumnya) → langsung kembalikan data struk
        if ($transaksi->status === 'selesai') {
            return response()->json($this->buildReceipt($transaksi));
        }

        // Re-check ke Midtrans API
        try {
            $remote = $this->midtransService->getStatus($orderId);
        } catch (\Exception $e) {
            Log::warning('Gagal re-check status Midtrans', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return response()->json([
                'status'  => $transaksi->status,
                'message' => 'Gagal mengecek status pembayaran. Silakan coba lagi.',
            ], 502);
        }

        $remoteStatus = $remote['transaction_status'] ?? null;
        $fraudStatus  = $remote['fraud_status'] ?? null;
        $paymentType  = $remote['payment_type'] ?? null;
        $txId         = $remote['transaction_id'] ?? null;

        $mapped = $this->midtransService->mapTransactionStatus($remoteStatus ?? '');
        if ($fraudStatus === 'reject') {
            $mapped = 'dibatalkan';
        }

        if ($mapped === 'selesai') {
            // Finalisasi idempoten + kurangi stok & jurnal
            $final = $this->transaksiService->finalisasiPembayaranOnline($transaksi, $paymentType ?? '', $txId ?? '');
            return response()->json($this->buildReceipt($final));
        }

        if ($mapped === 'dibatalkan') {
            DB::transaction(function () use ($transaksi) {
                $locked = Transaksi::whereKey($transaksi->id)->lockForUpdate()->first();
                if ($locked && $locked->status === 'pending') {
                    $locked->update(['status' => 'dibatalkan', 'midtrans_status' => 'dibatalkan']);
                }
            });
        } else {
            $transaksi->update(['midtrans_status' => 'pending']);
        }

        return response()->json([
            'status'          => $transaksi->fresh()->status,
            'midtrans_status' => $remoteStatus,
            'message'         => 'Pembayaran belum selesai.',
        ]);
    }

    /**
     * [No auth, CSRF exempt] Webhook notifikasi dari Midtrans.
     * Verifikasi SHA512 signature → cari transaksi by order_id → finalisasi
     * (stok + jurnal) HANYA saat settlement/capture. Selalu return 200 untuk
     * idempotency/retry. Fraud status reject → batalkan.
     */
    public function webhook(Request $request)
    {
        $payload   = json_decode($request->getContent(), true) ?: [];
        $signature = $request->header('X-Signature-Key');

        if (!$this->midtransService->verifySignature($payload, $signature)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        $orderId            = $payload['order_id'] ?? null;
        $transactionStatus  = $payload['transaction_status'] ?? null;
        $fraudStatus        = $payload['fraud_status'] ?? null;
        $grossAmount        = $payload['gross_amount'] ?? null;
        $paymentType        = $payload['payment_type'] ?? null;
        $midtransTransactionId = $payload['transaction_id'] ?? null;

        if (!$orderId || !$transactionStatus) {
            return response()->json(['status' => 'error', 'message' => 'Missing required fields'], 422);
        }

        $transaksi = Transaksi::where('midtrans_order_id', $orderId)->first();
        if (!$transaksi) {
            Log::warning('Midtrans webhook: order_id tidak dikenal', ['order_id' => $orderId]);
            return response()->json(['status' => 'ok']);
        }

        // Anti-tampering: gross_amount payload harus sama dengan total_bayar
        if (abs((float) $grossAmount - (float) $transaksi->total_bayar) > 0.01) {
            Log::warning('Midtrans webhook: gross_amount mismatch', [
                'order_id' => $orderId,
                'payload'  => $grossAmount,
                'db'       => $transaksi->total_bayar,
            ]);
            return response()->json(['status' => 'ok', 'message' => 'amount mismatch ignored']);
        }

        $mapped = $this->midtransService->mapTransactionStatus($transactionStatus);
        if ($fraudStatus === 'reject') {
            $mapped = 'dibatalkan';
        }

        try {
            if ($mapped === 'selesai') {
                // Kurangi stok + jurnal (idempoten via guard status di service)
                $this->transaksiService->finalisasiPembayaranOnline($transaksi, $paymentType ?? '', $midtransTransactionId ?? '');
            } elseif ($mapped === 'dibatalkan') {
                DB::transaction(function () use ($transaksi) {
                    $locked = Transaksi::whereKey($transaksi->id)->lockForUpdate()->first();
                    if ($locked && $locked->status === 'pending') {
                        $locked->update(['status' => 'dibatalkan', 'midtrans_status' => 'dibatalkan']);
                    }
                });
            } else {
                // pending/challenge — hanya catat snapshot, tanpa stok/jurnal
                $transaksi->update(['midtrans_status' => 'pending']);
            }
        } catch (\Exception $e) {
            Log::error('Midtrans webhook: gagal proses', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'internal error'], 500);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Susun payload struk (format sama dengan response TransaksiController::store)
     * agar tampilStruk() di POS bisa dipakai ulang.
     */
    private function buildReceipt(Transaksi $transaksi): array
    {
        $transaksi->load('details.produk', 'user');

        return [
            'kode_transaksi'    => $transaksi->kode_transaksi,
            'tanggal_transaksi' => $transaksi->tanggal_transaksi,
            'nama_kasir'        => $transaksi->user?->name,
            'total_harga'       => $transaksi->total_harga,
            'diskon'            => $transaksi->diskon,
            'total_bayar'       => $transaksi->total_bayar,
            'jumlah_bayar'      => $transaksi->jumlah_bayar,
            'kembalian'         => $transaksi->kembalian,
            'metode_pembayaran' => $transaksi->metode_pembayaran,
            'details'           => $transaksi->details->map(fn($d) => [
                'nama_produk' => $d->produk?->nama_produk,
                'jumlah'      => $d->jumlah,
                'harga_satuan'=> $d->harga_satuan,
                'subtotal'    => $d->subtotal,
            ])->values(),
        ];
    }
}
