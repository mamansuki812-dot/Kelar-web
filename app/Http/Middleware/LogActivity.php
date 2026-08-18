<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!Auth::check() || $request->method() === 'GET') {
            return $response;
        }

        $user = Auth::user();

        $modelParam = $request->route()?->parameter('kategori')
            ?? $request->route()?->parameter('produk')
            ?? $request->route()?->parameter('user')
            ?? $request->route()?->parameter('supplier')
            ?? $request->route()?->parameter('aturan_diskon')
            ?? $request->route()?->parameter('transaksi');

        $modelId = $modelParam instanceof \Illuminate\Database\Eloquent\Model ? $modelParam->getKey() : $modelParam;

        if (!$modelId) {
            $modelId = $this->extractIdFromRedirect($response->headers->get('Location'));
        }

        $this->log(
            user: $user,
            aksi: $this->mapMethod($request->method()),
            model: $this->guessModel($request),
            modelId: $modelId,
            request: $request
        );

        return $response;
    }

    private function log($user, string $aksi, string $model, $modelId, Request $request): void
    {
        AuditLog::create([
            'user_id'    => $user->id,
            'aksi'       => $aksi,
            'model'      => $model,
            'model_id'   => $modelId,
            'data_baru'  => $this->sanitizedPayload($request),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function mapMethod(string $method): string
    {
        return match ($method) {
            'POST'   => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default  => 'other',
        };
    }

    private function guessModel(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, 'kategori'))       return 'Kategori';
        if (str_contains($path, 'produk'))         return 'Produk';
        if (str_contains($path, 'users'))          return 'User';
        if (str_contains($path, 'supplier'))       return 'Supplier';
        if (str_contains($path, 'transaksi'))      return 'Transaksi';
        if (str_contains($path, 'inventori'))      return 'Inventori';
        if (str_contains($path, 'beban'))          return 'Beban';
        if (str_contains($path, 'shift'))          return 'ShiftKasir';
        if (str_contains($path, 'aturan-diskon'))  return 'AturanDiskon';
        if (str_contains($path, 'pengaturan'))     return 'Pengaturan';
        if (str_contains($path, 'midtrans'))       return 'Midtrans';
        if (str_contains($path, 'login'))          return 'Auth';
        if (str_contains($path, 'logout'))         return 'Auth';

        return 'Other';
    }

    private function extractIdFromRedirect(?string $location): ?int
    {
        if (!$location) {
            return null;
        }

        if (preg_match('#/(\d+)$#', $location, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function sanitizedPayload(Request $request): ?array
    {
        $data = $request->except([
            '_token', '_method', 'password', 'password_confirmation',
        ]);

        return empty($data) ? null : $data;
    }
}
