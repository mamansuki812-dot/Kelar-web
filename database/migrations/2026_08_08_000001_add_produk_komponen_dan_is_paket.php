<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tandai produk sebagai paket (fondasi BOM). Backward-compatible: semua produk biasa tetap false.
        Schema::table('produk', function (Blueprint $table) {
            $table->boolean('is_paket')->default(false)->after('is_active');
        });

        // Struktur komponen paket (BOM). Produk merupakan relasi ke tabel produk yang sama.
        Schema::create('produk_komponen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_paket_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('produk_komponen_id')->constrained('produk')->cascadeOnDelete();
            $table->decimal('qty_per_paket', 15, 2)->default(1);
            $table->timestamps();

            // Satu komponen tidak boleh dobel dalam satu paket.
            $table->unique(['produk_paket_id', 'produk_komponen_id'], 'produk_komponen_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_komponen');
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('is_paket');
        });
    }
};