<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jurnal extends Model
{
    protected $table = 'jurnal';

    // Jurnal only has created_at timestamp
    const UPDATED_AT = null;

    protected $fillable = [
        'tanggal',
        'kode_jurnal',
        'transaksi_id',
        'akun_debit',
        'akun_kredit',
        'akun_debit_id',
        'akun_kredit_id',
        'nominal',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke Transaksi
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    /**
     * Relasi ke akun debit (COA)
     */
    public function akunDebit(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_debit_id');
    }

    /**
     * Relasi ke akun kredit (COA)
     */
    public function akunKredit(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_kredit_id');
    }
}
