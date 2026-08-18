<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftKasir extends Model
{
    protected $table = 'shift_kasir';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_buka',
        'jam_tutup',
        'saldo_awal',
        'saldo_akhir_sistem',
        'saldo_akhir_fisik',
        'selisih',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'jam_buka'          => 'datetime',
        'jam_tutup'         => 'datetime',
        'saldo_awal'        => 'decimal:2',
        'saldo_akhir_sistem'=> 'decimal:2',
        'saldo_akhir_fisik' => 'decimal:2',
        'selisih'           => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaksis(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'shift_kasir_id');
    }
}