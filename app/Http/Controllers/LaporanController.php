<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Produk;
use App\Models\Beban;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\StokHistory;
use App\Services\AkuntansiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function __construct(private AkuntansiService $akuntansi)
    {
    }

    /**
     * Laporan Penjualan — ringkasan & detail per tanggal.
     */
    public function penjualan(Request $request)
    {
        $tanggalMulai = $request->filled('tanggal_mulai')
            ? Carbon::parse($request->tanggal_mulai)->startOfDay()
            : Carbon::now()->startOfMonth();

        $tanggalAkhir = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->endOfDay()
            : Carbon::now()->endOfDay();

        // Ringkasan
        $ringkasan = Transaksi::whereBetween('tanggal_transaksi', [$tanggalMulai, $tanggalAkhir]);
        $totalOmzet       = (clone $ringkasan)->sum('total_bayar');
        $totalTransaksi   = (clone $ringkasan)->count();
        $rataPerTransaksi = $totalTransaksi > 0 ? round($totalOmzet / $totalTransaksi) : 0;
        $totalDiskon      = (clone $ringkasan)->sum('diskon');

        // Grafik: omzet per hari
        $grafikHarian = Transaksi::whereBetween('tanggal_transaksi', [$tanggalMulai, $tanggalAkhir])
            ->select(DB::raw('DATE(tanggal_transaksi) as tanggal'), DB::raw('SUM(total_bayar) as total'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Produk terlaris
        $produkTerlaris = DetailTransaksi::join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('produk', 'detail_transaksi.produk_id', '=', 'produk.id')
            ->whereBetween('transaksi.tanggal_transaksi', [$tanggalMulai, $tanggalAkhir])
            ->select(
                'produk.nama_produk',
                'produk.kode_produk',
                DB::raw('SUM(detail_transaksi.jumlah) as total_qty'),
                DB::raw('SUM(detail_transaksi.subtotal) as total_subtotal')
            )
            ->groupBy('produk.id', 'produk.nama_produk', 'produk.kode_produk')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Daftar transaksi
        $transaksis = Transaksi::with('user')
            ->whereBetween('tanggal_transaksi', [$tanggalMulai, $tanggalAkhir])
            ->orderByDesc('tanggal_transaksi')
            ->paginate(15)
            ->withQueryString();

        return view('laporan.penjualan', compact(
            'tanggalMulai', 'tanggalAkhir',
            'totalOmzet', 'totalTransaksi', 'rataPerTransaksi', 'totalDiskon',
            'grafikHarian', 'produkTerlaris', 'transaksis'
        ));
    }

    /**
     * Laporan Stok — snapshot stok saat ini + filter status & periode pergerakan.
     * Filter periode (tanggal_mulai/tanggal_akhir) memfilter ringkasan & rincian
     * MUTASI stok (tabel stok_history), bukan snapshot (snapshot selalu kondisi
     * hari ini). Default: bulan berjalan hingga hari ini. Backward-compat: tanpa
     * parameter berarti seluruh mutasi yang tercatat.
     */
    public function stok(Request $request)
    {
        $query = Produk::with('kategori')->where('is_active', true);

        if ($request->filled('status')) {
            if ($request->status === 'normal') {
                $query->whereRaw('stok > stok_minimum');
            } elseif ($request->status === 'menipis') {
                $query->whereRaw('stok <= stok_minimum')->where('stok', '>', 0);
            } elseif ($request->status === 'habis') {
                $query->where('stok', '<=', 0);
            }
        }

        $produks = $query->orderByRaw('stok ASC')->get();

        $totalNilaiStok = $produks->sum(fn ($p) => $p->stok * $p->harga_jual);
        $totalStokHabis  = $produks->where('stok', '<=', 0)->count();
        $totalMenipis    = $produks->filter(fn ($p) => $p->stok > 0 && $p->stok <= $p->stok_minimum)->count();

        // Periode pergerakan stok (default bulan berjalan), dipakai filter mutasi.
        $tanggalMulai = $request->filled('tanggal_mulai')
            ? Carbon::parse($request->tanggal_mulai)->startOfDay()
            : Carbon::now()->startOfMonth();
        $tanggalAkhir = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->endOfDay()
            : Carbon::now()->endOfDay();

        $mutasiQuery = \App\Models\StokHistory::with('produk', 'user')
            ->whereBetween('created_at', [$tanggalMulai, $tanggalAkhir]);

        $totalMasuk      = (clone $mutasiQuery)->where('jenis', 'masuk')->sum('jumlah');
        $totalKeluar     = (clone $mutasiQuery)->where('jenis', 'keluar')->sum('jumlah');
        $totalPenyesuaian= (clone $mutasiQuery)->where('jenis', 'penyesuaian')->count();
        $jumlahRecord    = (clone $mutasiQuery)->count();

        $mutasiTerakhir = (clone $mutasiQuery)->orderByDesc('created_at')->limit(10)->get();

        return view('laporan.stok', compact(
            'produks', 'totalNilaiStok', 'totalStokHabis', 'totalMenipis',
            'tanggalMulai', 'tanggalAkhir',
            'totalMasuk', 'totalKeluar', 'totalPenyesuaian', 'jumlahRecord', 'mutasiTerakhir'
        ));
    }

    /**
     * Laporan Laba Rugi — sederhana (omzet - HPP ≈ laba kotor untuk MVP).
     * Filter berbasis PERIODE dari-sampai (tanggal_mulai & tanggal_akhir).
     * Backward-compat: parameter `bulan` (Y-m) lama tetap diterima.
     */
    public function labaRugi(Request $request)
    {
        ['tanggalMulai' => $tanggalMulai, 'tanggalAkhir' => $tanggalAkhir] = $this->resolusiPeriodeLabaRugi($request);
        $awalBulan  = $tanggalMulai;
        $akhirBulan = $tanggalAkhir;
        $bulan      = $tanggalMulai->format('Y-m');

        $totalPenjualan = Transaksi::whereBetween('tanggal_transaksi', [$awalBulan, $akhirBulan])->sum('total_bayar');
        $totalDiskon    = Transaksi::whereBetween('tanggal_transaksi', [$awalBulan, $akhirBulan])->sum('diskon');

        // HPP — estimasi berdasarkan harga_beli di produk
        $hpp = DetailTransaksi::join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('produk', 'detail_transaksi.produk_id', '=', 'produk.id')
            ->whereBetween('transaksi.tanggal_transaksi', [$awalBulan, $akhirBulan])
            ->select(DB::raw('SUM(detail_transaksi.jumlah * produk.harga_beli) as total_hpp'))
            ->value('total_hpp') ?? 0;

        $labaKotor = $totalPenjualan - $hpp;
        $marginPersen = $totalPenjualan > 0 ? round(($labaKotor / $totalPenjualan) * 100, 1) : 0;

        // Beban operasional & laba bersih
        $totalBeban = Beban::whereBetween('tanggal', [$awalBulan, $akhirBulan])->sum('nominal');
        $labaBersih = $labaKotor - $totalBeban;

        // Breakdown per kategori
        $kategoriBreakdown = DetailTransaksi::join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('produk', 'detail_transaksi.produk_id', '=', 'produk.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->whereBetween('transaksi.tanggal_transaksi', [$awalBulan, $akhirBulan])
            ->select(
                'kategori.nama_kategori',
                DB::raw('SUM(detail_transaksi.subtotal) as total_penjualan'),
                DB::raw('SUM(detail_transaksi.jumlah * produk.harga_beli) as total_hpp'),
                DB::raw('SUM(detail_transaksi.jumlah) as total_qty')
            )
            ->groupBy('kategori.id', 'kategori.nama_kategori')
            ->orderByDesc('total_penjualan')
            ->get()
            ->map(function ($row) {
                $row->laba = $row->total_penjualan - $row->total_hpp;
                $row->margin = $row->total_penjualan > 0
                    ? round(($row->laba / $row->total_penjualan) * 100, 1) : 0;
                return $row;
            });

        return view('laporan.laba_rugi', compact(
            'bulan', 'tanggalMulai', 'tanggalAkhir', 'awalBulan', 'akhirBulan',
            'totalPenjualan', 'totalDiskon', 'hpp', 'labaKotor', 'marginPersen',
            'totalBeban', 'labaBersih',
            'kategoriBreakdown'
        ));
    }

    /**
     * Laporan Neraca — berbasis saldo akun dari tabel jurnal (SAK EMKM).
     * Saldo per akun dihitung lewat AkuntansiService::saldoAkun() dengan filter
     * tanggal posisi. Ekuitas = saldo seluruh akun tipe 'ekuitas' (mis. Modal
     * Pemilik) + laba kumulatif (pendapatan + koreksi - beban - kerugian - hpp).
     */
    public function neraca(Request $request)
    {
        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->tanggal) : Carbon::now();
        $tanggalPosisi = $tanggal->toDateString();

        // Aset Lancar
        $kasEstimasi = $this->akuntansi->saldoAkun('Kas', $tanggalPosisi);
        $bank        = $this->akuntansi->saldoAkun('Bank', $tanggalPosisi);
        $nilaiStok   = $this->akuntansi->saldoAkun('Persediaan Barang', $tanggalPosisi);
        $totalAsetLancar = $kasEstimasi + $bank + $nilaiStok;

        // Aset Tetap (FASE 5): saldo akun Aset Tetap = nilai buku (harga perolehan − akumulasi)
        $asetTetap = $this->akuntansi->saldoAkun('Aset Tetap', $tanggalPosisi);
        $totalAset = $totalAsetLancar + $asetTetap;

        // Liabilitas
        $totalLiabilitas = $this->akuntansi->saldoAkun('Utang Usaha', $tanggalPosisi);

        // Laba kumulatif dari akun pendapatan/beban/hpp
        $pendapatan = $this->akuntansi->saldoAkun('Pendapatan Penjualan', $tanggalPosisi);
        $koreksi    = $this->akuntansi->saldoAkun('Koreksi Persediaan', $tanggalPosisi);
        $bebanOp    = $this->akuntansi->saldoAkun('Beban Operasional', $tanggalPosisi);
        $kerugian   = $this->akuntansi->saldoAkun('Kerugian Persediaan', $tanggalPosisi);
        $hpp        = $this->akuntansi->saldoAkun('Harga Pokok Penjualan', $tanggalPosisi);
        $labaKumulatif = $pendapatan + $koreksi - $bebanOp - $kerugian - $hpp;

        // Ekuitas = akun ekuitas (Modal Pemilik dst) + laba kumulatif
        $ekuitasAkun = Akun::where('tipe', 'ekuitas')->get()
            ->sum(fn (Akun $a) => $this->akuntansi->saldoAkun($a->nama_akun, $tanggalPosisi));
        $totalEkuitas = $ekuitasAkun + $labaKumulatif;

        $totalPasiva = $totalLiabilitas + $totalEkuitas;

        // Verifikasi keseimbangan — tidak dipaksakan cocok, hanya dilaporkan
        $selisihNeraca = $totalAset - $totalPasiva;
        $seimbang      = abs($selisihNeraca) < 0.01;

        // Laba kotor kumulatif (referensi): pendapatan penjualan - HPP
        $labaKotor = $pendapatan - $hpp;

        return view('laporan.neraca', compact(
            'tanggal', 'kasEstimasi', 'bank', 'nilaiStok', 'totalAsetLancar',
            'asetTetap', 'totalAset',
            'totalLiabilitas', 'totalEkuitas', 'totalPasiva', 'labaKotor',
            'selisihNeraca', 'seimbang'
        ));
    }

    /**
     * Catatan atas Laporan Keuangan (CaLK) — SAK EMKM.
     * Pernyataan kepatuhan + ikhtisar kebijakan akuntansi + rincian pos-pos utama
     * periode berjalan. Data laporan di-reuse dari method neraca() & labaRugi()
     * (single-source-of-truth, tidak dihitung ulang terpisah).
     */
    public function calk(Request $request)
    {
        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->tanggal) : Carbon::now();

        // Laba Rugi pada CaLK mengikuti bulan dari tanggal posisi (dari-sampai
        // di-pecah 1 s.d. akhir bulan). Backward-compat `bulan` tetap didukung
        // oleh labaRugi() bila parameter range tidak terisi.
        $request->merge([
            'tanggal_mulai' => $tanggal->copy()->startOfMonth()->toDateString(),
            'tanggal_akhir' => $tanggal->copy()->endOfMonth()->toDateString(),
        ]);

        $neraca = $this->neraca($request)->getData();
        $laba   = $this->labaRugi($request)->getData();

        return view('laporan.calk', [
            'tanggal' => $tanggal,

            // Neraca (posisi keuangan per tanggal)
            'kasEstimasi'        => $neraca['kasEstimasi'],
            'nilaiStok'          => $neraca['nilaiStok'],
            'totalAset'          => $neraca['totalAset'],
            'totalLiabilitas'    => $neraca['totalLiabilitas'],
            'totalEkuitas'       => $neraca['totalEkuitas'],
            'totalPasiva'        => $neraca['totalPasiva'],
            'labaKotorKumulatif' => $neraca['labaKotor'],
            'selisihNeraca'      => $neraca['selisihNeraca'],
            'seimbang'           => $neraca['seimbang'],

            // Laba Rugi (periode = bulan dari tanggal posisi)
            'bulan'              => $laba['bulan'],
            'awalBulan'          => $laba['awalBulan'],
            'akhirBulan'         => $laba['akhirBulan'],
            'totalPenjualan'     => $laba['totalPenjualan'],
            'totalDiskon'        => $laba['totalDiskon'],
            'hpp'                => $laba['hpp'],
            'labaKotor'          => $laba['labaKotor'],
            'marginPersen'       => $laba['marginPersen'],
            'totalBeban'         => $laba['totalBeban'],
            'labaBersih'         => $laba['labaBersih'],
            'kategoriBreakdown'  => $laba['kategoriBreakdown'],
        ]);
    }

    /**
     * Buku Jurnal & Buku Besar
     */
    public function jurnal(Request $request)
    {
        $query = \App\Models\Jurnal::with('transaksi')->orderByDesc('tanggal')->orderByDesc('id');

        if ($request->filled('akun')) {
            $akun = $request->akun;
            $query->where(function ($q) use ($akun) {
                $q->where('akun_debit', $akun)
                  ->orWhere('akun_kredit', $akun);
            });
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        $jurnals = $query->paginate(30)->withQueryString();

        // Ambil semua nama akun unik untuk filter
        $akuns = ['Kas', 'Pendapatan Penjualan', 'Harga Pokok Penjualan', 'Persediaan Barang'];

        return view('laporan.jurnal', compact('jurnals', 'akuns'));
    }

    /**
     * Export laporan ke PDF.
     */
    public function export(Request $request, string $jenis)
    {
        switch ($jenis) {
            case 'penjualan':
                return $this->exportPenjualan($request);
            case 'stok':
                return $this->exportStok($request);
            case 'laba-rugi':
                return $this->exportLabaRugi($request);
            case 'neraca':
                return $this->exportNeraca($request);
            case 'calk':
                return $this->exportCalk($request);
            case 'jurnal':
                return $this->exportJurnal($request);
            default:
                abort(404);
        }
    }

    private function exportPenjualan(Request $request)
    {
        $tanggalMulai = $request->filled('tanggal_mulai')
            ? Carbon::parse($request->tanggal_mulai)->startOfDay()
            : Carbon::now()->startOfMonth();
        $tanggalAkhir = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->endOfDay()
            : Carbon::now()->endOfDay();

        $ringkasan = Transaksi::whereBetween('tanggal_transaksi', [$tanggalMulai, $tanggalAkhir]);
        $totalOmzet       = (clone $ringkasan)->sum('total_bayar');
        $totalTransaksi   = (clone $ringkasan)->count();
        $rataPerTransaksi = $totalTransaksi > 0 ? round($totalOmzet / $totalTransaksi) : 0;
        $totalDiskon      = (clone $ringkasan)->sum('diskon');

        $transaksis = Transaksi::with('user')
            ->whereBetween('tanggal_transaksi', [$tanggalMulai, $tanggalAkhir])
            ->orderByDesc('tanggal_transaksi')
            ->get();

        $pdf = Pdf::loadView('laporan.pdf.penjualan', compact(
            'tanggalMulai', 'tanggalAkhir',
            'totalOmzet', 'totalTransaksi', 'rataPerTransaksi', 'totalDiskon', 'transaksis'
        ));
        return $pdf->download('laporan-penjualan-' . $tanggalMulai->format('Y-m-d') . '.pdf');
    }

    private function exportStok(Request $request)
    {
        $produks = Produk::with('kategori')->where('is_active', true)->orderByRaw('stok ASC')->get();
        $totalNilaiStok = $produks->sum(fn ($p) => $p->stok * $p->harga_jual);
        $totalStokHabis  = $produks->where('stok', '<=', 0)->count();
        $totalMenipis    = $produks->filter(fn ($p) => $p->stok > 0 && $p->stok <= $p->stok_minimum)->count();

        $tanggalMulai = $request->filled('tanggal_mulai')
            ? Carbon::parse($request->tanggal_mulai)->startOfDay()
            : Carbon::now()->startOfMonth();
        $tanggalAkhir = $request->filled('tanggal_akhir')
            ? Carbon::parse($request->tanggal_akhir)->endOfDay()
            : Carbon::now()->endOfDay();

        $mutasiQuery = StokHistory::with('produk', 'user')->whereBetween('created_at', [$tanggalMulai, $tanggalAkhir]);
        $totalMasuk      = (clone $mutasiQuery)->where('jenis', 'masuk')->sum('jumlah');
        $totalKeluar     = (clone $mutasiQuery)->where('jenis', 'keluar')->sum('jumlah');
        $jumlahRecord    = (clone $mutasiQuery)->count();

        $pdf = Pdf::loadView('laporan.pdf.stok', compact(
            'produks', 'totalNilaiStok', 'totalStokHabis', 'totalMenipis',
            'tanggalMulai', 'tanggalAkhir',
            'totalMasuk', 'totalKeluar', 'jumlahRecord'
        ));
        return $pdf->download('laporan-stok-' . now()->format('Y-m-d') . '.pdf');
    }

    private function exportLabaRugi(Request $request)
    {
        ['tanggalMulai' => $tanggalMulai, 'tanggalAkhir' => $tanggalAkhir] = $this->resolusiPeriodeLabaRugi($request);
        $awalBulan  = $tanggalMulai;
        $akhirBulan = $tanggalAkhir;
        $bulan      = $tanggalMulai->format('Y-m');

        $totalPenjualan = Transaksi::whereBetween('tanggal_transaksi', [$awalBulan, $akhirBulan])->sum('total_bayar');
        $totalDiskon    = Transaksi::whereBetween('tanggal_transaksi', [$awalBulan, $akhirBulan])->sum('diskon');
        $hpp = DetailTransaksi::join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('produk', 'detail_transaksi.produk_id', '=', 'produk.id')
            ->whereBetween('transaksi.tanggal_transaksi', [$awalBulan, $akhirBulan])
            ->select(DB::raw('SUM(detail_transaksi.jumlah * produk.harga_beli) as total_hpp'))
            ->value('total_hpp') ?? 0;
        $labaKotor = $totalPenjualan - $hpp;
        $marginPersen = $totalPenjualan > 0 ? round(($labaKotor / $totalPenjualan) * 100, 1) : 0;

        $totalBeban = Beban::whereBetween('tanggal', [$awalBulan, $akhirBulan])->sum('nominal');
        $labaBersih = $labaKotor - $totalBeban;

        $kategoriBreakdown = DetailTransaksi::join('transaksi', 'detail_transaksi.transaksi_id', '=', 'transaksi.id')
            ->join('produk', 'detail_transaksi.produk_id', '=', 'produk.id')
            ->join('kategori', 'produk.kategori_id', '=', 'kategori.id')
            ->whereBetween('transaksi.tanggal_transaksi', [$awalBulan, $akhirBulan])
            ->select('kategori.nama_kategori', DB::raw('SUM(detail_transaksi.subtotal) as total_penjualan'), DB::raw('SUM(detail_transaksi.jumlah * produk.harga_beli) as total_hpp'), DB::raw('SUM(detail_transaksi.jumlah) as total_qty'))
            ->groupBy('kategori.id', 'kategori.nama_kategori')
            ->orderByDesc('total_penjualan')
            ->get()
            ->map(function ($row) {
                $row->laba = $row->total_penjualan - $row->total_hpp;
                $row->margin = $row->total_penjualan > 0 ? round(($row->laba / $row->total_penjualan) * 100, 1) : 0;
                return $row;
            });

        $pdf = Pdf::loadView('laporan.pdf.laba_rugi', compact(
            'bulan', 'tanggalMulai', 'tanggalAkhir', 'awalBulan', 'akhirBulan', 'totalPenjualan', 'totalDiskon', 'hpp', 'labaKotor', 'marginPersen', 'totalBeban', 'labaBersih', 'kategoriBreakdown'
        ));
        return $pdf->download('laporan-laba-rugi-' . $tanggalMulai->format('Y-m-d') . '-' . $tanggalAkhir->format('Y-m-d') . '.pdf');
    }

    private function exportNeraca(Request $request)
    {
        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->tanggal) : Carbon::now();
        $tanggalPosisi = $tanggal->toDateString();

        // Rumus identik dengan neraca() agar angka PDF = angka layar.
        $kasEstimasi = $this->akuntansi->saldoAkun('Kas', $tanggalPosisi);
        $bank        = $this->akuntansi->saldoAkun('Bank', $tanggalPosisi);
        $nilaiStok   = $this->akuntansi->saldoAkun('Persediaan Barang', $tanggalPosisi);
        $totalAsetLancar = $kasEstimasi + $bank + $nilaiStok;

        $asetTetap   = $this->akuntansi->saldoAkun('Aset Tetap', $tanggalPosisi);
        $totalAset   = $totalAsetLancar + $asetTetap;

        $totalLiabilitas = $this->akuntansi->saldoAkun('Utang Usaha', $tanggalPosisi);

        $pendapatan = $this->akuntansi->saldoAkun('Pendapatan Penjualan', $tanggalPosisi);
        $koreksi    = $this->akuntansi->saldoAkun('Koreksi Persediaan', $tanggalPosisi);
        $bebanOp    = $this->akuntansi->saldoAkun('Beban Operasional', $tanggalPosisi);
        $kerugian   = $this->akuntansi->saldoAkun('Kerugian Persediaan', $tanggalPosisi);
        $hpp        = $this->akuntansi->saldoAkun('Harga Pokok Penjualan', $tanggalPosisi);
        $labaKumulatif = $pendapatan + $koreksi - $bebanOp - $kerugian - $hpp;

        $ekuitasAkun = Akun::where('tipe', 'ekuitas')->get()
            ->sum(fn (Akun $a) => $this->akuntansi->saldoAkun($a->nama_akun, $tanggalPosisi));
        $totalEkuitas = $ekuitasAkun + $labaKumulatif;

        $totalPasiva   = $totalLiabilitas + $totalEkuitas;
        $selisihNeraca = $totalAset - $totalPasiva;
        $seimbang      = abs($selisihNeraca) < 0.01;
        $labaKotor     = $pendapatan - $hpp;

        $pdf = Pdf::loadView('laporan.pdf.neraca', compact(
            'tanggal', 'kasEstimasi', 'bank', 'nilaiStok', 'totalAsetLancar',
            'asetTetap', 'totalAset', 'totalLiabilitas',
            'totalEkuitas', 'totalPasiva', 'labaKotor', 'selisihNeraca', 'seimbang'
        ));
        return $pdf->download('laporan-neraca-' . $tanggal->format('Y-m-d') . '.pdf');
    }

    private function exportCalk(Request $request)
    {
        // Reuse data dari calk() (yang me-reuse neraca() & labaRugi()) — konsisten.
        $data = $this->calk($request)->getData();

        $pdf = Pdf::loadView('laporan.pdf.calk', $data);
        return $pdf->download('catatan-atas-laporan-keuangan-' . $data['tanggal']->format('Y-m-d') . '.pdf');
    }

    private function exportJurnal(Request $request)
    {
        $query = \App\Models\Jurnal::with('transaksi')->orderByDesc('tanggal')->orderByDesc('id');
        if ($request->filled('akun')) {
            $akun = $request->akun;
            $query->where(function ($q) use ($akun) {
                $q->where('akun_debit', $akun)->orWhere('akun_kredit', $akun);
            });
        }
        if ($request->filled('tanggal_mulai')) $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        if ($request->filled('tanggal_akhir')) $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        $jurnals = $query->get();

        $pdf = Pdf::loadView('laporan.pdf.jurnal', compact('jurnals'));
        return $pdf->download('buku-jurnal-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Resolver periode Laba Rugi (sumber tunggal untuk halaman & export PDF).
     * Prioritas: tanggal_mulai/tanggal_akhir -> fallback bulan (Y-m) -> bulan berjalan.
     */
    private function resolusiPeriodeLabaRugi(Request $request): array
    {
        $isRange = $request->filled('tanggal_mulai') || $request->filled('tanggal_akhir');

        if ($isRange) {
            $tanggalMulai = $request->filled('tanggal_mulai')
                ? Carbon::parse($request->tanggal_mulai)->startOfDay()
                : Carbon::now()->startOfMonth();
            $tanggalAkhir = $request->filled('tanggal_akhir')
                ? Carbon::parse($request->tanggal_akhir)->endOfDay()
                : Carbon::now()->endOfDay();
        } elseif ($request->filled('bulan')) {
            // Backward-compat: bookmark/link lama dengan bulan tunggal (Y-m)
            $tanggalMulai = Carbon::parse($request->bulan . '-01')->startOfMonth();
            $tanggalAkhir = Carbon::parse($request->bulan . '-01')->endOfMonth();
        } else {
            $tanggalMulai = Carbon::now()->startOfMonth();
            $tanggalAkhir = Carbon::now()->endOfDay();
        }

        return ['tanggalMulai' => $tanggalMulai, 'tanggalAkhir' => $tanggalAkhir];
    }
}
