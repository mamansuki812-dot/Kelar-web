<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 1 (revisi dosen) — kolom tanggal_kadaluarsa pada master produk.
     * Fondasi Fase 3 (bundling produk mendekati kadaluarsa). Belum dipakai
     * logika bisnis di fase ini — hanya penyediaan kolom (NULLABLE, harmless).
     */
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->date('tanggal_kadaluarsa')->nullable()->after('satuan');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('tanggal_kadaluarsa');
        });
    }
};
