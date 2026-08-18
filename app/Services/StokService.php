<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\StokHistory;

class StokService
{
    /**
     * Kurangi stok produk setelah penjualan & catat history.
     */
    public function kurangi(Produk $produk, int $jumlah, int $userId, string $keterangan = ''): void
    {
        $stokSebelum = $produk->stok;
        $stokSesudah = $stokSebelum - $jumlah;

        if ($stokSesudah < 0) {
            throw new \Exception("Stok \"{$produk->nama_produk}\" tidak mencukupi (tersisa {$stokSebelum}).");
        }

        $produk->decrement('stok', $jumlah);

        $this->catatHistory(
            produkId: $produk->id,
            userId: $userId,
            jenis: 'keluar',
            jumlah: $jumlah,
            stokSebelum: $stokSebelum,
            stokSesudah: $stokSesudah,
            keterangan: $keterangan
        );
    }

    /**
     * Tambah stok produk (penerimaan barang) & catat history.
     */
    public function tambah(Produk $produk, int $jumlah, int $userId, string $keterangan = ''): StokHistory
    {
        $stokSebelum = $produk->stok;
        $stokSesudah = $stokSebelum + $jumlah;

        $produk->increment('stok', $jumlah);

        return $this->catatHistory(
            produkId: $produk->id,
            userId: $userId,
            jenis: 'masuk',
            jumlah: $jumlah,
            stokSebelum: $stokSebelum,
            stokSesudah: $stokSesudah,
            keterangan: $keterangan
        );
    }

    /**
     * Penyesuaian stok manual (koreksi fisik) & catat history.
     */
    public function sesuaikan(Produk $produk, int $stokBaru, int $userId, string $keterangan = 'Penyesuaian stok'): StokHistory
    {
        if ($stokBaru < 0) {
            throw new \Exception("Stok \"{$produk->nama_produk}\" tidak boleh negatif (nilai baru: {$stokBaru}).");
        }

        $stokSebelum = $produk->stok;
        $selisih     = abs($stokBaru - $stokSebelum);

        $produk->update(['stok' => $stokBaru]);

        return $this->catatHistory(
            produkId: $produk->id,
            userId: $userId,
            jenis: 'penyesuaian',
            jumlah: $selisih,
            stokSebelum: $stokSebelum,
            stokSesudah: $stokBaru,
            keterangan: $keterangan
        );
    }

    /**
     * Tulis record ke tabel stok_history.
     */
    private function catatHistory(
        int $produkId, int $userId, string $jenis,
        int $jumlah, int $stokSebelum, int $stokSesudah, string $keterangan
    ): StokHistory {
        return StokHistory::create([
            'produk_id'    => $produkId,
            'user_id'      => $userId,
            'jenis'        => $jenis,
            'jumlah'       => $jumlah,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'keterangan'   => $keterangan,
        ]);
    }
}
