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
        Schema::create('stok_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('jenis', ['masuk', 'keluar', 'penyesuaian']);
            $table->integer('jumlah')->default(0);
            $table->integer('stok_sebelum')->default(0);
            $table->integer('stok_sesudah')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_history');
    }
};
