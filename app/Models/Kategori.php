<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    protected $table = 'kategori';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    /**
     * Relasi: satu kategori punya banyak produk.
     */
    public function produk(): HasMany
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }

    /**
     * Cek apakah kategori ini masih dipakai produk yang aktif.
     */
    public function masihDipakai(): bool
    {
        return $this->produk()->where('is_active', true)->exists();
    }
}
