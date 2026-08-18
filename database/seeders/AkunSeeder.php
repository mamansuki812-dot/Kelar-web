<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AkunSeeder extends Seeder
{
    /**
     * Chart of Accounts (COA) minimal — master data referensi.
     * Berisi akun yang sudah dipakai kode existing + akun untuk fase berikutnya.
     * Idempoten: updateOrInsert berdasarkan kode_akun.
     */
    public function run(): void
    {
        $akuns = [
            ['kode_akun' => '1-1000', 'nama_akun' => 'Kas', 'tipe' => 'aset', 'saldo_normal' => 'debit', 'is_manual_entry' => true],
            ['kode_akun' => '1-1100', 'nama_akun' => 'Bank', 'tipe' => 'aset', 'saldo_normal' => 'debit', 'is_manual_entry' => true],
            ['kode_akun' => '1-1200', 'nama_akun' => 'Persediaan Barang', 'tipe' => 'aset', 'saldo_normal' => 'debit', 'is_manual_entry' => true],
            ['kode_akun' => '2-2000', 'nama_akun' => 'Utang Usaha', 'tipe' => 'liabilitas', 'saldo_normal' => 'kredit', 'is_manual_entry' => true],
            ['kode_akun' => '4-4000', 'nama_akun' => 'Pendapatan Penjualan', 'tipe' => 'pendapatan', 'saldo_normal' => 'kredit', 'is_manual_entry' => true],
            ['kode_akun' => '4-4100', 'nama_akun' => 'Koreksi Persediaan', 'tipe' => 'pendapatan', 'saldo_normal' => 'kredit', 'is_manual_entry' => true],
            ['kode_akun' => '5-5000', 'nama_akun' => 'Harga Pokok Penjualan', 'tipe' => 'hpp', 'saldo_normal' => 'debit', 'is_manual_entry' => true],
            ['kode_akun' => '6-6000', 'nama_akun' => 'Beban Operasional', 'tipe' => 'beban', 'saldo_normal' => 'debit', 'is_manual_entry' => true],
            // Auto-only: dipakai jurnal penyesuaian stok, tidak boleh dipilih manual di form.
            ['kode_akun' => '6-6100', 'nama_akun' => 'Kerugian Persediaan', 'tipe' => 'beban', 'saldo_normal' => 'debit', 'is_manual_entry' => false],
        ];

        foreach ($akuns as $akun) {
            DB::table('akun')->updateOrInsert(
                ['kode_akun' => $akun['kode_akun']],
                array_merge($akun, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
