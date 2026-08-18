<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsetTetap extends Model
{
    protected $table = 'aset_tetap';

    protected $fillable = [
        'user_id',
        'nama_aset',
        'kode_aset',
        'kategori_aset',
        'tanggal_perolehan',
        'harga_perolehan',
        'akumulasi_penyusutan',
        'nilai_residu',
        'masa_manfaat_bulan',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'tanggal_perolehan'   => 'date',
        'harga_perolehan'     => 'decimal:2',
        'akumulasi_penyusutan'=> 'decimal:2',
        'nilai_residu'        => 'decimal:2',
        'masa_manfaat_bulan'  => 'integer',
        'is_active'           => 'boolean',
    ];

    /**
     * Nilai buku (book value) = harga perolehan − akumulasi penyusutan.
     */
    public function getNilaiBukuAttribute(): float
    {
        return max(0, (float) $this->harga_perolehan - (float) $this->akumulasi_penyusutan);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
