<?php

namespace App\Http\Controllers;

use App\Models\AsetTetap;
use App\Models\Akun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AsetTetapController extends Controller
{
    /**
     * Daftar aset tetap.
     */
    public function index(Request $request)
    {
        $query = AsetTetap::with('user')->orderByDesc('tanggal_perolehan');

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        $asets = $query->paginate(15)->withQueryString();

        $totalHarga    = AsetTetap::sum('harga_perolehan');
        $totalPenyusutan = AsetTetap::sum('akumulasi_penyusutan');
        $totalNilaiBuku = AsetTetap::get()->sum(fn ($a) => $a->nilai_buku);

        return view('aset_tetap.index', compact('asets', 'totalHarga', 'totalPenyusutan', 'totalNilaiBuku'));
    }

    /**
     * Simpan aset tetap baru + jurnal pembukaan double-entry:
     * Debit Aset Tetap (nilai buku) / Kredit Modal Pemilik — menjaga neraca seimbang.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_aset'           => 'required|string|max:150',
            'kode_aset'           => 'nullable|string|max:30|unique:aset_tetap,kode_aset',
            'kategori_aset'       => 'nullable|string|max:100',
            'tanggal_perolehan'   => 'required|date',
            'harga_perolehan'     => 'required|numeric|min:0',
            'akumulasi_penyusutan'=> 'required|numeric|min:0',
            'nilai_residu'        => 'required|numeric|min:0',
            'masa_manfaat_bulan'  => 'nullable|integer|min:1',
            'keterangan'          => 'nullable|string',
        ], [
            'nama_aset.required'        => 'Nama aset wajib diisi.',
            'harga_perolehan.required'  => 'Harga perolehan wajib diisi.',
            'kode_aset.unique'          => 'Kode aset sudah digunakan.',
        ]);

        try {
            $aset = DB::transaction(function () use ($validated) {
                $aset = AsetTetap::create(array_merge($validated, [
                    'user_id'   => Auth::id(),
                    'is_active' => true,
                ]));

                $nilaiBuku = $aset->nilai_buku;

                if ($nilaiBuku > 0) {
                    // Jurnal: aset disetor/dibeli dari modal pemilik (investasi).
                    DB::table('jurnal')->insert([
                        'tanggal'      => $aset->tanggal_perolehan?->toDateString() ?? now()->toDateString(),
                        'kode_jurnal'  => 'JR-AT-' . now()->format('Ymd') . '-' . str_pad($aset->id, 4, '0', STR_PAD_LEFT),
                        'transaksi_id' => null,
                        'akun_debit'   => 'Aset Tetap',
                        'akun_kredit'  => 'Modal Pemilik',
                        'akun_debit_id'  => Akun::resolveIdByName('Aset Tetap'),
                        'akun_kredit_id' => Akun::resolveIdByName('Modal Pemilik'),
                        'nominal'      => $nilaiBuku,
                        'keterangan'   => "Perolehan aset tetap \"{$aset->nama_aset}\" (nilai buku Rp " . number_format($nilaiBuku, 0, ',', '.') . ')',
                        'created_at'   => now(),
                    ]);
                }

                return $aset;
            });

            return redirect()->route('aset-tetap.index')
                ->with('success', 'Aset tetap "' . $aset->nama_aset . '" berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan aset tetap: ' . $e->getMessage());
        }
    }

    /**
     * Update data aset tetap.
     */
    public function update(Request $request, AsetTetap $aset)
    {
        $validated = $request->validate([
            'nama_aset'           => 'required|string|max:150',
            'kode_aset'           => 'nullable|string|max:30|unique:aset_tetap,kode_aset,' . $aset->id,
            'kategori_aset'       => 'nullable|string|max:100',
            'tanggal_perolehan'   => 'required|date',
            'harga_perolehan'     => 'required|numeric|min:0',
            'akumulasi_penyusutan'=> 'required|numeric|min:0',
            'nilai_residu'        => 'required|numeric|min:0',
            'masa_manfaat_bulan'  => 'nullable|integer|min:1',
            'keterangan'          => 'nullable|string',
        ]);

        $aset->update($validated);

        return redirect()->route('aset-tetap.index')
            ->with('success', 'Aset tetap "' . $aset->nama_aset . '" berhasil diperbarui.');
    }

    /**
     * Aktivasi / nonaktifkan aset tetap.
     */
    public function toggle(AsetTetap $aset)
    {
        $aset->update(['is_active' => ! $aset->is_active]);

        return back()->with('success', 'Status aset tetap "' . $aset->nama_aset . '" diperbarui.');
    }
}