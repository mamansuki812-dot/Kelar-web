<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Beban;
use App\Models\DetailTransaksi;
use App\Models\Jurnal;
use App\Models\Produk;
use App\Models\StokHistory;
use App\Models\Transaksi;
use App\Services\PembukaanService;
use App\Services\ResetDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    public function __construct(
        private ResetDataService $resetDataService,
        private PembukaanService $pembukaanService,
    ) {}

    /**
     * Halaman konfirmasi Reset Data — menampilkan ringkasan jumlah data yang
     * akan dihapus (real-time dari DB) plus master data yang dipertahankan.
     */
    public function resetDataForm()
    {
        $ringkasan = [
            'detail_transaksi' => DetailTransaksi::count(),
            'transaksi'        => Transaksi::count(),
            'jurnal'           => Jurnal::count(),
            'stok_history'     => StokHistory::count(),
            'beban'            => Beban::count(),
            'audit_log'        => AuditLog::count(),
        ];

        $masterTetap = [
            'users'         => \App\Models\User::count(),
            'kategori'      => \App\Models\Kategori::count(),
            'produk'        => Produk::count(),
            'supplier'      => \App\Models\Supplier::count(),
            'akun'          => \App\Models\Akun::count(),
            'aturan_diskon' => \App\Models\AturanDiskon::count(),
        ];

        return view('pengaturan.reset-data', compact('ringkasan', 'masterTetap'));
    }

    /**
     * Eksekusi Reset Data. Pengaman berlapis:
     *  - input teks konfirmasi harus sama persis "HAPUS SEMUA DATA";
     *  - re-entry password akun yang sedang login diverifikasi via Hash::check.
     */
    public function resetData(Request $request)
    {
        $validated = $request->validate([
            'konfirmasi_teks' => ['required', 'string', 'in:HAPUS SEMUA DATA'],
            'password'        => ['required', 'string'],
        ], [
            'konfirmasi_teks.required' => 'Teks konfirmasi wajib diisi.',
            'konfirmasi_teks.in'       => 'Teks konfirmasi harus persis "HAPUS SEMUA DATA".',
            'password.required'        => 'Password wajib diisi untuk verifikasi.',
        ]);

        if (!Hash::check($validated['password'], $request->user()->password)) {
            return back()->withErrors(['password' => 'Password yang Anda masukkan salah.'])->withInput();
        }

        $counts = $this->resetDataService->resetData(
            user: $request->user(),
            ip: $request->ip(),
        );

        // Setelah reset, jurnal kosong -> EnsureSetup akan mengarahkan admin
        // ke Setup Awal pada kunjungan berikutnya (redirect eksplisit di sini
        // agar alur langsung ketat secara UX).
        return redirect()->route('pengaturan.setup-awal')->with('success', sprintf(
            'Reset data berhasil. %d transaksi, %d jurnal, %d riwayat stok, %d beban, %d detail, %d jejak audit dihapus; stok semua produk di-set 0.',
            $counts['transaksi'],
            $counts['jurnal'],
            $counts['stok_history'],
            $counts['beban'],
            $counts['detail_transaksi'],
            $counts['audit_log'],
        ));
    }

    /**
     * Halaman Setup Awal / Onboarding — admin & pemilik mengisi kas awal dan
     * tanggal mulai pembukuan (cutover).
     */
    public function setupAwalForm()
    {
        $sudahAdaJurnal = Jurnal::exists();

        return view('pengaturan.setup-awal', [
            'sudahAdaJurnal' => $sudahAdaJurnal,
        ]);
    }

    /**
     * Simpan Setup Awal — menghasilkan jurnal pembukaan (JR-OPENING-*).
     * Memakai PembukaanService sehingga aturan guard sama dengan command CLI.
     */
    public function setupAwal(Request $request)
    {
        $validated = $request->validate([
            'kas_awal' => ['required', 'numeric', 'min:0'],
            'tanggal'  => ['required', 'date'],
        ], [
            'kas_awal.required' => 'Kas awal wajib diisi.',
            'kas_awal.numeric'  => 'Kas awal harus berupa angka.',
            'kas_awal.min'      => 'Kas awal tidak boleh negatif.',
            'tanggal.required'  => 'Tanggal mulai (cutover) wajib diisi.',
            'tanggal.date'      => 'Format tanggal tidak valid.',
        ]);

        try {
            $hasil = $this->pembukaanService->buatPembukaan(
                kas: (float) $validated['kas_awal'],
                tanggal: $validated['tanggal'],
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['tanggal' => $e->getMessage()])->withInput();
        }

        $detailEntry = $hasil['entry_persediaan']
            ? 'persediaan Rp ' . number_format($hasil['nominal'], 0, ',', '.')
            : 'persediaan Rp 0';
        if ($hasil['entry_kas']) {
            $detailEntry .= ' + kas Rp ' . number_format($hasil['kas'], 0, ',', '.');
        }

        return redirect()->route('dashboard')->with('success', sprintf(
            'Setup awal selesai (tanggal cutover %s). Jurnal pembukaan dibuat: %s (Debit / Kredit Modal Pemilik).',
            $hasil['tanggal'],
            $detailEntry,
        ));
    }
}