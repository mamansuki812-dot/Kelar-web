<?php

namespace App\Http\Controllers;

use App\Models\AturanDiskon;
use App\Models\Produk;
use Illuminate\Http\Request;

class AturanDiskonController extends Controller
{
    /**
     * Daftar aturan diskon (admin).
     */
    public function index()
    {
        $aturanDiskons = AturanDiskon::with('produk')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('aturan-diskon.index', compact('aturanDiskons'));
    }

    /**
     * Tampilkan form tambah aturan diskon.
     */
    public function create()
    {
        $produks = Produk::orderBy('nama_produk')->get();
        return view('aturan-diskon.create', compact('produks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'produk_id'      => 'required|exists:produk,id',
            'tipe_diskon'    => 'required|in:nominal,persen,free-packaging',
            'nilai_diskon'   => 'required|numeric|min:0',
            'tanggal_mulai'  => 'nullable|date',
            'tanggal_selesai'=> 'nullable|date|after_or_equal:tanggal_mulai',
            'is_active'      => 'sometimes|boolean',
        ]);

        if ($validated['tipe_diskon'] === 'persen' && $validated['nilai_diskon'] > 100) {
            return back()->withErrors(['nilai_diskon' => 'Diskon persen maksimal 100%.'])->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active');
        AturanDiskon::create($validated);

        return redirect()->route('aturan-diskon.index')
            ->with('success', 'Aturan diskon berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit aturan diskon.
     */
    public function edit(AturanDiskon $aturanDiskon)
    {
        $produks = Produk::orderBy('nama_produk')->get();
        return view('aturan-diskon.edit', compact('aturanDiskon', 'produks'));
    }

    public function update(Request $request, AturanDiskon $aturanDiskon)
    {
        $validated = $request->validate([
            'produk_id'      => 'required|exists:produk,id',
            'tipe_diskon'    => 'required|in:nominal,persen,free-packaging',
            'nilai_diskon'   => 'required|numeric|min:0',
            'tanggal_mulai'  => 'nullable|date',
            'tanggal_selesai'=> 'nullable|date|after_or_equal:tanggal_mulai',
            'is_active'      => 'sometimes|boolean',
        ]);

        if ($validated['tipe_diskon'] === 'persen' && $validated['nilai_diskon'] > 100) {
            return back()->withErrors(['nilai_diskon' => 'Diskon persen maksimal 100%.'])->withInput();
        }

        $validated['is_active'] = $request->boolean('is_active');
        $aturanDiskon->update($validated);

        return redirect()->route('aturan-diskon.index')
            ->with('success', 'Aturan diskon berhasil diperbarui.');
    }

    public function destroy(AturanDiskon $aturanDiskon)
    {
        $aturanDiskon->delete();
        return redirect()->route('aturan-diskon.index')
            ->with('success', 'Aturan diskon berhasil dihapus.');
    }

    /**
     * [AJAX] Endpoint POS: cek aturan diskon aktif untuk sebuah produk.
     * Dipakai setelah scan barcode / tambah produk, agar keranjang otomatis
     * mendapat tipe_diskon + nilai_diskon bila ada aturan yang berlaku.
     */
    public function cekAktif(Request $request)
    {
        $produkId = $request->query('produk_id');
        if (!$produkId) {
            return response()->json(['aturan' => null]);
        }

        $aturan = AturanDiskon::where('produk_id', $produkId)
            ->where('is_active', true)
            ->get()
            ->first(fn($a) => $a->isBerlaku());

        if (!$aturan) {
            return response()->json(['aturan' => null]);
        }

        return response()->json([
            'aturan' => [
                'tipe_diskon'  => $aturan->tipe_diskon,
                'nilai_diskon' => (float) $aturan->nilai_diskon,
            ],
        ]);
    }
}