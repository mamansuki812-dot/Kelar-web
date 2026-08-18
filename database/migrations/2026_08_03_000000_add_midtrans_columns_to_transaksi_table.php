<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('midtrans_order_id', 50)->nullable()->unique();
            $table->string('midtrans_status', 20)->nullable();
            $table->string('midtrans_payment_type', 30)->nullable();
            $table->string('midtrans_transaction_id', 50)->nullable();
        });

        // Perubahan ENUM di MySQL tidak aman via change(); pakai raw SQL.
        DB::statement("ALTER TABLE transaksi MODIFY metode_pembayaran ENUM('tunai','transfer','qris','midtrans') NOT NULL DEFAULT 'tunai'");
        DB::statement("ALTER TABLE transaksi MODIFY status ENUM('pending','selesai','dibatalkan') NOT NULL DEFAULT 'selesai'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transaksi MODIFY metode_pembayaran ENUM('tunai','transfer','qris') NOT NULL DEFAULT 'tunai'");
        DB::statement("ALTER TABLE transaksi MODIFY status ENUM('selesai','dibatalkan') NOT NULL DEFAULT 'selesai'");

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropUnique('transaksi_midtrans_order_id_unique');
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_status',
                'midtrans_payment_type',
                'midtrans_transaction_id',
            ]);
        });
    }
};
