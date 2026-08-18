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
        Schema::create('jurnal', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('kode_jurnal', 30);
            $table->unsignedBigInteger('transaksi_id')->nullable();
            $table->string('akun_debit', 100);
            $table->string('akun_kredit', 100);
            $table->decimal('nominal', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('transaksi_id')->references('id')->on('transaksi')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal');
    }
};
