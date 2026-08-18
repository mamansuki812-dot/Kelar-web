<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 1 (revisi dosen) — tabel shift kasir (buka/tutup kasir harian).
 * Backward-compatible: tabel baru, tidak menyentuh data existing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_kasir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('tanggal');
            $table->dateTime('jam_buka');
            $table->dateTime('jam_tutup')->nullable();
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->decimal('saldo_akhir_sistem', 15, 2)->nullable();
            $table->decimal('saldo_akhir_fisik', 15, 2)->nullable();
            $table->decimal('selisih', 15, 2)->nullable();
            $table->enum('status', ['buka', 'tutup'])->default('buka');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_kasir');
    }
};