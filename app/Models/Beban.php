<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Beban extends Model
{
    protected $table = 'beban';

    protected $fillable = [
        'tanggal',
        'akun_id',
        'nominal',
        'keterangan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    /**
     * Relasi ke akun beban (COA).
     */
    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_id');
    }

    /**
     * Relasi ke pengguna yang mencatat.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
