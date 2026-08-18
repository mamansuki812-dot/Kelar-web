<?php

namespace App\Http\Controllers;

use App\Models\Beban;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Services\AkuntansiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(private AkuntansiService $akuntansi)
    {
    }

    public function index()
    {
        $user = Auth::user();

        // Ambil data statistik nyata dari database
        $today = Carbon::today();

        $transaksiHariIni = Transaksi::whereDate('tanggal_transaksi', $today);

        $stats = [
            'total_penjualan' => (clone $transaksiHariIni)->sum('total_bayar'),
            'total_transaksi' => (clone $transaksiHariIni)->count(),
            'produk_aktif'    => Produk::where('is_active', true)->count(),
            'stok_menipis'    => Produk::where('is_active', true)->whereRaw('stok <= stok_minimum')->where('stok', '>', 0)->count(),
            'stok_habis'      => Produk::where('is_active', true)->where('stok', '<=', 0)->count(),
        ];

        // Produk stok kritis untuk widget notifikasi (max 5)
        $produkKritis = Produk::where('is_active', true)
            ->where(function ($q) {
                $q->where('stok', '<=', 0)
                  ->orWhereRaw('stok <= stok_minimum');
            })
            ->orderByRaw('stok ASC')
            ->limit(5)
            ->get();

        // 5 transaksi terakhir
        $transaksiTerakhir = Transaksi::with('user')
            ->orderByDesc('tanggal_transaksi')
            ->limit(5)
            ->get();

        // Grafik tren omzet 7 hari terakhir
        $tujuhHariLalu = Carbon::now()->subDays(6)->startOfDay();
        $grafik7Hari = Transaksi::where('tanggal_transaksi', '>=', $tujuhHariLalu)
            ->select(
                \Illuminate\Support\Facades\DB::raw('DATE(tanggal_transaksi) as tanggal'),
                \Illuminate\Support\Facades\DB::raw('SUM(total_bayar) as total')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Dashboard Laba Rugi — hanya untuk admin & pemilik (data keuangan)
        $labaRugi = in_array($user->role, ['admin', 'pemilik'])
            ? $this->dataLabaRugi('bulanan', Carbon::now()->startOfMonth()->subMonths(5), Carbon::now()->endOfMonth())
            : null;

        return view('dashboard.index', compact(
            'user', 'stats', 'produkKritis', 'transaksiTerakhir', 'grafik7Hari', 'labaRugi'
        ));
    }

    /**
     * Endpoint AJAX dashboard laba rugi (Fase 1 — revisi dosen).
     *
     * Sumber kebenaran: AkuntansiService::saldoAkun() (buku besar tabel jurnal),
     * sama dengan laporan Neraca/CaLK yang sudah ada. Laba rugi PERIODE dihitung
     * sebagai delta saldo kumulatif: saldo(akhir titik) − saldo(hari sebelum awal titik).
     *
     * Query param: periode=harian|bulanan&mulai=YYYY-MM-DD&akhir=YYYY-MM-DD
     */
    public function labaRugi(Request $request)
    {
        $periode = in_array($request->periode, ['harian', 'bulanan']) ? $request->periode : 'bulanan';

        if ($request->filled('mulai') && $request->filled('akhir')) {
            $mulai = Carbon::parse($request->mulai);
            $akhir = Carbon::parse($request->akhir);
        } else {
            // Default: harian = 30 hari terakhir, bulanan = 6 bulan terakhir.
            $mulai = $periode === 'harian'
                ? Carbon::now()->subDays(29)->startOfDay()
                : Carbon::now()->startOfMonth()->subMonths(5);
            $akhir = $periode === 'harian'
                ? Carbon::now()->endOfDay()
                : Carbon::now()->endOfMonth();
        }

        return response()->json($this->dataLabaRugi($periode, $mulai, $akhir));
    }

    /**
     * Bangun series laba rugi per titik (hari/bulan) dari buku besar.
     *
     * Akun yang dipakai (COA): Pendapatan Penjualan (4-4000), Harga Pokok
     * Penjualan (5-5000), Beban Operasional (6-6000), Kerugian Persediaan (6-6100).
     * Laba kotor = pendapatan − HPP; beban = beban operasional + kerugian persediaan;
     * laba bersih = laba kotor − beban. Koreksi Persediaan (4-4100) tidak dimasukkan
     * (pendapatan lain-lain, jarang muncul di POS) — konsisten pendekatan MVP.
     *
     * @return array{periode:string, labels:array, pendapatan:array, labaKotor:array, beban:array, labaBersih:array, estimasi:float|null}
     */
    private function dataLabaRugi(string $periode, Carbon $mulai, Carbon $akhir): array
    {
        $mulai = $mulai->copy()->startOfDay();
        $akhir = $akhir->copy()->endOfDay();

        // Daftar titik periode: akhir tiap hari/bulan dalam rentang.
        $titik = [];
        if ($periode === 'harian') {
            $t = $mulai->copy()->startOfDay();
            while ($t->lte($akhir)) {
                $titik[] = $t->copy();
                $t->addDay();
            }
        } else {
            $t = $mulai->copy()->startOfMonth();
            while ($t->lte($akhir)) {
                $titik[] = $t->copy()->endOfMonth();
                $t->addMonth();
            }
        }

        $akun = [
            'pendapatan' => 'Pendapatan Penjualan',
            'hpp'        => 'Harga Pokok Penjualan',
            'beban'      => 'Beban Operasional',
            'kerugian'   => 'Kerugian Persediaan',
        ];

        // Saldo kumulatif sampai tanggal tertentu; titik sebelumnya = hari sebelum
        // awal titik (untuk delta). Untuk titik pertama, basis = sebelum mulai.
        $sebelumAwal = $mulai->copy()->subDay()->toDateString();

        $seri = ['pendapatan' => [], 'hpp' => [], 'beban' => [], 'kerugian' => []];
        $labels = [];

        foreach ($titik as $t) {
            $tanggal = $t->toDateString();
            $labels[] = $periode === 'harian'
                ? $t->format('d M')
                : $t->format('M Y');

            foreach ($akun as $key => $namaAkun) {
                $saldoTitik   = $this->akuntansi->saldoAkun($namaAkun, $tanggal);
                $saldoSebelum = $this->akuntansi->saldoAkun($namaAkun, $sebelumAwal);
                $seri[$key][] = $saldoTitik - $saldoSebelum;
            }

            $sebelumAwal = $tanggal;
        }

        $pendapatan = $seri['pendapatan'];
        $hpp        = $seri['hpp'];
        $bebanTotal = array_map(fn ($a, $b) => $a + $b, $seri['beban'], $seri['kerugian']);
        $labaKotor  = array_map(fn ($a, $b) => $a - $b, $pendapatan, $hpp);
        $labaBersih = array_map(fn ($a, $b) => $a - $b, $labaKotor, $bebanTotal);

        return [
            'periode'   => $periode,
            'labels'    => $labels,
            'pendapatan'=> array_map(fn ($v) => round($v, 2), $pendapatan),
            'labaKotor' => array_map(fn ($v) => round($v, 2), $labaKotor),
            'beban'     => array_map(fn ($v) => round($v, 2), $bebanTotal),
            'labaBersih'=> array_map(fn ($v) => round($v, 2), $labaBersih),
            'estimasi'  => $this->estimasiBebanBulanDepan(),
        ];
    }

    /**
     * Estimasi biaya bulan depan — rata-rata bergerak sederhana dari beban
     * operasional 3 bulan kalender terakhir yang lengkap (tabel beban).
     * BUKAN model prediktif/ML (di luar cakupan MVP). Jika belum ada 3 bulan
     * data, gunakan rata-rata dari bulan yang tersedia; 0 jika belum ada sama sekali.
     */
    private function estimasiBebanBulanDepan(): ?float
    {
        $mulai = Carbon::now()->startOfMonth()->subMonths(3);
        $akhir = Carbon::now()->startOfMonth()->subDay(); // bulan terakhir yang lengkap

        $total = Beban::whereBetween('tanggal', [$mulai, $akhir])->sum('nominal');

        // Banyak bulan lengkap yang punya data (untuk rata-rata yang jujur).
        $bulanBerisi = Beban::whereBetween('tanggal', [$mulai, $akhir])
            ->select(\Illuminate\Support\Facades\DB::raw('DISTINCT DATE_FORMAT(tanggal, "%Y-%m") as bulan'))
            ->get()
            ->count();

        if ($bulanBerisi === 0) {
            return null;
        }

        return round((float) $total / $bulanBerisi, 2);
    }
}
