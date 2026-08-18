<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Komponen penyusun paket (BOM) — pivot eksplisit tabel `produk_komponen`.
 * Fondasi untuk integrasi paket ke TransaksiService (pekerjaan lanjutan).
 */
class ProdukKomponen extends Model
{
    protected $table = 'produk_komponen';

    protected $fillable = [
        'produk_paket_id',
        'produk_komponen_id',
        'qty_per_paket',
    ];

    protected $casts = [
        'qty_per_paket' => 'decimal:2',
    ];

    /**
     * Produk paket (induk).
     */
    public function produkPaket(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_paket_id');
    }

    /**
     * Produk komponen (bagian dari paket).
     */
    public function produkKomponen(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_komponen_id');
    }
}
