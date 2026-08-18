# DEPLOYMENT.md — Panduan Deploy Produksi KELAR POS

> Target utama: **Platform PaaS** (Railway, Laravel Forge, Heroku, dll).
> Bahasa Indonesia — ikuti urutan langkah, jangan lewati bagian "Peringatan".

---

## 1. Prasyarat Server / Runtime

| Komponen | Minimal / Rekomendasi |
|---|---|
| PHP | ^8.2 (rekomendasi **8.3**) — `/etc/php/8.3/fpm` di Forge |
| Ekstensi PHP wajib | `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `exif`, `gd`, `dom`, `bcmath`, `zip`, `xml`, `tokenizer` |
| Database | **MySQL 8.0** (Pakai plugin/managed MySQL dari platform) |
| Composer | ^2.x |
| Node.js | ^20 / ^22 (dipakai saat **build aset**, tidak wajib di runtime) |
| NPM | ^10 |

**Catatan penting:** Proses build aset frontend (`npm run build`) bisa dilakukan di mesin lokal lalu hasil `public/build/` ikut di-upload / di-commit, ATAU di-build di server pada tahap build. Untuk PaaS yang build-from-git, pastikan `public/build/` **tidak** di-`.gitignore` jika ingin siap-pakai tanpa build step.

> ⚠️ `composer install --optimize-autoloader` pernah menggantung di lingkungan Windows dev (lihat ai.md). Di server Linux produksi selalu aman dijalankan.

---

## 2. Langkah Deploy Step-by-Step (dari nol)

### 2.1 Siapkan kode sumber
```bash
git init && git add -A && git commit -m "init: KELAR POS deployment baseline"
git remote add origin <url-repo-GitHub> && git push -u origin main
```
`.env` sudah masuk `.gitignore` — pastikan **tidak pernah** ter-commit.

### 2.2 Siapkan aplikasi di platform
- Buat proyek di Railway / situs di Forge / app di Heroku, arahkan ke repo di atas.
- Buat **MySQL 8.0** (Railway: *New → MySQL*; Forge: *Databases → Create*; Heroku: add-on ClearDB/PlanetScale).

### 2.3 Isi konfigurasi environment (PASTI di dashboard platform, bukan file)
Salin nilai dari template **`.env.production.example`** (sudah berisi placeholder aman):

```
APP_ENV=production
APP_DEBUG=false                      # WAJIB false!
APP_URL=https://<domain-asli>        # harus https
APP_KEY=<hasil key:generate>         # lihat langkah 2.4
DB_CONNECTION=mysql
DB_HOST=<host-DB-platform>
DB_PORT=3306
DB_DATABASE=<nama-DB>
DB_USERNAME=<user>
DB_PASSWORD=<password>
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true           # WAJIB true (HTTPS)
SESSION_SAME_SITE=lax
CACHE_STORE=database
QUEUE_CONNECTION=database            # worker TIDAK wajib (lihat §4)
LOG_CHANNEL=daily
LOG_LEVEL=error
TRUSTED_PROXIES=*                    # PaaS di belakang reverse proxy
FILESYSTEM_DISK=local
MIDTRANS_SERVER_KEY=<key-server>
MIDTRANS_CLIENT_KEY=<key-client>
MIDTRANS_IS_PRODUCTION=false         # JANGAN ubah ke true tanpa prosedur §5
```

### 2.4 Generate APP_KEY (sekali saja)
```bash
# di mesin lokal (atau di server): hasilnya TEMPEL ke dashboard platform
php artisan key:generate --show
```
Jangan regenerasi setelah deploy — akan membuat sesi & token lama invalid.

### 2.5 Install dependency & build
Di langkah build platform (Railway *Build Command* / Forge *Deploy Script*) atau manual di server:
```bash
composer install --no-dev --optimize-autoloader --prefer-dist --no-progress
npm install
npm run build          # hasil Vite: public/build/* + manifest.json
```

### 2.6 Migrasi & seed master data
```bash
php artisan migrate --force
php artisan db:seed --force        # user admin, kategori, akun COA, produk fondasi
```

### 2.7 Optimasi cache (urutan ini)
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```
> `route:cache` **aman** — Laravel 12.63 sudah bisa me-serialize closure route `GET /`.

### 2.8 Perizinan storage (bila manual / non-Forge)
```bash
chmod -R 775 storage bootstrap/cache
```

### 2.9 Web server (opsional, untuk Forge/VPS)
- Document root → `<project>/public` (bukan root proyek!).
- Pastikan MIME **WebAssembly** dikenali (wajib untuk scanner kamera POS):
  ```nginx
  types { application/wasm wasm; }
  ```
- Contoh vhost minimal Nginx:
  ```nginx
  server {
      listen 80;
      server_name <domain>;
      root /home/forge/kelar-pos/public;
      index index.php;
      location / { try_files $uri $uri/ /index.php?$query_string; }
      location ~ \.php$ {
          fastcgi_pass unix:/run/php/php8.3-fpm.sock;
          fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
          include fastcgi_params;
      }
  }
  ```
  (Sertifikat TLS: Let's Encrypt via platform/`certbot`.)

### 2.10 Persistensi upload (penting di PaaS)
Filesystem `local` bersifat **ephemeral** jika tanpa volume. Pasang **persistent volume** pada direktori `storage/app/` (Railway: *Volumes → mount ke /app/storage/app*; Heroku: perlu object storage S3 atau sebaran). Tanpa itu, upload gambar produk / logo akan hilang saat restart.

---

## 3. Checklist Pasca-Deploy

1. **Login** → pilih admin, **langsung ganti password** (lihat §5).
2. **Reset Data & Setup Awal** wajib dijalankan sebelum toko dipakai (lihat §5).
3. Uji **transaksi POS** (tunai), barcode manual, dan scan kamera (pastikan `zxing_reader-*.wasm` ter-serve: buka `/build/assets/` → MIME harus `application/wasm`).
4. Uji **Midtrans sandbox**: buat transaksi → cek webhook di `storage/logs/laravel.log` (halaman `GET /midtrans/status?order_id=...`).
5. Uji **export PDF laporan** (Penjualan, Stok, Laba Rugi, Neraca, Jurnal, CaLK).
6. Cek `/up` (health check) → `{"status":"UP"}`.
7. **Log bersih**: `cat storage/logs/laravel.log` — tidak boleh ada `ERROR`/stack trace.
8. Chome DevTools → tab Network saat membuka POS: tidak ada request gagal / cache error.

---

## 4. Scheduler & Queue

- **Scheduler (cron):** TIDAK diperlukan — proyek tidak punya scheduled task (`schedule->`=0). Bilamana ada task baru, pasang:
  ```cron
  * * * * * cd /path/kelar-pos && php artisan schedule:run >> /dev/null 2>&1
  ```
- **Queue worker:** TIDAK diperlukan — seluruh proses sinkron (tanpa `ShouldQueue`), `QUEUE_CONNECTION=database` hanya cadangan. Jangan jalankan `queue:work` di produksi kecuali ada perubahan arsitektur ke async.

---

## 5. Peringatan Eksplisit (WAJIB DIHORMATI)

### 5.1 Password admin default
Seeder membuat akun `admin` / `admin123`. **Setelah deploy pertama, ganti password segera** lewat menu *Users → Edit*. Keputusan ganti = tanggung jawab admin toko.

### 5.2 Reset Data + Setup Awal (alur onboarding)
Sebelum toko benar-benar dipakai:
1. Buka **Pengaturan → Reset Data** (konfirmasi teks `HAPUS SEMUA DATA` + password) — ini meng-nol-kan stok & data transaksional, mempertahankan master data.
2. Ikuti **Setup Awal**: isi kas awal + tanggal cutover → menghasilkan jurnal pembukaan (`JR-OPENING-*`). Seluruh route ter-block sampai langkah ini selesai (middleware `pengaturan-ready`).

### 5.3 Midtrans — kapan boleh `MIDTRANS_IS_PRODUCTION=true`
HANYA setelah: (1) Anda sudah punya **key live** dari dashboard Midtrans (bukan sandbox), (2) diisi & ditest end-to-end di sandbox dulu, (3) mengganti `MIDTRANS_SERVER_KEY`/`MIDTRANS_CLIENT_KEY` ke key live. Tanpa itu, **tetap `false`** (sandbox). Server Key live tidak boleh pernah tampil di frontend/HTML.

---

## 6. Update / Redeploy Selanjutnya

```bash
git pull / git push        # ambil perubahan kode
composer install --no-dev --optimize-autoloader   # jika composer.json berubah
npm install && npm run build                       # jika aset berubah
php artisan migrate --force                        # jika ada migration baru
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link                           # sekali lagi bila perlu
```
Tanpa downtime data: migration `--force` aman karena backward-compatible; DNS/HTTPS dikelola oleh platform.

---

## 7. Referensi Internal

- Template env produksi: `.env.production.example`
- Konteks arsitektur & keputusan: `ai.md`
- Spesifikasi PRD: `MVP-PRD-POS-TokoMajuJaya.md`
- Aturan UI: `DESIGN-GUIDELINES.md`