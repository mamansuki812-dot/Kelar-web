<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 1 (revisi dosen) — kolom shift_kasir_id pada tabel transaksi agar
 * tiap transaksi bisa ditelusuri ke shift kasir yang sedang berjalan.
 * Backward-compatible: nullable + FK nullOnDelete, transaksi lama tetap utuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreignId('shift_kasir_id')->nullable()
                ->after('user_id')
                ->constrained('shift_kasir')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_kasir_id');
        });
    }
};