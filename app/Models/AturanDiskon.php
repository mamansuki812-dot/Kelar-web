<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aturan diskon otomatis per produk (Fase 2).
 * Saat produk ditambahkan ke keranjang POS, aturan aktif yang sesuai
 * akan otomatis diisi sebagai tipe_diskon/nilai_diskon pada baris keranjang.
 */
class AturanDiskon extends Model
{
    use HasFactory;

    protected $table = 'aturan_diskon';

    protected $fillable = [
        'produk_id',
        'tipe_diskon',
        'nilai_diskon',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'nilai_diskon'   => 'decimal:2',
        'tanggal_mulai'  => 'date',
        'tanggal_selesai'=> 'date',
        'is_active'      => 'boolean',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Apakah aturan masih berlaku pada tanggal tertentu (default hari ini).
     * Aktif secara boolean DAN dalam rentang tanggal (jika diisi).
     */
    public function isBerlaku(?string $tanggal = null): bool
    {
        $tgl = $tanggal ?: today()->toDateString();
        if (!$this->is_active) {
            return false;
        }
        if ($this->tanggal_mulai && $tgl < $this->tanggal_mulai->toDateString()) {
            return false;
        }
        if ($this->tanggal_selesai && $tgl > $this->tanggal_selesai->toDateString()) {
            return false;
        }
        return true;
    }
}
