# RAILWAY.md — Panduan Deploy KELAR POS ke Railway (untuk Pemula)

> Panduan langkah-demi-langkah paling rinci untuk menaikkan aplikasi **KELAR POS**
> (Laravel 12 + MySQL + Midtrans sandbox) ke **Railway** secara online.
> Bahasa Indonesia. Ikuti urutannya. Jangan lompat-lompat.

---

## 0. Gambaran Besar (Apa yang akan terjadi?)

Railway adalah layanan "deploy otomatis dari GitHub". Begini alurnya:

```
GitHub repo (kelar-web) --(push)--> Railway -> build (composer+npm) -> jalankan
                                                                       |
                                                              MySQL (database)
                                                                       |
                                                       Aplikasi KELAR POS online
```

- Semua kode yang sudah Anda push ke GitHub akan otomatis dibangun (build) dan dijalankan oleh Railway.
- Database MySQL dibuat di Railway, lalu diisi tabel via migrasi.
- Setiap kali Anda `git push`, Railway otomatis membangun ulang (auto-deploy).
- File yang sudah disiapkan otomatis di repo:
  - `nixpacks.toml` → memberi tahu Railway cara menjalankan aplikasi.
  - `railway-start.sh` → perintah yang dijalankan saat aplikasi mulai (migrasi, seeder, cache, lalu melayani request).
  - `app/Console/Commands/EnsureSeed.php` → mengisi data master (admin, kategori, akun COA, produk fondasi) **hanya sekali**, otomatis.

---

## 1. Prasyarat

1. Repo GitHub Anda sudah berisi kode KELAR (commit `Initial commit - Sistem POS KELAR`) — **sudah selesai**.
2. Akun **Railway** (gratis: https://railway.app → *Sign up*; pakai email GitHub/Google).
3. Kredensial **Midtrans sandbox** (opsional untuk tahap awal — bisa diisi belakangan).
4. PHP sudah terpasang di komputer Anda (untuk generate `APP_KEY`). Cek dengan:
   ```bash
   php --version
   ```

---

## 2. Persiapan Sebelum Masuk Railway

### 2.1 Generate APP_KEY (sekali saja, di komputer Anda)
Buka terminal di folder proyek, lalu jalankan:

```bash
php artisan key:generate --show
```

Hasilnya seperti: `base64:ABC123...==`. **Salin** hasil ini — nanti ditempel sebagai variabel `APP_KEY` di dashboard Railway.
> Jangan generate ulang setelah aplikasi berjalan, karena sesi login & token lama akan tidak valid.

### 2.2 Ambil kunci Midtrans sandbox (bisa juga nanti)
1. Login ke https://dashboard.sandbox.midtrans.com
2. Menu **Settings → Access Keys**.
3. Salin **Server Key** (rahasia) dan **Client Key** (untuk Snap.js).
> Kalau belum punya akun Midtrans, bisa **lewati dulu** dan isi belakangan — aplikasi tetap bisa diuji tanpa Midtrans (transaksi tunai).

---

## 3. Buat Project & Hubungkan GitHub

1. Buka https://railway.app dan **login**.
2. Klik **New Project** (atau tombol **+ New**).
3. Pilih **Deploy from GitHub repo** (Deploy from Image / template jangan).
4. Jika belum menghubungkan GitHub: Railway akan meminta izin → klik **Authorize / Install** → pilih akun GitHub Anda → **Only select repositories** → centang repo **`Kelar-web`** → **Save**.
5. Pilih repo **`Kelar-web`** dari daftar. Railway mulai membuat service.
   - Baris "Deploying…" akan muncul — **biarkan**, masih ada beberapa langkah dulu.

---

## 4. Tambah Database MySQL

1. Di halaman project Railway, klik **+ New** (atau **New Service**).
2. Pilih **Database → MySQL** (bukan PostgreSQL, bukan MongoDB).
3. Tunggu sampai status database berubah menjadi **Deployed** (hijau).

Sekarang Anda punya 2 service di project: **`Kelar-web`** (aplikasi) dan **MySQL** (database).

### 4.1 Salin kredensial database
1. Klik service **MySQL**.
2. Buka tab **Variables**.
3. Salin nilai-nilai ini (nama bisa sedikit berbeda, cari yang mengandung kata `MYSQL`):

| Variabel di tab Variables MySQL | Isi untuk app |
|---|---|
| `MYSQLHOST` (atau `MYSQL_HOST`) | `DB_HOST` |
| `MYSQLPORT` (atau `MYSQL_TCP_PORT`) | `DB_PORT` |
| `MYSQLDATABASE` (atau `MYSQL_DATABASE`) | `DB_DATABASE` |
| `MYSQLUSER` (atau `MYSQL_USER`) | `DB_USERNAME` |
| `MYSQLPASSWORD` (atau `MYSQL_PASSWORD`) | `DB_PASSWORD` |

> Jangan pindah-pindah service: Anda akan menuliskan variabel ini di service **aplikasi**, bukan di service MySQL.

---

## 5. Isi Environment Variables (di service Aplikasi)

1. Klik service **`Kelar-web`**.
2. Buka tab **Variables**.
3. Klik **New Variable** satu per satu, atau **Edit in Editor** (masukkan semua sekaligus).

Isi **semua variabel berikut** (nilai contoh hanya ilustrasi — ganti dengan milik Anda):

```env
APP_KEY=base64:hasil-dari-langkah-2.1
APP_NAME="KELAR POS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kelar-web-production-xxxx.up.railway.app

DB_CONNECTION=mysql
DB_HOST=isi-dari-MYSQLHOST
DB_PORT=3306
DB_DATABASE=isi-dari-MYSQLDATABASE
DB_USERNAME=isi-dari-MYSQLUSER
DB_PASSWORD=isi-dari-MYSQLPASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_CHANNEL=daily
LOG_LEVEL=error
FILESYSTEM_DISK=local
TRUSTED_PROXIES=*

MIDTRANS_SERVER_KEY=isi-Server-Key-sandbox-Midtrans
MIDTRANS_CLIENT_KEY=isi-Client-Key-sandbox-Midtrans
MIDTRANS_IS_PRODUCTION=false
```

**PENTING:**
- `APP_URL` masih pakai domain sementara (bentuk `xxxx.up.railway.app`). Domain asli akan keluar di **Langkah 7** — nanti nilai ini Anda ganti dan Railway otomatis deploy ulang.
- `APP_DEBUG` **harus** `false`.
- `SESSION_SECURE_COOKIE` **harus** `true` (karena HTTPS).
- `MIDTRANS_IS_PRODUCTION` **tetap** `false` — jangan pernah ubah ke `true` tanpa prosedur di `DEPLOYMENT.md` §5.3.

---

## 6. Pastikan Build & Start Command Benar

File `nixpacks.toml` sudah ada di repo, jadi biasanya otomatis. Tapi **periksa/masukkan manual** supaya pasti:

1. Di service **`Kelar-web`**, buka **Settings**.
2. Bagian **Build / Deploy**:
   - **Build Command**: kosongkan / biarkan otomatis (Nixpacks menjalankan `composer install` → `npm install` → `npm run build`).
   - **Start Command**: isi persis:
     ```
     sh /app/railway-start.sh
     ```
3. Simpan.

---

## 7. Deploy Pertama & Ambil Domain

1. Klik tombol **Deploy** (atau Railway akan otomatis deploy setelah perubahan). Klik tab **Deployments** untuk melihat proses:
   - Fase **Build**: `composer install`, `npm install`, `npm run build` (beberapa menit).
   - Fase **Deploy/Run**: script `railway-start.sh` dijalankan (migrasi + seeder + cache).
2. Perhatikan log. Jika muncul **kata "ERROR" atau merah** → lihat bagian [Troubleshooting](#10-troubleshooting).
3. Setelah selesai, buka **Settings → Networking** (di service aplikasi).
4. Klik **Generate Domain** / aktifkan **Public Networking**. Copy URL yang muncul, misalnya:
   ```
   https://kelar-web-production-abc123.up.railway.app
   ```
5. Kembali ke **Variables** service aplikasi, ganti `APP_URL` dengan URL di atas (lengkap dengan `https://`).
6. Railway otomatis deploy ulang. Tunggu sampai berhasil lagi.

> Jika halaman menampilkan "Sistem Belum Disiapkan" atau login — itu tanda aplikasi berjalan. Lanjut ke Langkah 8.

---

## 8. Login & Persiapan Toko (WAJIB untuk Pemula)

1. Buka URL aplikasi (dari Langkah 7) di browser.
2. Login dengan akun bawaan:
   - **Username**: `admin`
   - **Password**: `admin123`
3. **Segera ganti password** (keamanan): menu **Users → Edit** (pada akun `admin`), isi password baru → **Simpan**.
4. Jalankan alur onboarding (sekali saja):
   - **Pengaturan → Reset Data**: ketik teks konfirmasi `HAPUS SEMUA DATA` + password → **Jalankan Reset**.
     (Ini mengosongkan data transaksi/stok, mempertahankan master data. Aman dilakukan di database baru.)
   - **Pengaturan → Setup Awal**: isi **kas awal** dan **tanggal cutover** → **Simpan**. Ini menghasilkan jurnal pembukaan (`JR-OPENING-*`).
5. Halaman dashboard POS sudah bisa dipakai.

> Seeder otomatis (Langkah 6) mengisi: user `admin`, kategori, akun COA, dan produk fondasi — jadi tidak perlu menjalankan seeder manual.

---

## 9. Uji Fungsi (Checklist)

1. **Login / Logout** → jalan.
2. **Dashboard** → angka & grafik tampil.
3. **POS → buka shift** → transaksi tunai → cetak/struk.
4. **Barcode**: tombol scan kamera (pastikan izin kamera diaktifkan browser). Kalau kamera gagal, fitur **ketik manual** tetap jalan (barcode-engine punya fallback otomatis untuk MIME wasm).
5. **Laporan**: ekspor PDF (Penjualan, Stok, Laba Rugi, Neraca, Jurnal, CaLK).
6. **Midtrans sandbox** (bila kunci sudah diisi):
   - Lakukan transaksi dengan metode pembayaran → halaman Snap muncul.
   - Di dashboard Midtrans sandbox: **Settings → Configuration → Payment Notification URL** diisi:
     ```
     https://APP_URL-ANDA/midtrans/webhook
     ```
   - Cek status via `GET /midtrans/status?order_id=...`.
7. **Health check**: buka `https://APP_URL-ANDA/up` → harus menampilkan `{"status":"UP"}`.

---

## 10. Troubleshooting

| Gejala | Penyebab | Solusi |
|---|---|---|
| Build gagal "No application encryption key" | `APP_KEY` kosong/salah | Set `APP_KEY` hasil `php artisan key:generate --show`, lalu Deploy ulang. |
| Deploy berhenti di "Connecting to database" / migrate error | `DB_*` salah atau MySQL belum Deployed | Cek nilai di tab Variables service MySQL; tunggu MySQL status Deployed. |
| Halaman 503 Service Unavailable | Aplikasi sedang build ulang, atau maintenance mode | Tunggu deploy selesai. Jangan jalankan `php artisan down`. |
| Halaman 419 Page Expired | Cookie session tidak aman (HTTP vs HTTPS) | Pastikan `APP_URL` https, `SESSION_SECURE_COOKIE=true`, `TRUSTED_PROXIES=*`. |
| Login "Too Many Attempts" | Terlalu sering salah password (limit 5x/menit) | Tunggu 1 menit lalu coba lagi. |
| Gambar produk hilang setelah deploy ulang | Filesystem `local` ephemeral (tanpa volume) | Pasang Volume (lihat §11.1). |
| Kamera scan tidak terbaca (jarang) | MIME `.wasm` bukan `application/wasm` saat pakai `artisan serve` | zxing-wasm punya fallback otomatis; coba browser lain / mode manual. Untuk produksi berat, deploy ke VPS/Forge dengan nginx (lihat DEPLOYMENT.md §2.9). |
| `composer install` lambat/gantung | Jaringan build | Biarkan; sekali saja per deploy. |
| Tidak ada aplikasi muncul, hanya log build sukses | Start Command belum diset | Set Start Command `sh /app/railway-start.sh` (Langkah 6) lalu Deploy. |

---

## 11. Opsional (Tidak Wajib untuk MVP / Skripsi)

### 11.1 Volume persisten untuk upload produk
Agar gambar produk/logo **tidak hilang** saat deploy ulang:
1. Service aplikasi → **Settings → Volumes** → **New Volume**.
2. Mount path: **`/app/storage`**.
3. Deploy ulang sekali.
> Perhatikan: volume meng-hapus file sementara di storage pada deploy pertama setelah dipasang.

### 11.2 Health check di Railway
1. Service aplikasi → **Settings → Health Checks** → aktifkan.
2. Path: `/up` → Railway akan mematikan instance yang sehat terus-menerus.

### 11.3 Custom domain
1. **Settings → Networking → Custom Domain** → masukkan domain Anda.
2. Tambahkan record DNS `CNAME` ke target yang diberikan Railway.
3. Pastikan `APP_URL` diubah ke domain baru.

### 11.4 Midtrans production (JANGAN buru-buru)
Ikuti prosedur ketat di **DEPLOYMENT.md §5.3**: kunci **live** dulu, test sandbox dulu, lalu ganti `MIDTRANS_SERVER_KEY`/`MIDTRANS_CLIENT_KEY` ke live dan `MIDTRANS_IS_PRODUCTION=true`. Server Key live tidak boleh pernah tampil di halaman/HTML.

---

## 12. Update Aplikasi ke Depan

1. Ubah kode di komputer → `git push origin main`.
2. Railway otomatis build + deploy ulang.
3. Migrasi baru dijalankan otomatis oleh `railway-start.sh` (`php artisan migrate --force`).
4. Seeder **tidak** menggandakan data (dijaga `app:ensure-seed`).

---

## 13. Referensi

- Konfigurasi env lengkap: `.env.production.example`
- Panduan produksi umum: `DEPLOYMENT.md`
- Nixpacks (builder Railway): https://nixpacks.com/docs/providers/php
- Railway docs: https://docs.railway.com