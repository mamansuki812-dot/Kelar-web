# Kelar-web

**Sistem Point of Sales (POS) KELAR** — manajemen penjualan, inventori, dan laporan keuangan (SAK EMKM) berbasis web, dengan dukungan scanner barcode (keyboard-wedge & kamera via zxing-wasm) dan pembayaran online Midtrans Snap.

## Tech Stack

- Backend: Laravel 12 (PHP ^8.2 / 8.3)
- Database: MySQL 8.0
- Frontend: Tailwind CSS v3.4 + Vite + Blade
- Scan barcode kamera: `barcode-detector` (zxing-wasm, di-bundle lokal — tanpa CDN)
- PDF laporan: barryvdh/laravel-dompdf
- Payment: midtrans/midtrans-php (Snap), mode sandbox default

## Struktur

- `app/` — Controllers, Services (logika bisnis), Models, Middleware
- `resources/views/` — Blade (Tailwind)
- `database/migrations/` + `seeders/`
- `DEPLOYMENT.md` — panduan deploy produksi (PaaS: Railway / Forge / Heroku)
- `.env.production.example` — template konfigurasi produksi (placeholder, aman)

## Instalasi (lokal)

```bash
composer install
cp .env.example .env       # lalu isi DB_* sesuai database Anda
php artisan key:generate
php artisan migrate --seed
npm install
npm run build              # atau: npm run dev (development)
php artisan serve
```

Login default hasil seeder: username `admin`, password `admin123` (**wajib diganti di produksi**).

## Catatan Keamanan

- `.env` (berisi kredensial DB & Midtrans server key) di-`gitignore` — jangan pernah di-commit.
- Konfigurasi produksi: `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, Midtrans sandbox — lihat `DEPLOYMENT.md` sebelum go-live.