<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'kode_transaksi',
        'user_id',
        'shift_kasir_id',
        'tanggal_transaksi',
        'total_harga',
        'diskon',
        'total_bayar',
        'jumlah_bayar',
        'kembalian',
        'donasi',
        'metode_pembayaran',
        'status',
        'catatan',
        'midtrans_order_id',
        'midtrans_status',
        'midtrans_payment_type',
        'midtrans_transaction_id',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'total_harga'       => 'decimal:2',
        'diskon'            => 'decimal:2',
        'total_bayar'       => 'decimal:2',
        'jumlah_bayar'      => 'decimal:2',
        'kembalian'         => 'decimal:2',
        'donasi'            => 'decimal:2',
    ];

    /**
     * Relasi: setiap transaksi dicatat oleh satu user/kasir.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: setiap transaksi dapat dikaitkan ke shift kasir yang sedang berjalan.
     */
    public function shiftKasir(): BelongsTo
    {
        return $this->belongsTo(ShiftKasir::class, 'shift_kasir_id');
    }

    /**
     * Relasi: setiap transaksi memiliki banyak detail item.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailTransaksi::class, 'transaksi_id');
    }
}
