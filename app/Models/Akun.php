<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    protected $table = 'akun';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'tipe',
        'saldo_normal',
        'is_manual_entry',
    ];

    /**
     * Resolusi nama akun (string) ke id (COA), normalisasi LOWER+TRIM.
     * Dipakai bersama oleh TransaksiService dan JurnalService agar perilaku
     * pencocokan seragam dengan BackfillAkunJurnal. Cache statik per-proses.
     */
    public static function resolveIdByName(string $nama): ?int
    {
        static $cache = [];

        $key = mb_strtolower(trim($nama));
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = static::query()
                ->whereRaw('LOWER(TRIM(nama_akun)) = ?', [$key])
                ->value('id');
        }

        return $cache[$key];
    }
}
