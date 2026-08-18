<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->boolean('is_manual_entry')->default(true)->after('saldo_normal');
        });

        // Akun sistem auto-only (dipakai jurnal otomatis) tidak boleh dipilih manual.
        DB::table('akun')
            ->where('kode_akun', '6-6100')
            ->update(['is_manual_entry' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akun', function (Blueprint $table) {
            $table->dropColumn('is_manual_entry');
        });
    }
};
