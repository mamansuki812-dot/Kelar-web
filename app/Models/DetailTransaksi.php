<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';

    // Tabel detail_transaksi tidak membutuhkan kolom updated_at bawaan Laravel
    const UPDATED_AT = null;

    protected $fillable = [
        'transaksi_id',
        'produk_id',
        'jumlah',
        'harga_satuan',
        'tipe_diskon',
        'nilai_diskon',
        'subtotal',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'nilai_diskon' => 'decimal:2',
        'subtotal'     => 'decimal:2',
    ];

    /**
     * Relasi: detail transaksi belongs to transaksi induk.
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    /**
     * Relasi: detail transaksi belongs to produk.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
