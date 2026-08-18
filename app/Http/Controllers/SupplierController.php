<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        return view('supplier.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email|max:255',
        ]);

        Supplier::create($validated);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'kontak' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email|max:255',
        ]);

        $supplier->update($validated);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('supplier.index')->with('error', 'Supplier gagal dihapus karena mungkin masih digunakan dalam transaksi stok.');
        }
    }
}
