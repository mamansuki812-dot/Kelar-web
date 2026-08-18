<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StokHistory extends Model
{
    protected $table = 'stok_history';

    // Model ini hanya memiliki timestamps 'created_at' saja (no updated_at)
    const UPDATED_AT = null;

    protected $fillable = [
        'produk_id',
        'user_id',
        'jenis',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'keterangan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke Produk
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Relasi ke User (Kasir/Admin/Gudang)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
