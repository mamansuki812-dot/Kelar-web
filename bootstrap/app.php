<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'       => \App\Http\Middleware\CheckRole::class,
            'pengaturan-ready' => \App\Http\Middleware\EnsureSetup::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\LogActivity::class,
        ]);

        // Trusted proxies (deploy PaaS di belakang reverse proxy / TLS termination).
        // Dikontrol via TRUSTED_PROXIES di .env:
        //   - "*"            -> percayai semua proxy (umum untuk Railway/Forge/Heroku)
        //   - kosong/unset  -> tidak ada proxy yang dipercaya
        //   - IP terpisah koma -> daftar IP proxy tertentu (e.g. 10.0.0.1,10.0.0.2)
        $trustedProxies = env('TRUSTED_PROXIES', '*');
        if ($trustedProxies !== false && $trustedProxies !== '' && $trustedProxies !== null) {
            $middleware->trustProxies(at: $trustedProxies === '*'
                ? '*'
                : array_map('trim', explode(',', $trustedProxies)));
        }

        // Webhook Midtrans dipanggil oleh server Midtrans tanpa session/CSRF
        $middleware->validateCsrfTokens(except: [
            'midtrans/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
