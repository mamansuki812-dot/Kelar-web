<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use App\Services\JurnalService;
use App\Services\StokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoriController extends Controller
{
    public function __construct(
        protected StokService $stokService,
        protected JurnalService $jurnalService
    ) {}
    /**
     * Tampilkan daftar stok produk (inventori).
     */
    public function index(Request $request)
    {
        $query = Produk::with('kategori')->where('is_active', true);

        // Filter Kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Filter Status Stok (normal: stok > stok_minimum, minimum: stok <= stok_minimum, habis: stok <= 0)
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'normal') {
                $query->where('stok', '>', \DB::raw('stok_minimum'));
            } elseif ($status === 'minimum') {
                $query->where('stok', '<=', \DB::raw('stok_minimum'))->where('stok', '>', 0);
            } elseif ($status === 'habis') {
                $query->where('stok', '<=', 0);
            }
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
                  ->orWhere('kode_produk', 'like', '%' . $search . '%');
            });
        }

        $produks = $query->orderBy('nama_produk')->paginate(20)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('inventori.index', compact('produks', 'kategoris'));
    }

    public function receive()
    {
        $produks = Produk::where('is_active', true)->orderBy('nama_produk')->get();
        $suppliers = \App\Models\Supplier::orderBy('nama_supplier')->get();
        return view('inventori.receive', compact('produks', 'suppliers'));
    }

    public function storeReceive(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'supplier_id' => 'required|exists:supplier,id',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        $supplier = \App\Models\Supplier::findOrFail($request->supplier_id);

        DB::transaction(function () use ($request, $supplier) {
            $produk = Produk::where('id', $request->produk_id)->lockForUpdate()->first();

            $history = $this->stokService->tambah(
                produk: $produk,
                jumlah: $request->jumlah,
                userId: \Illuminate\Support\Facades\Auth::id(),
                keterangan: 'Penerimaan dari ' . $supplier->nama_supplier . ($request->keterangan ? ' - ' . $request->keterangan : '')
            );

            $this->jurnalService->catatPenerimaanBarang(
                history: $history,
                totalNilai: (float) $produk->harga_beli * (int) $request->jumlah,
                metodeBayar: 'utang'
            );
        });

        return redirect()->route('inventori.index')->with('success', 'Stok berhasil ditambahkan dari penerimaan supplier.');
    }

    public function adjust()
    {
        $produks = Produk::where('is_active', true)->orderBy('nama_produk')->get();
        return view('inventori.adjust', compact('produks'));
    }

    public function storeAdjust(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'jenis' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $produk = Produk::where('id', $request->produk_id)->lockForUpdate()->first();

                $stokBaru = $request->jenis === 'masuk'
                    ? $produk->stok + $request->jumlah
                    : $produk->stok - $request->jumlah;

                if ($stokBaru < 0) {
                    throw new \Exception('Penyesuaian gagal! Stok tidak boleh kurang dari 0.');
                }

                $history = $this->stokService->sesuaikan(
                    produk: $produk,
                    stokBaru: $stokBaru,
                    userId: \Illuminate\Support\Facades\Auth::id(),
                    keterangan: 'Penyesuaian Manual: ' . $request->keterangan
                );

                $this->jurnalService->catatPenyesuaianStok($history);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('inventori.index')->with('success', 'Penyesuaian stok berhasil disimpan.');
    }

    public function history(Request $request)
    {
        $query = \App\Models\StokHistory::with(['produk', 'user'])->orderByDesc('created_at');
        
        if ($request->filled('produk_id')) {
            $query->where('produk_id', $request->produk_id);
        }
        
        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }
        
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('created_at', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->tanggal_akhir);
        }

        $histories = $query->paginate(20)->withQueryString();
        $produks = Produk::orderBy('nama_produk')->get();
        
        return view('inventori.history', compact('histories', 'produks'));
    }
}
