<?php

namespace App\Services;

use App\Models\ShiftKasir;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * FASE 1 (revisi dosen) — Manajemen Shift Kasir (Buka/Tutup Kasir).
 *
 * Aturan:
 *  - Satu kasir hanya boleh memiliki SATU shift berstatus 'buka' secara
 *    bersamaan (bukaShift menolak bila masih ada shift 'buka' miliknya).
 *  - Tutup shift menghitung saldo_akhir_sistem = saldo_awal + total transaksi
 *    TUNAI berstatus 'selesai' selama shift; selisih = fisik − sistem.
 *  - Query agregasi memakai lockForUpdate terhadap baris shift untuk mencegah
 *    race condition saat 2 request tutup bersamaan.
 */
class ShiftKasirService
{
    /**
     * Ambil shift aktif (status 'buka') milik seorang user.
     */
    public function shiftAktif(int $userId): ?ShiftKasir
    {
        return ShiftKasir::where('user_id', $userId)
            ->where('status', 'buka')
            ->latest('id')
            ->first();
    }

    /**
     * Buka shift baru untuk kasir.
     *
     * @throws \RuntimeException bila kasir masih punya shift 'buka' yang belum ditutup.
     */
    public function bukaShift(User $kasir, float $saldoAwal): ShiftKasir
    {
        if ($this->shiftAktif($kasir->id) !== null) {
            throw new \RuntimeException('Anda masih memiliki shift yang belum ditutup. Tutup shift sebelumnya terlebih dahulu.');
        }

        return DB::transaction(function () use ($kasir, $saldoAwal) {
            $adaShiftAktif = ShiftKasir::where('user_id', $kasir->id)
                ->where('status', 'buka')
                ->lockForUpdate()
                ->exists();

            if ($adaShiftAktif) {
                throw new \RuntimeException('Anda masih memiliki shift yang belum ditutup. Tutup shift sebelumnya terlebih dahulu.');
            }

            return ShiftKasir::create([
                'user_id'    => $kasir->id,
                'tanggal'    => now()->toDateString(),
                'jam_buka'   => now(),
                'saldo_awal' => max(0, (float) $saldoAwal),
                'status'     => 'buka',
            ]);
        });
    }

    /**
     * Tutup shift: hitung saldo_akhir_sistem & selisih, lalu set status 'tutup'.
     */
    public function tutupShift(ShiftKasir $shift, float $saldoAkhirFisik, ?string $catatan): ShiftKasir
    {
        return DB::transaction(function () use ($shift, $saldoAkhirFisik, $catatan) {
            $locked = ShiftKasir::whereKey($shift->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'buka') {
                throw new \RuntimeException('Shift ini sudah ditutup.');
            }

            $totalTunai = (float) Transaksi::where('shift_kasir_id', $locked->id)
                ->where('metode_pembayaran', 'tunai')
                ->where('status', 'selesai')
                ->sum('total_bayar');

            $saldoSistem = (float) $locked->saldo_awal + $totalTunai;
            $fisik       = max(0, (float) $saldoAkhirFisik);

            $locked->update([
                'jam_tutup'         => now(),
                'saldo_akhir_sistem'=> $saldoSistem,
                'saldo_akhir_fisik' => $fisik,
                'selisih'           => round($fisik - $saldoSistem, 2),
                'status'            => 'tutup',
                'catatan'           => $catatan ?: null,
            ]);

            return $locked;
        });
    }

    /**
     * Jumlah transaksi tunai & total per shift (untuk ringkasan di form tutup).
     */
    public function ringkasanShift(ShiftKasir $shift): array
    {
        $query = Transaksi::where('shift_kasir_id', $shift->id)->where('status', 'selesai');

        $tunai = (clone $query)->where('metode_pembayaran', 'tunai');
        $nonTunai = (clone $query)->where('metode_pembayaran', '!=', 'tunai');

        return [
            'total_transaksi' => (clone $query)->count(),
            'total_tunai'     => (clone $query)->where('metode_pembayaran', 'tunai')->sum('total_bayar'),
            'total_non_tunai' => (clone $query)->where('metode_pembayaran', '!=', 'tunai')->sum('total_bayar'),
            'jumlah_tunai'    => (clone $tunai)->count(),
            'jumlah_non_tunai'=> (clone $nonTunai)->count(),
        ];
    }
}