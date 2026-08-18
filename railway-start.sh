#!/bin/sh
set -e

cd /app

echo "[KELAR] 1/6 - storage:link"
php artisan storage:link || echo "  (storage link sudah ada / gagal, diabaikan)"

echo "[KELAR] 2/6 - migrate"
php artisan migrate --force

echo "[KELAR] 3/6 - seed otomatis (hanya jika data belum ada)"
php artisan app:ensure-seed

echo "[KELAR] 4/6 - cache config/route/view"
php artisan config:cache || echo "  (config:cache gagal, dilewati)"
php artisan route:cache || echo "  (route:cache gagal, dilewati)"
php artisan view:cache || echo "  (view:cache gagal, dilewati)"

echo "[KELAR] 5/6 - jalankan server"
php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
