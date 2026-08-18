<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('akun', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun', 30)->unique();
            $table->string('nama_akun', 100);
            $table->enum('tipe', ['aset', 'liabilitas', 'ekuitas', 'pendapatan', 'beban', 'hpp']);
            $table->enum('saldo_normal', ['debit', 'kredit']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};
