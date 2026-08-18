<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom diskon per baris detail transaksi (backward-compatible: default 0/nullable).
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->string('tipe_diskon', 20)->nullable()->after('harga_satuan'); // nominal | persen
            $table->decimal('nilai_diskon', 15, 2)->default(0)->after('tipe_diskon');
        });

        // Tabel aturan diskon otomatis per produk (Fase 2).
        Schema::create('aturan_diskon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->string('tipe_diskon', 20); // nominal | persen
            $table->decimal('nilai_diskon', 15, 2);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aturan_diskon');
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->dropColumn(['nilai_diskon', 'tipe_diskon']);
        });
    }
};