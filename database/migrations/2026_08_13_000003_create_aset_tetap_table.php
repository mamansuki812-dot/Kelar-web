<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 5 (revisi dosen) — Aset Tetap: tabel master aset tetap + akun COA untuk
 * pencatatan jurnal pembukaan. Aset disetor/dibeli dari modal sehingga jurnal
 * Debit "Aset Tetap" / Kredit "Modal Pemilik" menjaga keseimbangan neraca.
 * Backward-compatible: tabel baru, akun baru (idempoten updateOrInsert).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aset_tetap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_aset', 150);
            $table->string('kode_aset', 30)->nullable()->unique();
            $table->string('kategori_aset', 100)->nullable();
            $table->date('tanggal_perolehan');
            $table->decimal('harga_perolehan', 15, 2);
            $table->decimal('akumulasi_penyusutan', 15, 2)->default(0);
            $table->decimal('nilai_residu', 15, 2)->default(0);
            $table->integer('masa_manfaat_bulan')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $akun = [
            ['kode_akun' => '1-1300', 'nama_akun' => 'Aset Tetap', 'tipe' => 'aset', 'saldo_normal' => 'debit', 'is_manual_entry' => false],
            ['kode_akun' => '1-1400', 'nama_akun' => 'Akumulasi Penyusutan Aset Tetap', 'tipe' => 'aset', 'saldo_normal' => 'kredit', 'is_manual_entry' => false],
        ];

        foreach ($akun as $a) {
            DB::table('akun')->updateOrInsert(
                ['kode_akun' => $a['kode_akun']],
                array_merge($a, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_tetap');
        DB::table('akun')->whereIn('kode_akun', ['1-1300', '1-1400'])->delete();
    }
};