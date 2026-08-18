<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            // kode_produk juga kita siapkan sebagai wadah scan barcode fisik / kamera
            $table->string('kode_produk', 50)->unique();
            $table->string('nama_produk', 150);
            
            // Relasi ke tabel kategori (Jika kategori dihapus, produk tidak terhapus tapi restricted/cascade)
            // Di sini kita pakai restrict agar kategori yang ada produknya tidak bisa sembarangan dihapus
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            
            $table->decimal('harga_beli', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(5);
            $table->string('satuan', 20)->default('pcs');
            $table->string('gambar', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};