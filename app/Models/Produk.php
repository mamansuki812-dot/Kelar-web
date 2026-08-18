<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'kategori_id',
        'harga_beli',
        'harga_jual',
        'stok',
        'stok_minimum',
        'satuan',
        'tanggal_kadaluarsa',
        'gambar',
        'is_active',
        'is_paket',
    ];

    protected $casts = [
        'harga_beli'           => 'decimal:2',
        'harga_jual'           => 'decimal:2',
        'is_active'            => 'boolean',
        'is_paket'             => 'boolean',
        'tanggal_kadaluarsa'   => 'date',
    ];

    /**
     * Relasi: setiap produk milik satu kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /**
     * Relasi: aturan diskon yang dimiliki produk (Fase 2).
     */
    public function aturanDiskon(): HasMany
    {
        return $this->hasMany(AturanDiskon::class, 'produk_id');
    }

    /**
     * Relasi (BOM): komponen-komponen yang menyusun paket ini.
     * Pivot `produk_komponen` membawa qty_per_paket.
     */
    public function komponen(): BelongsToMany
    {
        return $this->belongsToMany(
            Produk::class,
            'produk_komponen',
            'produk_paket_id',
            'produk_komponen_id'
        )->withPivot('qty_per_paket');
    }

    /**
     * Relasi (BOM): paket-paket yang memakai produk ini sebagai komponen.
     */
    public function paketInduk(): BelongsToMany
    {
        return $this->belongsToMany(
            Produk::class,
            'produk_komponen',
            'produk_komponen_id',
            'produk_paket_id'
        );
    }
}
