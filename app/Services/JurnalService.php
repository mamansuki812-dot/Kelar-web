<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Beban;
use App\Models\Jurnal;
use App\Models\StokHistory;

class JurnalService
{
    /**
     * Jurnal penerimaan barang dari supplier.
     * Debit Persediaan Barang / Kredit Kas (tunai) atau Utang Usaha (default).
     * Nominal = jumlah diterima x harga_beli produk saat itu.
     */
    public function catatPenerimaanBarang(StokHistory $history, float $totalNilai, string $metodeBayar = 'utang'): void
    {
        $akunKredit = $metodeBayar === 'tunai' ? 'Kas' : 'Utang Usaha';

        Jurnal::create([
            'tanggal'        => now(),
            'kode_jurnal'    => 'JR-' . now()->format('Ymd') . '-R' . str_pad($history->id, 5, '0', STR_PAD_LEFT),
            'transaksi_id'   => null,
            'akun_debit'     => 'Persediaan Barang',
            'akun_kredit'    => $akunKredit,
            'akun_debit_id'  => Akun::resolveIdByName('Persediaan Barang'),
            'akun_kredit_id' => Akun::resolveIdByName($akunKredit),
            'nominal'        => $totalNilai,
            'keterangan'     => $history->keterangan ?: 'Penerimaan barang',
        ]);
    }

    /**
     * Jurnal penyesuaian stok (opname / rusak / hilang / temuan).
     * Berkurang: Debit Kerugian Persediaan / Kredit Persediaan Barang.
     * Bertambah: Debit Persediaan Barang / Kredit Koreksi Persediaan (pendapatan lain-lain).
     * Nominal = |selisih stok| x harga_beli produk.
     */
    public function catatPenyesuaianStok(StokHistory $history): void
    {
        $nominal   = $history->jumlah * (float) ($history->produk?->harga_beli ?? 0);
        $bertambah = $history->stok_sesudah > $history->stok_sebelum;

        if ($bertambah) {
            $akunDebit  = 'Persediaan Barang';
            $akunKredit = 'Koreksi Persediaan';
        } else {
            $akunDebit  = 'Kerugian Persediaan';
            $akunKredit = 'Persediaan Barang';
        }

        Jurnal::create([
            'tanggal'        => now(),
            'kode_jurnal'    => 'JR-' . now()->format('Ymd') . '-S' . str_pad($history->id, 5, '0', STR_PAD_LEFT),
            'transaksi_id'   => null,
            'akun_debit'     => $akunDebit,
            'akun_kredit'    => $akunKredit,
            'akun_debit_id'  => Akun::resolveIdByName($akunDebit),
            'akun_kredit_id' => Akun::resolveIdByName($akunKredit),
            'nominal'        => $nominal,
            'keterangan'     => $history->keterangan ?: 'Penyesuaian stok',
        ]);
    }

    /**
     * Jurnal beban operasional.
     * Debit akun beban (akun_id dari tabel beban) / Kredit Kas.
     * Nominal = nilai beban.
     */
    public function catatBeban(Beban $beban): void
    {
        Jurnal::create([
            'tanggal'        => $beban->tanggal,
            'kode_jurnal'    => 'JR-' . $beban->tanggal->format('Ymd') . '-B' . str_pad($beban->id, 5, '0', STR_PAD_LEFT),
            'transaksi_id'   => null,
            'akun_debit'     => $beban->akun?->nama_akun ?: 'Beban Operasional',
            'akun_kredit'    => 'Kas',
            'akun_debit_id'  => $beban->akun_id,
            'akun_kredit_id' => Akun::resolveIdByName('Kas'),
            'nominal'        => $beban->nominal,
            'keterangan'     => $beban->keterangan ?: 'Beban operasional',
        ]);
    }
}
