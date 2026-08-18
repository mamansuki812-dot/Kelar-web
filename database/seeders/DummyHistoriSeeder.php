<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Beban;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\JurnalService;
use App\Services\StokService;
use App\Services\TransaksiService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Fase 1 (revisi dosen) — data dummy historis Januari–Juli 2026.
 *
 * KHUSUS untuk database demo (kelar_pos_demo). TIDAK untuk database produksi.
 *
 * Aturan wajib:
 * - Wajib panggil TransaksiService::proses() (bukan insert manual) agar stok &
 *   jurnal konsisten mengikuti alur asli POS. Tanggal historis disimulasikan
 *   dengan Carbon::setTestNow() per transaksi (service memakai now()).
 * - Beban via JurnalService::catatBeban().
 * - Semua transaksi dummy diberi penanda "DUMMY-SEED" di kolom catatan agar bisa
 *   diidentifikasi/dibersihkan dan tidak tercampur data asli.
 * - Jumlah transaksi per bulan dibuat bervariasi (bukan flat) supaya tren chart
 *   terlihat naik-turun. Penerimaan barang otomatis (top-up stok) saat stok
 *   mendekati habis, ikut mencatat jurnal utang usaha.
 */
class DummyHistoriSeeder extends Seeder
{
    public function run(): void
    {
        $transaksiService = app(TransaksiService::class);
        $stokService      = app(StokService::class);
        $jurnalService    = app(JurnalService::class);

        $user    = User::where('role', 'admin')->firstOrFail();

        if (Produk::where('is_active', true)->count() === 0) {
            throw new \RuntimeException('Tidak ada produk aktif. Jalankan ProdukFondasiSeeder terlebih dahulu.');
        }

        // Variasi jumlah transaksi per bulan (naik-turun realistis, bukan flat).
        $bulanConfig = [
            '2026-01' => 38,
            '2026-02' => 30,
            '2026-03' => 44,
            '2026-04' => 36,
            '2026-05' => 50,
            '2026-06' => 46,
            '2026-07' => 56,
        ];

        $metodePool = [
            'tunai', 'tunai', 'tunai',
            'transfer', 'transfer',
            'qris',
        ];

        foreach ($bulanConfig as $bulan => $jumlahTransaksi) {
            $tanggalMulai = Carbon::parse($bulan . '-01');
            $hariDalamBulan = $tanggalMulai->daysInMonth;

            echo "Bulan {$bulan}: {$jumlahTransaksi} transaksi\n";

            for ($i = 0; $i < $jumlahTransaksi; $i++) {
                // Tanggal: kebanyakan hari kerja, sesekali weekend/awal bulan (ramai).
                // Mulai dari tanggal 2 (tanggal 1 dipakai jurnal pembukaan).
                if ($i % 7 === 0) {
                    // Hari ramai: tanggal 2, 15, 25 (biasa orang gajian/belanja)
                    $day = [2, 15, 25][($i / 7) % 3];
                } else {
                    $day = random_int(2, max(2, $hariDalamBulan - 1));
                }

                $tanggal = $tanggalMulai->copy()->day(min($day, $hariDalamBulan));
                $jam     = random_int(8, 20);
                $menit   = random_int(0, 59);
                $waktu   = $tanggal->setTime($jam, $menit);

                Carbon::setTestNow($waktu);

                // Produk dimuat ulang tiap transaksi agar stok selalu segar
                // (sebelumnya mungkin dikurangi transaksi lain).
                $produks = Produk::where('is_active', true)->get();

                // Pilih 1-3 produk dengan qty kecil; top-up stok jika tidak cukup.
                $items = [];
                $jumlahItem = random_int(1, 3);
                $indeksDipilih = array_rand($produks->all(), $jumlahItem);
                foreach ((array) $indeksDipilih as $idx) {
                    $produk = $produks[$idx];
                    $qty = random_int(1, 3);
                    $this->pastikanStok($stokService, $jurnalService, $produk, $qty, $user->id);
                    $items[] = [
                        'produk_id'    => $produk->id,
                        'jumlah'       => $qty,
                        'harga_satuan' => (float) $produk->harga_jual,
                    ];
                }

                $metode = $metodePool[array_rand($metodePool)];

                $trx = $transaksiService->proses(
                    items: $items,
                    diskon: 0,
                    metode_pembayaran: $metode,
                    jumlah_bayar: collect($items)->sum(fn ($i) => $i['harga_satuan'] * $i['jumlah']),
                    user_id: $user->id,
                );

                // Penanda dummy (tidak mengubah jurnal/stok yang sudah tercatat).
                $trx->update(['catatan' => 'DUMMY-SEED']);
            }
        }

        // Reset waktu uji sebelum membuat beban (catatBeban memakai $beban->tanggal).
        Carbon::setTestNow(null);

        $this->seederBeban($jurnalService, $user->id);

        echo "\nDUMMY SEED SELESAI.\n";
    }

    /**
     * Pastikan stok produk cukup untuk qty yang diminta.
     * Jika stok di bawah ambang aman (25), tambah stok (penerimaan barang)
     * + catat jurnal utang usaha. Threshold dibuat cukup tinggi supaya produk
     * fondasi (kadaluarsa/BHP) tidak habis di tengah sejarah — tetap tersedia
     * untuk Fase 3 (BOM paket).
     */
    private function pastikanStok(StokService $stokService, JurnalService $jurnalService, Produk $produk, int $qty, int $userId): void
    {
        if ($produk->stok < max(25, $qty)) {
            $tambah = max($qty + 40, 60);
            $history = $stokService->tambah(
                produk: $produk,
                jumlah: $tambah,
                userId: $userId,
                keterangan: "Penerimaan DUMMY-SEED stok {$produk->kode_produk}",
            );
            $jurnalService->catatPenerimaanBarang($history, $tambah * (float) $produk->harga_beli, 'utang');
            $produk->refresh();
        }
    }

    /**
     * Beban operasional dummy per bulan (Jan–Jul 2026), nominal bervariasi
     * supaya grafik beban tidak flat. Via JurnalService::catatBeban().
     */
    private function seederBeban(JurnalService $jurnalService, int $userId): void
    {
        $akunBeban = Akun::where('nama_akun', 'Beban Operasional')->firstOrFail();

        $bebanPerBulan = [
            '2026-01' => 180_000,
            '2026-02' => 150_000,
            '2026-03' => 200_000,
            '2026-04' => 190_000,
            '2026-05' => 230_000,
            '2026-06' => 210_000,
            '2026-07' => 250_000,
        ];

        foreach ($bebanPerBulan as $bulan => $nominal) {
            $tanggal = Carbon::parse($bulan . '-28');

            $beban = Beban::create([
                'tanggal'    => $tanggal,
                'akun_id'    => $akunBeban->id,
                'nominal'    => $nominal,
                'keterangan' => 'Beban operasional DUMMY-SEED ' . $bulan,
                'user_id'    => $userId,
            ]);

            $jurnalService->catatBeban($beban);
        }
    }
}
