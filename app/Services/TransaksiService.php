<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransaksiService
{
    public function __construct(
        protected StokService $stokService
    ) {}

    /**
     * Ambil id shift kasir aktif milik user (null untuk admin atau tanpa shift).
     * Dipakai untuk menandai transaksi agar bisa ditelusuri ke shift yang sedang
     * berjalan (FASE 1). Kasir tanpa shift aktif ditolak di controller, bukan di sini.
     */
    private function shiftKasirIdAktif(int $userId): ?int
    {
        if (Auth::check() && Auth::id() === $userId && Auth::user()->role === 'kasir') {
            $shift = app(\App\Services\ShiftKasirService::class)->shiftAktif($userId);
            return $shift?->id;
        }
        return null;
    }

    /**
     * Ambang maksimum selisih kembalian (Rp) yang boleh dicatat sebagai donasi
     * (uang receh yang pelanggan relakan). Selisih di atas ambang wajib dikembalikan.
     */
    public const AMBANG_DONASI = 500;

    /**
     * Proses transaksi POS secara atomik (tunai/transfer/qris manual).
     * Perilaku & output identik dengan versi sebelumnya.
     *
     * @param array $items  [{ produk_id, jumlah, harga_satuan, tipe_diskon?, nilai_diskon? }]
     * @param float $diskon (header diskon legacy; diabaikan bila item sudah membawa
     *                       tipe_diskon/nilai_diskon — header diskon dihitung agregat dari baris)
     * @param string $metode_pembayaran
     * @param float $jumlah_bayar
     * @param int $user_id
     * @param bool $relakanKembalian  FASE 4: jika true dan selisih kembalian tunai
     *                                ≤ AMBANG_DONASI, selisih dicatat sebagai donasi
     *                                (pendapatan non-operasional) sehingga tidak ada
     *                                kelebihan yang harus dikembalikan.
     * @return Transaksi
     */
    public function proses(array $items, float $diskon, string $metode_pembayaran, float $jumlah_bayar, int $user_id, bool $relakanKembalian = false): Transaksi
    {
        return DB::transaction(function () use ($items, $metode_pembayaran, $jumlah_bayar, $user_id, $relakanKembalian) {

            // 0. Diskonto TIDAK dipercaya dari browser: hitung ulang dari aturan_diskon
            //    aktif (single source of truth) — nilai manual apa pun yang dikirim
            //    klien ditimpa di sini (anti manipulasi request).
            $items = $this->normalkanDiskonOtomatis($items);

            // 0. Validasi jumlah_bayar sebelum proses (safety net)
            $totalHarga = collect($items)->sum(fn($i) => $i['harga_satuan'] * $i['jumlah']);
            $totalDiskon = collect($items)->sum(fn($i) => $this->hitungDiskonEfektif($i));
            $totalBayar = max(0, $totalHarga - $totalDiskon);

            if ($jumlah_bayar <= 0) {
                throw new \Exception("Jumlah bayar harus lebih dari 0.");
            }
            if ($metode_pembayaran === 'tunai' && $jumlah_bayar < $totalBayar) {
                throw new \Exception("Jumlah bayar tunai tidak boleh kurang dari total.");
            }

            // 1. Validasi stok & kunci baris produk (pessimistic locking)
            $produks = $this->validasiStokDanKunci($items);

            // 2. Hitung kembalian; selisih kecil yang direlakan menjadi donasi.
            $kembalian = max(0, $jumlah_bayar - $totalBayar);
            $donasi = 0.0;
            if (
                $relakanKembalian &&
                $metode_pembayaran === 'tunai' &&
                $kembalian > 0 &&
                $kembalian <= self::AMBANG_DONASI
            ) {
                $donasi = $kembalian;
                $kembalian = 0.0;
            }

            // 3. Buat header transaksi
            $transaksi = Transaksi::create([
                'kode_transaksi'    => $this->generateKode(),
                'user_id'           => $user_id,
                'shift_kasir_id'    => $this->shiftKasirIdAktif($user_id),
                'tanggal_transaksi' => now(),
                'total_harga'       => $totalHarga,
                'diskon'            => $totalDiskon,
                'total_bayar'       => $totalBayar,
                'jumlah_bayar'      => $jumlah_bayar,
                'kembalian'         => $kembalian,
                'donasi'            => $donasi > 0 ? $donasi : null,
                'metode_pembayaran' => $metode_pembayaran,
                'status'            => 'selesai',
            ]);

            // 4. Simpan detail & hitung total HPP
            $totalHpp = $this->simpanDetailDanHitungHpp($transaksi, $items);

            // 5. Kurangi stok + catat jurnal (double-entry)
            $this->finalisasiStokDanJurnal(
                transaksi: $transaksi,
                items: $items,
                produks: $produks,
                metode_pembayaran: $metode_pembayaran,
                totalBayar: $totalBayar,
                totalHpp: $totalHpp,
                userId: $user_id
            );

            // 5b. FASE 4: jurnal donasi (Debit Kas / Kredit Pendapatan Non-Operasional)
            if ($donasi > 0) {
                $akunDonasi = 'Pendapatan Non-Operasional - Donasi Kembalian';
                \App\Models\Jurnal::create([
                    'tanggal'      => now(),
                    'kode_jurnal'  => 'JR-' . now()->format('Ymd') . '-' . str_pad($transaksi->id, 5, '0', STR_PAD_LEFT) . 'C',
                    'transaksi_id' => $transaksi->id,
                    'akun_debit'   => 'Kas',
                    'akun_kredit'  => $akunDonasi,
                    'akun_debit_id'   => $this->resolveAkunId('Kas'),
                    'akun_kredit_id'  => $this->resolveAkunId($akunDonasi),
                    'nominal'      => $donasi,
                    'keterangan'   => "Donasi kembalian #{$transaksi->kode_transaksi} (selisih Rp " . number_format($donasi, 0, ',', '.') . " direlakan pelanggan)",
                ]);
            }

            return $transaksi->load('details.produk', 'user');
        });
    }

    /**
     * Buat transaksi Midtrans status 'pending' (belum ada pengurangan stok/jurnal).
     * idempotency: midtrans_order_id = kode_transaksi (UNIQUE).
     *
     * @param array $items [{ produk_id, jumlah, harga_satuan, tipe_diskon?, nilai_diskon? }]
     * @param float $diskon (header legacy, diabaikan bila item membawa diskon per baris)
     * @param int $user_id
     * @return Transaksi
     */
    public function createPendingTransaksi(array $items, float $diskon, int $user_id): Transaksi
    {
        return DB::transaction(function () use ($items, $user_id) {

            // Diskonto dihitung ulang dari aturan_diskon aktif (anti manipulasi).
            $items = $this->normalkanDiskonOtomatis($items);

            // Validasi stok & kunci produk
            $this->validasiStokDanKunci($items);

            $totalHarga  = collect($items)->sum(fn($i) => $i['harga_satuan'] * $i['jumlah']);
            $totalDiskon = collect($items)->sum(fn($i) => $this->hitungDiskonEfektif($i));
            $totalBayar  = max(0, $totalHarga - $totalDiskon);

            if ($totalBayar <= 0) {
                throw new \Exception("Total bayar harus lebih dari 0.");
            }

            $kode = $this->generateKode();

            $transaksi = Transaksi::create([
                'kode_transaksi'    => $kode,
                'user_id'           => $user_id,
                'shift_kasir_id'    => $this->shiftKasirIdAktif($user_id),
                'tanggal_transaksi' => now(),
                'total_harga'       => $totalHarga,
                'diskon'            => $totalDiskon,
                'total_bayar'       => $totalBayar,
                'jumlah_bayar'      => $totalBayar,
                'kembalian'         => 0,
                'metode_pembayaran' => 'midtrans',
                'status'            => 'pending',
                'midtrans_order_id' => $kode,
            ]);

            $this->simpanDetailDanHitungHpp($transaksi, $items);

            return $transaksi->load('details.produk', 'user');
        });
    }

    /**
     * Finalisasi transaksi Midtrans setelah settlement/capture (webhook ATAU status API).
     * Idempoten: jika status sudah 'selesai'/'dibatalkan', langsung return tanpa proses ulang.
     * Stok & jurnal HANYA dikurangi di sini.
     *
     * @param Transaksi $transaksi
     * @param string $paymentType  payment_type dari Midtrans (qris, bank_transfer, dst)
     * @param string $midtransTransactionId
     * @return Transaksi
     */
    public function finalisasiPembayaranOnline(Transaksi $transaksi, string $paymentType, string $midtransTransactionId): Transaksi
    {
        return DB::transaction(function () use ($transaksi, $paymentType, $midtransTransactionId) {

            $locked = Transaksi::whereKey($transaksi->id)->lockForUpdate()->firstOrFail();

            // Idempotency guard — sudah diproses, tidak proses ulang
            if (in_array($locked->status, ['selesai', 'dibatalkan'])) {
                return $locked;
            }
            if ($locked->status !== 'pending') {
                throw new \Exception("Status transaksi tidak valid untuk finalisasi ({$locked->status}).");
            }

            $details = $locked->details()->get();
            $items   = $details->map(fn($d) => [
                'produk_id'    => $d->produk_id,
                'jumlah'       => $d->jumlah,
                'harga_satuan' => $d->harga_satuan,
                'tipe_diskon'  => $d->tipe_diskon,
                'nilai_diskon' => $d->nilai_diskon,
            ])->all();

            $produks = $this->validasiStokDanKunci($items);

            $totalHarga  = collect($items)->sum(fn($i) => $i['harga_satuan'] * $i['jumlah']);
            $totalDiskon = collect($items)->sum(fn($i) => $this->hitungDiskonEfektif($i));
            $totalBayar  = max(0, $totalHarga - $totalDiskon);
            $totalHpp    = collect($items)->sum(fn($i) => $produks->get($i['produk_id'])->harga_beli * $i['jumlah']);

            // Tandai selesai + simpan snapshot Midtrans
            $locked->update([
                'status'                    => 'selesai',
                'diskon'                    => $totalDiskon,
                'jumlah_bayar'              => $totalBayar,
                'kembalian'                 => 0,
                'midtrans_status'           => 'settlement',
                'midtrans_payment_type'     => $paymentType,
                'midtrans_transaction_id'   => $midtransTransactionId,
            ]);

            // Kurangi stok + jurnal (baru di sini!)
            $this->finalisasiStokDanJurnal(
                transaksi: $locked,
                items: $items,
                produks: $produks,
                metode_pembayaran: 'midtrans',
                totalBayar: $totalBayar,
                totalHpp: $totalHpp,
                userId: $locked->user_id
            );

            return $locked->load('details.produk', 'user');
        });
    }

    /**
     * Batalkan transaksi pending Midtrans (khusus metode 'midtrans' + status 'pending').
     * Hanya ubah state lokal DB → 'dibatalkan'; TIDAK memanggil API cancel ke Midtrans
     * (webhook expire Midtrans tetap berjalan terpisah). Idempoten.
     *
     * @param Transaksi $transaksi
     * @return Transaksi
     */
    public function batalkanPending(Transaksi $transaksi): Transaksi
    {
        return DB::transaction(function () use ($transaksi) {

            $locked = Transaksi::whereKey($transaksi->id)->lockForUpdate()->firstOrFail();

            // Idempotent — sudah dibatalkan
            if ($locked->status === 'dibatalkan') {
                return $locked;
            }

            if ($locked->status !== 'pending') {
                throw new \Exception('Transaksi hanya bisa dibatalkan saat status pending.');
            }
            if ($locked->metode_pembayaran !== 'midtrans') {
                throw new \Exception('Transaksi ini bukan pembayaran online.');
            }

            $locked->update([
                'status'         => 'dibatalkan',
                'midtrans_status'=> 'dibatalkan',
            ]);

            return $locked;
        });
    }

    /**
     * Kunci baris produk + validasi stok & status aktif.
     *
     * @return \Illuminate\Support\Collection produk keyed by id
     */
    private function validasiStokDanKunci(array $items): \Illuminate\Support\Collection
    {
        $produkIds = collect($items)->pluck('produk_id')->all();
        $produks   = Produk::whereIn('id', $produkIds)->lockForUpdate()->get()->keyBy('id');

        foreach ($items as $item) {
            $produk = $produks->get($item['produk_id']);
            if (!$produk) {
                throw new \Exception("Produk ID {$item['produk_id']} tidak ditemukan.");
            }
            if (!$produk->is_active) {
                throw new \Exception("Produk \"{$produk->nama_produk}\" tidak aktif.");
            }
            if ($produk->stok < $item['jumlah']) {
                throw new \Exception("Stok \"{$produk->nama_produk}\" tidak mencukupi (tersisa {$produk->stok}).");
            }
        }

        return $produks;
    }

    /**
     * Simpan detail transaksi & hitung total HPP.
     * Subtotal per baris = (harga_satuan × jumlah) − diskon efektif. Header transaksi
     * diskon dihitung agregat dari seluruh baris (backward-compat: tanpa tipe diskon ⇒ 0).
     */
    private function simpanDetailDanHitungHpp(Transaksi $transaksi, array $items): float
    {
        $totalHpp = 0;
        foreach ($items as $item) {
            $produk      = Produk::findOrFail($item['produk_id']);
            $subtotal    = $item['harga_satuan'] * $item['jumlah'];
            $diskonEfek  = $this->hitungDiskonEfektif($item);
            $subtotal    = max(0, $subtotal - $diskonEfek);
            $totalHpp    += $produk->harga_beli * $item['jumlah'];

            DetailTransaksi::create([
                'transaksi_id' => $transaksi->id,
                'produk_id'    => $item['produk_id'],
                'jumlah'       => $item['jumlah'],
                'harga_satuan' => $item['harga_satuan'],
                'tipe_diskon'  => $item['tipe_diskon'] ?? null,
                'nilai_diskon' => $this->nilaiDiskonUntukSimpan($item),
                'subtotal'     => $subtotal,
            ]);
        }

        return $totalHpp;
    }

/**
     * Hitung diskon efektif per baris keranjang (Rp).
     * - nominal: sebesar nilai_diskon (dibatasi tidak melebihi subtotal bruto).
     * - persen: presentase dari subtotal bruto.
     * - free-packaging: biaya kemasan per unit (nilai_diskon) × jumlah dibeli,
     *   dibatasi subtotal bruto (gratis pengemasan = digratiskan sebagai diskon).
     * Tanpa tipe (legacy) → 0.
     */
    public function hitungDiskonEfektif(array $item): float
    {
        $bruto     = (float) $item['harga_satuan'] * (int) $item['jumlah'];
        $tipe      = $item['tipe_diskon'] ?? null;
        $nilai     = (float) ($item['nilai_diskon'] ?? 0);

        if ($tipe === 'nominal') {
            return max(0, min($bruto, $nilai));
        }
        if ($tipe === 'free-packaging') {
            return max(0, min($bruto, $nilai * (int) $item['jumlah']));
        }
        if ($tipe === 'persen' && $nilai > 0) {
            return max(0, min($bruto, $bruto * $nilai / 100));
        }
        return 0;
    }

    /**
     * Nilai diskon yang disimpan ke DB: nilai asli untuk 'nominal', nilai persen (0-100)
     * untuk 'persen'; 0 bila tidak ada tipe (legacy).
     */
    private function nilaiDiskonUntukSimpan(array $item): float
    {
        if (empty($item['tipe_diskon'])) {
            return 0;
        }
        return (float) ($item['nilai_diskon'] ?? 0);
    }

    /**
     * Normalisasi diskon per baris dari aturan_diskon AKTIF (single source of truth).
     * Nilai tipe_diskon/nilai_diskon yang dikirim klien DIABAIKAN dan ditimpa —
     * kasir tidak boleh (dan tidak bisa) menambahkan diskon manual di POS.
     * Produk tanpa aturan berlaku → tipe_diskon null, nilai_diskon 0.
     */
    public function normalkanDiskonOtomatis(array $items): array
    {
        $ids = array_values(array_unique(array_column($items, 'produk_id')));
        $produks = Produk::with(['aturanDiskon' => fn($q) => $q->where('is_active', true)])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        foreach ($items as &$item) {
            $produk = $produks->get($item['produk_id']);
            $aturan = $produk?->aturanDiskon->first(fn($a) => $a->isBerlaku());
            $item['tipe_diskon'] = $aturan?->tipe_diskon;
            $item['nilai_diskon']= $aturan ? (float) $aturan->nilai_diskon : 0;
        }
        unset($item);

        return $items;
    }

    /**
     * Kurangi stok (catat history) + catat 2 jurnal double-entry.
     */
    private function finalisasiStokDanJurnal(
        Transaksi $transaksi,
        array $items,
        \Illuminate\Support\Collection $produks,
        string $metode_pembayaran,
        float $totalBayar,
        float $totalHpp,
        int $userId
    ): void {
        // Kurangi stok via StokService (mencatat history)
        foreach ($items as $item) {
            $this->stokService->kurangi(
                produk: $produks->get($item['produk_id']),
                jumlah: $item['jumlah'],
                userId: $userId,
                keterangan: "Penjualan #{$transaksi->kode_transaksi}"
            );
        }

        // Jurnal 1: Debit berdasarkan metode pembayaran
        $akunDebit = match ($metode_pembayaran) {
            'transfer', 'qris', 'midtrans' => 'Bank',
            default => 'Kas',
        };
        $labelMetode = match ($metode_pembayaran) {
            'midtrans' => 'online (Midtrans)',
            'transfer' => 'transfer',
            'qris'     => 'QRIS',
            default    => 'tunai',
        };

        \App\Models\Jurnal::create([
            'tanggal'      => now(),
            'kode_jurnal'  => 'JR-' . now()->format('Ymd') . '-' . str_pad($transaksi->id, 5, '0', STR_PAD_LEFT) . 'A',
            'transaksi_id' => $transaksi->id,
            'akun_debit'   => $akunDebit,
            'akun_kredit'  => 'Pendapatan Penjualan',
            'akun_debit_id'   => $this->resolveAkunId($akunDebit),
            'akun_kredit_id'  => $this->resolveAkunId('Pendapatan Penjualan'),
            'nominal'      => $totalBayar,
            'keterangan'   => "Penjualan {$labelMetode} #{$transaksi->kode_transaksi}",
        ]);

        // Jurnal 2: Harga Pokok Penjualan / Persediaan Barang
        if ($totalHpp > 0) {
            \App\Models\Jurnal::create([
                'tanggal'      => now(),
                'kode_jurnal'  => 'JR-' . now()->format('Ymd') . '-' . str_pad($transaksi->id, 5, '0', STR_PAD_LEFT) . 'B',
                'transaksi_id' => $transaksi->id,
                'akun_debit'   => 'Harga Pokok Penjualan',
                'akun_kredit'  => 'Persediaan Barang',
                'akun_debit_id'   => $this->resolveAkunId('Harga Pokok Penjualan'),
                'akun_kredit_id'  => $this->resolveAkunId('Persediaan Barang'),
                'nominal'      => $totalHpp,
                'keterangan'   => "Beban HPP atas penjualan #{$transaksi->kode_transaksi}",
            ]);
        }
    }

    /**
     * Resolusi nama akun (string) ke id tabel akun (COA), dengan cache per-proses.
     * Kolom string akun_debit/akun_kredit tetap diisi SEKALIGUS agar kode lama
     * yang masih membaca kolom string tidak error selama masa transisi.
     */
    private function resolveAkunId(string $nama): ?int
    {
        return Akun::resolveIdByName($nama);
    }

    /**
     * Generate kode transaksi unik format: TRX-YYYYMMDD-XXXXX
     */
    private function generateKode(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd') . '-';
        $last   = Transaksi::where('kode_transaksi', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('kode_transaksi');

        $urutan = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $prefix . str_pad($urutan, 5, '0', STR_PAD_LEFT);
    }
}
