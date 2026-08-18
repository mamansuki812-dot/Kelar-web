<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::withCount(['produk' => function ($q) {
            $q->where('is_active', true);
        }])->orderBy('nama_kategori')->paginate(15);

        return view('kategori.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori,nama_kategori',
            'deskripsi'     => 'nullable|string|max:500',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah digunakan.',
            'nama_kategori.max'      => 'Nama kategori maksimal 100 karakter.',
        ]);

        Kategori::create($validated);

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori "' . $validated['nama_kategori'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:100|unique:kategori,nama_kategori,' . $kategori->id,
            'deskripsi'     => 'nullable|string|max:500',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah digunakan oleh kategori lain.',
        ]);

        $kategori->update($validated);

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori "' . $kategori->nama_kategori . '" berhasil diperbarui.');
    }

    public function destroy(Kategori $kategori)
    {
        // Validasi: kategori yang masih dipakai produk aktif tidak boleh dihapus (US-003 AC-2)
        if ($kategori->masihDipakai()) {
            return redirect()->route('kategori.index')
                ->with('error', 'Kategori "' . $kategori->nama_kategori . '" tidak dapat dihapus karena masih digunakan oleh produk aktif.');
        }

        $nama = $kategori->nama_kategori;
        $kategori->delete();

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori "' . $nama . '" berhasil dihapus.');
    }
}
