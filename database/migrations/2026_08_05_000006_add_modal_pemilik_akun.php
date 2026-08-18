<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 4.5 — akun ekuitas "Modal Pemilik" untuk jurnal pembukaan.
 * Migration data (idempoten, updateOrInsert by kode_akun) — tidak mengubah
 * migration/seeder akun yang sudah jalan.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('akun')->updateOrInsert(
            ['kode_akun' => '3-3000'],
            [
                'nama_akun'       => 'Modal Pemilik',
                'tipe'            => 'ekuitas',
                'saldo_normal'    => 'kredit',
                // Hanya ditulis via command akuntansi:jurnal-pembukaan, bukan form manual.
                'is_manual_entry' => false,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('akun')->where('kode_akun', '3-3000')->delete();
    }
};
