<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 4: Akun untuk menampung selisih kecil kembalian yang direlakan pelanggan
 * sebagai donasi (pendapatan non-operasional) + kolom donasi pada transaksi.
 * Idempoten: updateOrInsert akun; cek kolom sebelum menambah.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('akun')->updateOrInsert(
            ['kode_akun' => '4-4900'],
            [
                'nama_akun'       => 'Pendapatan Non-Operasional - Donasi Kembalian',
                'tipe'            => 'pendapatan',
                'saldo_normal'    => 'kredit',
                'is_manual_entry' => false,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]
        );

        if (!Schema::hasColumn('transaksi', 'donasi')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->decimal('donasi', 15, 2)->nullable()->after('kembalian');
            });
        }
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn('donasi');
        });
        DB::table('akun')->where('kode_akun', '4-4900')->delete();
    }
};
