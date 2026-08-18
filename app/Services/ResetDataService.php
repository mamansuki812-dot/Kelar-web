<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Beban;
use App\Models\DetailTransaksi;
use App\Models\Jurnal;
use App\Models\Produk;
use App\Models\StokHistory;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Fitur Reset Data (fondasi sesi deploy / demo).
 *
 * Menghapus seluruh data transaksional yang terdaftar dalam satu transaksi DB,
 * lalu menulis jejak reset ke file terpisah (storage/logs/reset-data.log)
 * karena tabel audit_log ikut dikosongkan oleh reset ini.
 *
 * Yang DIhapus : detail_transaksi, transaksi, jurnal, stok_history, beban,
 *                audit_log.
 * Yang DIPERTAHANKAN : users, kategori, produk, supplier, akun,
 *                aturan_diskon, produk_komponen.
 * Stok seluruh produk di-set ulang ke 0.
 */
class ResetDataService
{
    /**
     * @return array{detail_transaksi:int, transaksi:int, jurnal:int, stok_history:int,
     *               beban:int, audit_log:int}
     */
    public function resetData(User $user, string $ip): array
    {
        $counts = DB::transaction(function () {
            $counts = [
                'detail_transaksi' => DetailTransaksi::count(),
                'transaksi'        => Transaksi::count(),
                'jurnal'           => Jurnal::count(),
                'stok_history'     => StokHistory::count(),
                'beban'            => Beban::count(),
                'audit_log'        => AuditLog::count(),
            ];

            // Urutan aman sesuai FK dari migrasi:
            // detail_transaksi (child transaksi) -> transaksi -> jurnal
            // (jurnal.transaksi_id SET NULL saat transaksi dihapus, tapi kita hapus jurnal juga).
            DetailTransaksi::query()->delete();
            Jurnal::query()->delete();
            Transaksi::query()->delete();
            StokHistory::query()->delete();
            Beban::query()->delete();
            AuditLog::query()->delete();

            Produk::query()->update(['stok' => 0]);

            return $counts;
        });

        $this->tulisLogFile($user, $ip, $counts);

        return $counts;
    }

    /**
     * Log jejak reset ke storage/logs/reset-data.log (JSON per baris).
     * Tabel audit_log ikut di-reset sehingga log ini menjadi jejak permanen.
     */
    private function tulisLogFile(User $user, string $ip, array $counts): void
    {
        $line = json_encode([
            'waktu'      => now()->toDateTimeString(),
            'user_id'    => $user->id,
            'user_name'  => $user->name,
            'role'       => $user->role,
            'ip'         => $ip,
            'aksi'       => 'RESET-SEMUA-DATA',
            'dihapus'    => $counts,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $logPath = storage_path('logs/reset-data.log');
        File::ensureDirectoryExists(dirname($logPath));
        File::append($logPath, $line . PHP_EOL . PHP_EOL);
    }
}