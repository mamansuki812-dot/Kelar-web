<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Jurnal;
use Illuminate\Support\Carbon;

class AkuntansiService
{
    /**
     * Saldo akun (berdasarkan saldo normal) dari tabel jurnal, per tanggal posisi.
     *
     * Akun bersaldo normal debit (aset, beban, hpp):
     *   saldo = SUM(nominal debit) - SUM(nominal kredit)
     * Akun bersaldo normal kredit (liabilitas, ekuitas, pendapatan):
     *   saldo = SUM(nominal kredit) - SUM(nominal debit)
     *
     * @param string $kodeAtauNamaAkun kode_akun atau nama_akun pada COA.
     * @param string|null $tanggalPosisi Tanggal posisi (Y-m-d); null = seluruh jurnal.
     */
    public function saldoAkun(string $kodeAtauNamaAkun, ?string $tanggalPosisi = null): float
    {
        static $cache = [];

        $key = mb_strtolower(trim($kodeAtauNamaAkun)) . '|' . ($tanggalPosisi ?: 'all');
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        $akun = Akun::query()
            ->where('kode_akun', $kodeAtauNamaAkun)
            ->orWhereRaw('LOWER(TRIM(nama_akun)) = ?', [mb_strtolower(trim($kodeAtauNamaAkun))])
            ->first();

        if (!$akun) {
            return 0.0;
        }

        $filter = function ($query) use ($tanggalPosisi) {
            return $query->whereDate('tanggal', '<=', Carbon::parse($tanggalPosisi)->endOfDay());
        };

        $debit  = Jurnal::where('akun_debit_id', $akun->id)
            ->when($tanggalPosisi, $filter)
            ->sum('nominal');

        $kredit = Jurnal::where('akun_kredit_id', $akun->id)
            ->when($tanggalPosisi, $filter)
            ->sum('nominal');

        $saldo = $akun->saldo_normal === 'debit'
            ? $debit - $kredit
            : $kredit - $debit;

        return $cache[$key] = (float) $saldo;
    }
}
