<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::with('kategori');

        // Filter pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('kode_produk', 'like', "%{$search}%");
            });
        }

        // Filter kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $produks   = $query->orderBy('nama_produk')->paginate(15)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        return view('produk.index', compact('produks', 'kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->aturanValidasi(), $this->pesanValidasi());

        // Upload gambar
        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        $validated['is_active'] = true;

        Produk::create($validated);

        return redirect()->route('produk.index')
            ->with('success', 'Produk "' . $validated['nama_produk'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Produk $produk)
    {
        $rules = $this->aturanValidasi();
        // Exception: kode_produk boleh sama dengan diri sendiri saat edit
        $rules['kode_produk'] = 'required|string|max:50|unique:produk,kode_produk,' . $produk->id;

        $validated = $request->validate($rules, $this->pesanValidasi());

        // Upload gambar baru (hapus yang lama jika ada)
        if ($request->hasFile('gambar')) {
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            $validated['gambar'] = $request->file('gambar')->store('produk', 'public');
        }

        $produk->update($validated);

        return redirect()->route('produk.index')
            ->with('success', 'Produk "' . $produk->nama_produk . '" berhasil diperbarui.');
    }

    /**
     * Aturan validasi produk (store/update). harga_jual WAJIB > 0 (min:1)
     * agar produk harga Rp0 tidak bisa tersimpan lagi.
     */
    private function aturanValidasi(): array
    {
        return [
            'kode_produk'  => 'required|string|max:50|unique:produk,kode_produk',
            'nama_produk'  => 'required|string|max:150',
            'kategori_id'  => 'required|exists:kategori,id',
            'harga_beli'   => 'required|numeric|min:0',
            'harga_jual'   => 'required|numeric|min:1',
            'stok'         => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'satuan'       => 'required|string|max:20',
            'gambar'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Pesan error per-field dalam Bahasa Indonesia yang jelas.
     */
    private function pesanValidasi(): array
    {
        return [
            'kode_produk.required'  => 'Kode produk wajib diisi.',
            'kode_produk.max'       => 'Kode produk maksimal 50 karakter.',
            'kode_produk.unique'    => 'Kode produk sudah dipakai produk lain.',
            'nama_produk.required'  => 'Nama produk wajib diisi.',
            'nama_produk.max'       => 'Nama produk maksimal 150 karakter.',
            'kategori_id.required'  => 'Pilih kategori produk.',
            'kategori_id.exists'    => 'Kategori yang dipilih tidak ditemukan.',
            'harga_beli.required'   => 'Harga beli wajib diisi.',
            'harga_beli.numeric'    => 'Harga beli harus berupa angka.',
            'harga_beli.min'        => 'Harga beli tidak boleh negatif.',
            'harga_jual.required'   => 'Harga jual wajib diisi.',
            'harga_jual.numeric'    => 'Harga jual harus berupa angka.',
            'harga_jual.min'        => 'Harga jual harus lebih dari 0 (tidak boleh 0).',
            'stok.required'         => 'Stok awal wajib diisi.',
            'stok.integer'          => 'Stok harus berupa bilangan bulat.',
            'stok.min'              => 'Stok tidak boleh negatif.',
            'stok_minimum.required' => 'Stok minimum wajib diisi.',
            'stok_minimum.integer'  => 'Stok minimum harus berupa bilangan bulat.',
            'stok_minimum.min'      => 'Stok minimum tidak boleh negatif.',
            'satuan.required'       => 'Satuan wajib diisi.',
            'satuan.max'            => 'Satuan maksimal 20 karakter.',
            'gambar.image'          => 'File harus berupa gambar.',
            'gambar.mimes'          => 'Format gambar harus jpg, jpeg, atau png.',
            'gambar.max'            => 'Ukuran gambar maksimal 2 MB.',
        ];
    }

    /**
     * Toggle aktif/nonaktif produk (US-004 AC-2: tanpa hapus riwayat transaksi).
     */
    public function toggle(Produk $produk)
    {
        $produk->update(['is_active' => !$produk->is_active]);
        $status = $produk->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('produk.index')
            ->with('success', 'Produk "' . $produk->nama_produk . '" berhasil ' . $status . '.');
    }

    /**
     * Live search endpoint — dipakai oleh halaman POS dan scanner barcode (US-006, US-030, US-031).
     * Tidak perlu route baru; barcode scan hardware & kamera memakai ulang endpoint ini.
     */
    public function search(Request $request)
    {
        $query = Produk::with('kategori')
            ->where('is_active', true);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qb) use ($q) {
                $qb->where('nama_produk', 'like', '%' . $q . '%')
                   ->orWhere('kode_produk', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $produk = $query->orderBy('nama_produk')->limit(50)->get();

        // Sertakan aturan diskon yang sedang berlaku (Fase 2) agar keranjang POS
        // otomatis menerapkan tipe_diskon + nilai_diskon tanpa request tambahan.
        $produk->load([
            'aturanDiskon' => fn($q) => $q->where('is_active', true),
        ]);
        $produk->each(function ($p) {
            $aktif = $p->aturanDiskon->first(fn($a) => $a->isBerlaku());
            $p->aturan_diskon_aktif = $aktif ? [
                'tipe_diskon'  => $aktif->tipe_diskon,
                'nilai_diskon' => (float) $aktif->nilai_diskon,
            ] : null;
            unset($p->aturanDiskon);
        });

        return response()->json($produk);
    }
}
