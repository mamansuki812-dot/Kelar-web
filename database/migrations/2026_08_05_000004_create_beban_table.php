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
        Schema::create('beban', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('akun_id');
            $table->decimal('nominal', 15, 2);
            $table->string('keterangan', 255)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('akun_id')->references('id')->on('akun')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beban');
    }
};
