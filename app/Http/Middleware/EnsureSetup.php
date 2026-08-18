<?php

namespace App\Http\Middleware;

use App\Models\Jurnal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard Setup Awal Wajib / Onboarding (Fase 5).
 *
 * Jika tabel jurnal masih kosong (sistem belum "disiapkan" / belum ada
 * jurnal pembukaan), maka:
 *  - admin & pemilik   -> diarahkan ke halaman Setup Awal (kecuali logout,
 *    halaman setup itu sendiri, dan Reset Data);
 *  - role lain (kasir) -> ditampilkan halaman pesan ramah (HTTP 200, bukan 500),
 *    bukan form tulen, berisi instruksi "hubungi admin/pemilik".
 *
 * Pengecualian route yang harus tetap bisa diakses saat setup belum selesai:
 * - logout, pengaturan.setup-awal (+store), pengaturan.reset-data (+store).
 *
 * Dipasang pada grup route yang sudah dimiliki middleware `auth`.
 */
class EnsureSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $jurnalSudahTerisi = Jurnal::exists();
        if ($jurnalSudahTerisi) {
            return $next($request);
        }

        $allowed = [
            'logout',
            'pengaturan.setup-awal',
            'pengaturan.setup-awal.store',
            'pengaturan.reset-data',
            'pengaturan.reset-data.store',
        ];

        $routeName = $request->route()?->getName();
        if (in_array($routeName, $allowed, true)) {
            return $next($request);
        }

        // Admin/pemilik selalu diarahkan ke Setup Awal hingga jurnal pembukaan dibuat.
        if (in_array(auth()->user()->role, ['admin', 'pemilik'], true)) {
            return redirect()->route('pengaturan.setup-awal');
        }

        // Non-admin (kasir/gudang): tampilkan halaman "sistem belum disiapkan".
        return response()->view('pengaturan.sistem-belum-disiapkan', [], 200);
    }
}