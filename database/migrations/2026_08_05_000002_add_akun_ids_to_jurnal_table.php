<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Kolom string akun_debit/akun_kredit LAMA tetap dipertahankan agar kode
     * lama yang masih membacanya tidak error selama masa transisi.
     */
    public function up(): void
    {
        Schema::table('jurnal', function (Blueprint $table) {
            $table->unsignedBigInteger('akun_debit_id')->nullable()->after('akun_kredit');
            $table->unsignedBigInteger('akun_kredit_id')->nullable()->after('akun_debit_id');
            $table->index('akun_debit_id');
            $table->index('akun_kredit_id');
            $table->foreign('akun_debit_id')->references('id')->on('akun')->onDelete('set null');
            $table->foreign('akun_kredit_id')->references('id')->on('akun')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal', function (Blueprint $table) {
            $table->dropForeign(['akun_debit_id']);
            $table->dropForeign(['akun_kredit_id']);
            $table->dropIndex(['akun_debit_id']);
            $table->dropIndex(['akun_kredit_id']);
            $table->dropColumn(['akun_debit_id', 'akun_kredit_id']);
        });
    }
};
