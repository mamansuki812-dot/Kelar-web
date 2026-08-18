<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PRD - KELAR POS</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 18mm 25mm 18mm;
            @bottom-center {
                content: "KELAR POS — PRD v1.0 | Halaman " counter(page) " dari " counter(pages);
                font-size: 8pt;
                color: #94a3b8;
            }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #1e293b;
        }

        /* ---- COVER PAGE ---- */
        .cover {
            page-break-after: always;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 700px;
            text-align: center;
        }
        .cover-logo {
            font-size: 42pt;
            font-weight: 800;
            color: #4F46E5;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        .cover-dot { color: #10B981; }
        .cover-sub {
            font-size: 14pt;
            color: #64748b;
            margin-bottom: 50px;
            font-weight: 400;
        }
        .cover-title {
            font-size: 22pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .cover-subtitle {
            font-size: 12pt;
            color: #475569;
            margin-bottom: 60px;
        }
        .cover-meta {
            font-size: 9pt;
            color: #64748b;
            line-height: 2;
        }
        .cover-meta strong { color: #334155; }

        /* ---- TOC ---- */
        .toc { page-break-after: always; }
        .toc h2 { font-size: 16pt; color: #0f172a; border-bottom: 3px solid #4F46E5; padding-bottom: 6px; margin-bottom: 20px; }
        .toc-list { list-style: none; padding: 0; }
        .toc-list li {
            padding: 5px 0;
            border-bottom: 1px dotted #cbd5e1;
            font-size: 10pt;
        }
        .toc-list li span.num { color: #4F46E5; font-weight: 700; margin-right: 8px; }
        .toc-list li.sub { padding-left: 25px; font-size: 9.5pt; color: #475569; }

        /* ---- SECTIONS ---- */
        .section { page-break-inside: avoid; margin-bottom: 20px; }
        h1 {
            font-size: 16pt;
            color: #4F46E5;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 5px;
            margin: 30px 0 15px 0;
            page-break-after: avoid;
        }
        h2 {
            font-size: 13pt;
            color: #1e293b;
            border-left: 4px solid #4F46E5;
            padding-left: 10px;
            margin: 22px 0 10px 0;
            page-break-after: avoid;
        }
        h3 {
            font-size: 11pt;
            color: #334155;
            margin: 15px 0 8px 0;
            page-break-after: avoid;
        }
        h4 {
            font-size: 10pt;
            color: #475569;
            margin: 10px 0 5px 0;
        }
        p { margin-bottom: 8px; text-align: justify; }
        ul, ol { margin: 5px 0 10px 20px; }
        li { margin-bottom: 3px; }

        /* ---- TABLES ---- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 15px 0;
            font-size: 9pt;
        }
        th {
            background: #4F46E5;
            color: white;
            padding: 7px 8px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) { background: #f8fafc; }
        tr:hover { background: #f1f5f9; }

        /* ---- BADGES ---- */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: 600;
        }
        .badge-high { background: #fee2e2; color: #dc2626; }
        .badge-med { background: #fef3c7; color: #d97706; }
        .badge-low { background: #dbeafe; color: #2563eb; }
        .badge-done { background: #d1fae5; color: #059669; }
        .badge-dev { background: #fef9c3; color: #a16207; }
        .badge-todo { background: #f1f5f9; color: #64748b; }

        /* ---- MISC ---- */
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 10px 14px;
            margin: 10px 0;
            border-radius: 0 6px 6px 0;
            font-size: 9.5pt;
        }
        .warn-box {
            background: #fefce8;
            border-left: 4px solid #eab308;
            padding: 10px 14px;
            margin: 10px 0;
            border-radius: 0 6px 6px 0;
            font-size: 9.5pt;
        }
        .page-break { page-break-before: always; }
        code {
            background: #f1f5f9;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 9pt;
            font-family: 'Consolas', monospace;
        }
        .two-col { display: flex; gap: 20px; }
        .two-col > div { flex: 1; }
    </style>
</head>
<body>

<!-- ==================== COVER ==================== -->
<div class="cover">
    <div class="cover-logo">KELAR<span class="cover-dot">.</span></div>
    <div class="cover-sub">Point of Sale System</div>
    <div class="cover-title">Product Requirements Document</div>
    <div class="cover-subtitle">Dokumen Spesifikasi Kebutuhan Produk</div>
    <div class="cover-meta">
        <strong>Versi:</strong> 1.0<br>
        <strong>Tanggal:</strong> 23 Juli 2026<br>
        <strong>Penulis:</strong> Rikni Winur Alam (NIM 3222001)<br>
        <strong>Status:</strong> Final Draft<br>
        <strong>Klasifikasi:</strong> Internal / Skripsi
    </div>
</div>

<!-- ==================== REVISION HISTORY ==================== -->
<h1>Daftar Revisi</h1>
<table>
    <tr><th>Versi</th><th>Tanggal</th><th>Penulis</th><th>Deskripsi Perubahan</th></tr>
    <tr><td>1.0</td><td>23 Juli 2026</td><td>Rikni Winur Alam</td><td>Dokumen awal — seluruh fitur sistem KELAR POS</td></tr>
</table>

<!-- ==================== TOC ==================== -->
<div class="toc page-break">
    <h2>Daftar Isi</h2>
    <ol class="toc-list">
        <li><span class="num">1</span> Pendahuluan</li>
        <li class="sub">1.1 Latar Belakang</li>
        <li class="sub">1.2 Tujuan</li>
        <li class="sub">1.3 Ruang Lingkup</li>
        <li class="sub">1.4 Definisi & Akronim</li>
        <li><span class="num">2</span> Gambaran Umum Sistem</li>
        <li class="sub">2.1 Arsitektur Sistem</li>
        <li class="sub">2.2 Stack Teknologi</li>
        <li class="sub">2.3 User Roles & Hak Akses</li>
        <li class="sub">2.4 Alur Autentikasi</li>
        <li><span class="num">3</span> Fitur — Modul Autentikasi</li>
        <li class="sub">3.1 Login</li>
        <li class="sub">3.2 Logout</li>
        <li class="sub">3.3 Session Management</li>
        <li><span class="num">4</span> Fitur — Dashboard</li>
        <li class="sub">4.1 Statistik Ringkas</li>
        <li class="sub">4.2 Notifikasi Stok Kritis</li>
        <li class="sub">4.3 Grafik Tren Penjualan</li>
        <li><span class="num">5</span> Fitur — Transaksi POS (Point of Sale)</li>
        <li class="sub">5.1 Pencarian & Scan Barcode</li>
        <li class="sub">5.2 Keranjang Belanja</li>
        <li class="sub">5.3 Pembayaran</li>
        <li class="sub">5.4 Cetak Struk</li>
        <li><span class="num">6</span> Fitur — Riwayat Transaksi</li>
        <li class="sub">6.1 Daftar Transaksi</li>
        <li class="sub">6.2 Detail & Cetak Ulang Struk</li>
        <li><span class="num">7</span> Fitur — Master Data</li>
        <li class="sub">7.1 Kategori Produk</li>
        <li class="sub">7.2 Produk</li>
        <li class="sub">7.3 Supplier</li>
        <li class="sub">7.4 Pengguna (Users)</li>
        <li><span class="num">8</span> Fitur — Manajemen Inventori</li>
        <li class="sub">8.1 Stok Barang</li>
        <li class="sub">8.2 Penerimaan Barang</li>
        <li class="sub">8.3 Penyesuaian Stok</li>
        <li class="sub">8.4 Riwayat Pergerakan Stok</li>
        <li><span class="num">9</span> Fitur — Laporan & Keuangan</li>
        <li class="sub">9.1 Laporan Penjualan</li>
        <li class="sub">9.2 Laporan Laba Rugi</li>
        <li class="sub">9.3 Laporan Neraca</li>
        <li class="sub">9.4 Laporan Stok</li>
        <li class="sub">9.5 Buku Jurnal</li>
        <li class="sub">9.6 Export PDF</li>
        <li><span class="num">10</span> Fitur — Audit Trail</li>
        <li><span class="num">11</span> Desain Database (ERD)</li>
        <li><span class="num">12</span> Non-Functional Requirements</li>
        <li class="sub">12.1 Responsivitas & UI</li>
        <li class="sub">12.2 Keamanan</li>
        <li class="sub">12.3 Performa</li>
        <li class="sub">12.4 Kompatibilitas</li>
        <li><span class="num">13</span> Integrasi</li>
        <li><span class="num">14</span> Roadmap Pengembangan</li>
        <li><span class="num">15</span> Lampiran</li>
    </ol>
</div>

<!-- ==================== 1. PENDAHULUAN ==================== -->
<h1>1. Pendahuluan</h1>

<h2>1.1 Latar Belakang</h2>
<p>
    Usaha kecil dan menengah (UKM) di Indonesia, khususnya toko retail dan warung, masih banyak yang mengelola transaksi penjualan secara manual menggunakan buku tulis atau spreadsheet. Pendekatan ini memiliki beberapa kelemahan: rentan terhadap kesalahan pencatatan, sulit melacak stok secara real-time, tidak memiliki data penjualan yang terstruktur untuk analisis bisnis, dan proses pencatatan yang memakan waktu.
</p>
<p>
    KELAR POS (Point of Sale) dirancang sebagai solusi sistem kasir digital yang berbasis web, sehingga dapat diakses dari perangkat apa pun yang memiliki browser — termasuk ponsel pintar, tablet, dan komputer. Sistem ini bertujuan untuk menyederhanakan proses transaksi penjualan, mengelola inventori produk, menyediakan laporan keuangan yang akurat, serta memberikan fitur scan barcode untuk mempercepat proses checkout.
</p>

<h2>1.2 Tujuan</h2>
<ul>
    <li>Menyediakan sistem POS yang mudah digunakan oleh kasir tanpa pelatihan intensif.</li>
    <li>Menyediakan manajemen produk dan stok yang terintegrasi secara real-time.</li>
    <li>Menghasilkan laporan penjualan, laba rugi, neraca, dan buku jurnal secara otomatis.</li>
    <li>Mendukung pencetakan struk thermal 80mm untuk transaksi tunai.</li>
    <li>Mencatat seluruh aktivitas CRUD melalui audit trail untuk keamanan data.</li>
    <li>Mendukung akses multi-role (Admin, Kasir, Pemilik, Gudang) dengan hak akses berbeda.</li>
    <li>Menerapkan desain responsif mobile-first sehingga dapat diakses dari ponsel.</li>
</ul>

<h2>1.3 Ruang Lingkup</h2>

<h3>In Scope</h3>
<table>
    <tr><th>No</th><th>Modul</th><th>Deskripsi</th></tr>
    <tr><td>1</td><td>Autentikasi</td><td>Login, logout, session management, role-based access</td></tr>
    <tr><td>2</td><td>Dashboard</td><td>Statistik penjualan hari ini, notifikasi stok kritis, grafik tren</td></tr>
    <tr><td>3</td><td>POS (Point of Sale)</td><td>Scan barcode, keranjang belanja, metode bayar, cetak struk thermal</td></tr>
    <tr><td>4</td><td>Riwayat Transaksi</td><td>Daftar transaksi, detail struk, cetak ulang</td></tr>
    <tr><td>5</td><td>Master Data</td><td>CRUD Kategori, Produk, Supplier, Pengguna</td></tr>
    <tr><td>6</td><td>Inventori</td><td>Monitoring stok, penerimaan barang, penyesuaian stok, riwayat</td></tr>
    <tr><td>7</td><td>Laporan Keuangan</td><td>Penjualan, Laba Rugi, Neraca, Stok, Buku Jurnal + Export PDF</td></tr>
    <tr><td>8</td><td>Audit Trail</td><td>Pencatatan semua aktivitas CRUD oleh user</td></tr>
    <tr><td>9</td><td>Responsive UI</td><td>Mobile-first design, card/table switch</td></tr>
</table>

<h3>Out of Scope</h3>
<ul>
    <li>Pembayaran online/payment gateway (QRIS manual only)</li>
    <li>Multi-cabang / multi-gudang</li>
    <li>Integrasi dengan sistem akuntansi eksternal (e.g. Accurate, Zahir)</li>
    <li>Aplikasi mobile native (berbasis web responsive)</li>
    <li>Manajemen promo/diskon otomatis</li>
    <li>CRM atau loyalty program</li>
</ul>

<h2>1.4 Definisi & Akronim</h2>
<table>
    <tr><th>Istilah</th><th>Definisi</th></tr>
    <tr><td>POS</td><td>Point of Sale — sistem kasir tempat transaksi penjualan dilakukan</td></tr>
    <tr><td>SKU</td><td>Stock Keeping Unit — kode unik produk untuk identifikasi</td></tr>
    <tr><td>HPP</td><td>Harga Pokok Penjualan — biaya beli produk yang dijual</td></tr>
    <tr><td>QRIS</td><td>Quick Response Indonesian Standard — metode pembayaran QR</td></tr>
    <tr><td>Thermal Printer</td><td>Printer khusus struk dengan kertas termal 80mm</td></tr>
    <tr><td>Barcode</td><td>Kode batang (CODE_128, EAN_13, QR_CODE) untuk scan produk</td></tr>
    <tr><td>Double-entry</td><td>Sistem pencatatan akuntansi dimana setiap transaksi memiliki debit dan kredit</td></tr>
</table>

<!-- ==================== 2. GAMBARAN UMUM ==================== -->
<div class="page-break"></div>
<h1>2. Gambaran Umum Sistem</h1>

<h2>2.1 Arsitektur Sistem</h2>
<p>KELAR POS menggunakan arsitektur MVC (Model-View-Controller) berbasis Laravel 12 dengan pendekatan monolithic:</p>

<table>
    <tr><th>Layer</th><th>Teknologi</th><th>Keterangan</th></tr>
    <tr><td>Frontend</td><td>Blade Templates + Tailwind CSS 3.4</td><td>Server-side rendering, mobile-first responsive</td></tr>
    <tr><td>JavaScript</td><td>Vanilla JS + html5-qrcode + Axios</td><td>Interaktivitas, barcode scan, AJAX</td></tr>
    <tr><td>Backend</td><td>Laravel 12 (PHP 8.3)</td><td>Controller, Service, Middleware, Eloquent ORM</td></tr>
    <tr><td>Database</td><td>MySQL 8.0</td><td>16 tabel (9 bisnis + 7 framework)</td></tr>
    <tr><td>PDF Export</td><td>barryvdh/laravel-dompdf v3.1.2</td><td>Export laporan ke PDF</td></tr>
    <tr><td>Server</td><td>Apache/Nginx + PHP-FPM</td><td>Local development: php artisan serve</td></tr>
</table>

<h2>2.2 Stack Teknologi</h2>
<table>
    <tr><th>Komponen</th><th>Versi</th><th>Fungsi</th></tr>
    <tr><td>PHP</td><td>8.3+</td><td>Bahasa backend</td></tr>
    <tr><td>Laravel</td><td>12.x</td><td>Framework MVC</td></tr>
    <tr><td>MySQL</td><td>8.0</td><td>Database relasional</td></tr>
    <tr><td>Tailwind CSS</td><td>3.4</td><td>Utility-first CSS framework</td></tr>
    <tr><td>Vite</td><td>6.x</td><td>Build tool & dev server</td></tr>
    <tr><td>html5-qrcode</td><td>2.3.8</td><td>Scan barcode via kamera</td></tr>
    <tr><td>Axios</td><td>1.x</td><td>HTTP client untuk AJAX</td></tr>
    <tr><td>DomPDF</td><td>3.1.2</td><td>Generate PDF dari HTML</td></tr>
    <tr><td>Chart.js</td><td>4.x</td><td>Visualisasi grafik (dashboard)</td></tr>
</table>

<h2>2.3 User Roles & Hak Akses</h2>
<p>Sistem memiliki 4 role pengguna dengan hak akses berbeda:</p>

<table>
    <tr>
        <th>Modul</th>
        <th style="text-align:center">Admin</th>
        <th style="text-align:center">Kasir</th>
        <th style="text-align:center">Pemilik</th>
        <th style="text-align:center">Gudang</th>
    </tr>
    <tr><td>Dashboard</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
    <tr><td>POS (Point of Sale)</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
    <tr><td>Riwayat Transaksi</td><td style="text-align:center">✅ Semua</td><td style="text-align:center">✅ Sendiri</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
    <tr><td>Kelola Kategori</td><td style="text-align:center">✅ CRUD</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
    <tr><td>Kelola Produk</td><td style="text-align:center">✅ CRUD</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td><td style="text-align:center">👁️ Lihat</td></tr>
    <tr><td>Kelola Supplier</td><td style="text-align:center">✅ CRUD</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td><td style="text-align:center">👁️ Lihat</td></tr>
    <tr><td>Kelola Pengguna</td><td style="text-align:center">✅ CRUD</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
    <tr><td>Stok Barang</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td></tr>
    <tr><td>Terima Barang</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td></tr>
    <tr><td>Sesuaikan Stok</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td></tr>
    <tr><td>Riwayat Stok</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td></tr>
    <tr><td>Laporan Penjualan</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
    <tr><td>Laporan Laba Rugi</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
    <tr><td>Laporan Neraca</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
    <tr><td>Laporan Stok</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td><td style="text-align:center">✅</td></tr>
    <tr><td>Buku Jurnal</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
    <tr><td>Export PDF</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td></tr>
    <tr><td>Audit Trail</td><td style="text-align:center">✅</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td><td style="text-align:center">❌</td></tr>
</table>

<h2>2.4 Alur Autentikasi</h2>
<ol>
    <li>User mengakses halaman mana pun → middleware <code>auth</code> mengecek session.</li>
    <li>Jika belum login → redirect ke <code>/login</code>.</li>
    <li>User memasukkan username + password → <code>AuthController::login()</code> memvalidasi.</li>
    <li>Validasi: username ada, password cocok, <code>is_active = true</code>.</li>
    <li>Jika berhasil → regenerate session, redirect ke <code>/dashboard</code>.</li>
    <li>Jika gagal → kembali ke login dengan pesan error.</li>
    <li>Setiap akses route → middleware <code>role:xxx</code> mengecek hak akses user.</li>
    <li>Logout → <code>AuthController::logout()</code> invalidasi session, redirect ke login.</li>
</ol>

<!-- ==================== 3. AUTENTIKASI ==================== -->
<div class="page-break"></div>
<h1>3. Fitur — Modul Autentikasi</h1>

<h2>3.1 Login</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /login</code>, <code>POST /login</code></td></tr>
    <tr><td>Middleware</td><td><code>guest</code> (hanya bisa diakses jika belum login)</td></tr>
    <tr><td>Input</td><td>Username (string, required), Password (string, required)</td></tr>
    <tr><td>Validasi</td><td>Username harus ada di DB, password harus cocok (Hash::check), is_active harus true</td></tr>
    <tr><td>Success</td><td>Regenerate session → redirect ke /dashboard</td></tr>
    <tr><td>Failed</td><td>Kembali ke /login dengan flash message error</td></tr>
    <tr><td>Default Admin</td><td>Username: <code>admin</code>, Password: <code>admin123</code></td></tr>
</table>

<h2>3.2 Logout</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>POST /logout</code></td></tr>
    <tr><td>Middleware</td><td><code>auth</code></td></tr>
    <tr><td>Proses</td><td>Auth::logout() → invalidate session → regenerasi CSRF token → redirect ke /login</td></tr>
</table>

<h2>3.3 Session Management</h2>
<ul>
    <li>Session disimpan di database tabel <code>sessions</code> (SESSION_DRIVER=database).</li>
    <li>Session token disimpan di cookie + <code>remember_token</code> di tabel users.</li>
    <li>CSRF token otomatis dikirim via Axios header <code>X-CSRF-TOKEN</code>.</li>
</ul>

<!-- ==================== 4. DASHBOARD ==================== -->
<h1>4. Fitur — Dashboard</h1>

<h2>4.1 Statistik Ringkas</h2>
<p>Dashboard ditampilkan di <code>/dashboard</code> dan menampilkan kartu statistik:</p>
<table>
    <tr><th>Metrik</th><th>Deskripsi</th><th>Sumber Data</th></tr>
    <tr><td>Total Penjualan Hari Ini</td><td>Jumlah total_bayar dari transaksi hari ini (status: selesai)</td><td><code>transaksi</code> WHERE tanggal hari ini</td></tr>
    <tr><td>Jumlah Transaksi Hari Ini</td><td>Count transaksi hari ini</td><td><code>transaksi</code> WHERE tanggal hari ini</td></tr>
    <tr><td>Produk Aktif</td><td>Jumlah produk dengan is_active = true</td><td><code>produk</code> WHERE is_active</td></tr>
    <tr><td>Stok Menipis</td><td>Jumlah produk dengan stok <= stok_minimum</td><td><code>produk</code> WHERE stok <= stok_minimum</td></tr>
    <tr><td>Stok Habis</td><td>Jumlah produk dengan stok = 0</td><td><code>produk</code> WHERE stok = 0</td></tr>
</table>

<h2>4.2 Notifikasi Stok Kritis</h2>
<p>Menampilkan 5 produk dengan stok paling rendah (stok <= stok_minimum, urut dari terendah). Setiap item menampilkan nama produk, stok saat ini, dan batas minimum.</p>

<h2>4.3 Grafik Tren Penjualan</h2>
<p>Menampilkan grafik garis/batang penjualan 7 hari terakhir menggunakan Chart.js. Data dikirim dari controller sebagai array labels (tanggal) dan values (total penjualan per hari).</p>

<!-- ==================== 5. POS ==================== -->
<div class="page-break"></div>
<h1>5. Fitur — Transaksi POS (Point of Sale)</h1>

<h2>5.1 Pencarian & Scan Barcode</h2>
<table>
    <tr><th>Fitur</th><th>Detail</th></tr>
    <tr><td>Live Search</td><td>Pencarian produk real-time berdasarkan nama/kode. Menggunakan <code>GET /produk/search</code> (JSON API). Limit 50 hasil. Filter by kategori.</td></tr>
    <tr><td>Scan Barcode Kamera</td><td>Menggunakan html5-qrcode@2.3.8 (Tier 2) + Native BarcodeDetector API (Tier 1). Format: CODE_128, EAN_13, QR_CODE. FPS: 12. Torch toggle. Debounce 2 detik.</td></tr>
    <tr><td>Keyboard Scan</td><td>Buffer input dari barcode scanner hardware. Auto-detect Enter key. Timeout 100ms antar karakter.</td></tr>
</table>

<h2>5.2 Keranjang Belanja</h2>
<table>
    <tr><th>Fitur</th><th>Detail</th></tr>
    <tr><td>Tambah Item</td><td>Klik produk atau scan barcode → otomatis ditambahkan. Jika sudah ada, qty +1.</td></tr>
    <tr><td>Ubah Qty</td><td>Tombol +/- atau input manual. Validasi: qty tidak boleh melebihi stok.</td></tr>
    <tr><td>Hapus Item</td><td>Tombol hapus per item. Konfirmasi via modal custom (appConfirm).</td></tr>
    <tr><td>Kosongkan Keranjang</td><td>Tombol "Kosongkan" → konfirmasi modal → keranjang dikosongkan.</td></tr>
    <tr><td>Diskon</td><td>Input diskon nominal (Rp). Dihitung dari total harga sebelum diskon.</td></tr>
    <tr><td>Subtotal</td><td>Dihitung otomatis: total harga - diskon. Ditampilkan secara real-time.</td></tr>
</table>

<h2>5.3 Pembayaran</h2>
<table>
    <tr><th>Metode</th><th>Deskripsi</th></tr>
    <tr><td>Tunai</td><td>Input jumlah bayar. Sistem hitung kembalian. Validasi: jumlah bayar >= total.</td></tr>
    <tr><td>Transfer</td><td>Pembayaran via transfer bank. Tidak perlu input jumlah bayar (diasumsikan sesuai).</td></tr>
    <tr><td>QRIS</td><td>Pembayaran via QR code. Tidak perlu input jumlah bayar.</td></tr>
</table>
<p><strong>Proses pembayaran:</strong></p>
<ol>
    <li>Validasi keranjang tidak kosong.</li>
    <li>Validasi jumlah bayar (jika tunai).</li>
    <li>AJAX POST ke <code>/transaksi</code> dengan data: items[], diskon, metode_pembayaran, jumlah_bayar.</li>
    <li>Backend: <code>TransaksiService::proses()</code> — DB transaction + pessimistic locking.</li>
    <li>Generate kode transaksi unik: <code>TRX-YYYYMMDD-XXXXX</code>.</li>
    <li>Simpan transaksi + detail + kurangi stok + buat jurnal double-entry.</li>
    <li>Return JSON: kode_transaksi, detail items, totals, kembalian.</li>
    <li>Tampilkan modal sukses + opsi cetak struk.</li>
</ol>

<h2>5.4 Cetak Struk</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Ukuran Kertas</td><td>Thermal 80mm (<code>@page { size: 80mm auto; margin: 2mm; }</code>)</td></tr>
    <tr><td>Isi Struk</td><td>Nama toko, alamat, tanggal, kasir, daftar item, subtotal, diskon, total, bayar, kembalian, metode bayar, barcode transaksi</td></tr>
    <tr><td>Metode</td><td>Window.print() dengan CSS @media print</td></tr>
</table>

<!-- ==================== 6. RIWAYAT TRANSAKSI ==================== -->
<h1>6. Fitur — Riwayat Transaksi</h1>

<h2>6.1 Daftar Transaksi</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /transaksi</code></td></tr>
    <tr><td>Data</td><td>Kode transaksi, tanggal, kasir, total, metode bayar, status</td></tr>
    <tr><td>Filter</td><td>By user_id (admin), by tanggal (range picker)</td></tr>
    <tr><td>Pagination</td><td>20 item per halaman</td></tr>
    <tr><td>Akses</td><td>Kasir hanya melihat transaksi sendiri. Admin melihat semua.</td></tr>
</table>

<h2>6.2 Detail & Cetak Ulang Struk</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /transaksi/{id}</code></td></tr>
    <tr><td>Data</td><td>Header transaksi + detail items (nama produk, qty, harga, subtotal) + info kasir</td></tr>
    <tr><td>Cetak Ulang</td><td>Tombol "Cetak Struk" → window.print() dengan format thermal 80mm</td></tr>
</table>

<!-- ==================== 7. MASTER DATA ==================== -->
<div class="page-break"></div>
<h1>7. Fitur — Master Data</h1>

<h2>7.1 Kategori Produk</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET/POST/PUT/DELETE /kategori</code></td></tr>
    <tr><td>Fields</td><td>nama_kategori (string 100, unique), deskripsi (text, nullable)</td></tr>
    <tr><td>Operasi</td><td>Create, Read, Update, Delete (soft — cek masihDipakai())</td></tr>
    <tr><td>Validasi Hapus</td><td>Cek apakah masih ada produk aktif yang menggunakan kategori ini. Jika ya, tolak hapus.</td></tr>
    <tr><td>Akses</td><td>Admin only</td></tr>
</table>

<h2>7.2 Produk</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET/POST/PUT /produk</code>, <code>PATCH /produk/{id}/toggle</code>, <code>GET /produk/search</code></td></tr>
    <tr><td>Fields</td><td>kode_produk (50, unique), nama_produk (150), kategori_id (FK), harga_beli (decimal 15,2), harga_jual (decimal 15,2), stok (int), stok_minimum (int, default 5), satuan (20, default 'pcs'), gambar (255, nullable), is_active (boolean)</td></tr>
    <tr><td>Gambar</td><td>Upload ke <code>storage/app/public/produk/</code>. Format: jpg, jpeg, png. Max: 2MB.</td></tr>
    <tr><td>Toggle</td><td><code>PATCH /produk/{id}/toggle</code> — toggle is_active (aktif/nonaktif)</td></tr>
    <tr><td>Search API</td><td><code>GET /produk/search?q=xxx</code> — JSON untuk live search POS. Hanya produk aktif.</td></tr>
    <tr><td>Akses</td><td>Admin: CRUD. Gudang: lihat tambah. Kasir: search only (untuk POS).</td></tr>
</table>

<h2>7.3 Supplier</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET/POST/PUT/DELETE /supplier</code></td></tr>
    <tr><td>Fields</td><td>nama_supplier (150), kontak (20, nullable), alamat (text, nullable), email (100, nullable)</td></tr>
    <tr><td>Operasi</td><td>CRUD lengkap. Hapus langsung (hard delete).</td></tr>
    <tr><td>Akses</td><td>Admin: CRUD. Gudang: lihat tambah.</td></tr>
</table>

<h2>7.4 Pengguna (Users)</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET/POST/PUT /users</code>, <code>PATCH /users/{id}/toggle</code></td></tr>
    <tr><td>Fields</td><td>name (100), username (50, unique), email (100, unique), password (hashed), role (enum: admin/kasir/pemilik/gudang), is_active (boolean)</td></tr>
    <tr><td>Toggle</td><td>Admin tidak bisa menonaktifkan akun sendiri</td></tr>
    <tr><td>Akses</td><td>Admin only</td></tr>
</table>

<!-- ==================== 8. INVENTORI ==================== -->
<h1>8. Fitur — Manajemen Inventori</h1>

<h2>8.1 Stok Barang</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /inventori</code></td></tr>
    <tr><td>Data</td><td>Daftar produk aktif beserta stok, stok_minimum, satuan, status (normal/menipis/habis)</td></tr>
    <tr><td>Filter</td><td>By kategori_id, status (normal/minimum/habis), search (nama/kode)</td></tr>
    <tr><td>Pagination</td><td>20 item per halaman</td></tr>
</table>

<h2>8.2 Penerimaan Barang</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /inventori/terima</code> (form), <code>POST /inventori/terima</code> (proses)</td></tr>
    <tr><td>Input</td><td>produk_id (select), supplier_id (select), jumlah (integer, min:1), keterangan (text, optional)</td></tr>
    <tr><td>Proses</td><td>Stok produk bertambah sejumlah <code>jumlah</code>. Mencatat stok_history dengan jenis = 'masuk'.</td></tr>
    <tr><td>Akses</td><td>Admin + Gudang</td></tr>
</table>

<h2>8.3 Penyesuaian Stok</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /inventori/sesuaikan</code> (form), <code>POST /inventori/sesuaikan</code> (proses)</td></tr>
    <tr><td>Input</td><td>produk_id (select), jenis (enum: masuk/keluar), jumlah (integer, min:1), keterangan (text, required)</td></tr>
    <tr><td>Validasi</td><td>Jika jenis = 'keluar', jumlah tidak boleh melebihi stok saat ini.</td></tr>
    <tr><td>Proses</td><td>Stok naik/turun. Mencatat stok_history dengan jenis sesuai.</td></tr>
</table>

<h2>8.4 Riwayat Pergerakan Stok</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /inventori/riwayat</code></td></tr>
    <tr><td>Data</td><td>produk, user yang melakukan, jenis (masuk/keluar/penyesuaian), jumlah, stok sebelum, stok sesudah, keterangan, tanggal</td></tr>
    <tr><td>Filter</td><td>produk_id, jenis, tanggal_mulai, tanggal_akhir</td></tr>
    <tr><td>Pagination</td><td>20 item per halaman</td></tr>
</table>

<!-- ==================== 9. LAPORAN ==================== -->
<div class="page-break"></div>
<h1>9. Fitur — Laporan & Keuangan</h1>

<h2>9.1 Laporan Penjualan</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /laporan/penjualan</code></td></tr>
    <tr><td>Filter</td><td>tanggal_mulai, tanggal_akhir (default: awal bulan ini → sekarang)</td></tr>
    <tr><td>Metrik</td><td>Total revenue, jumlah transaksi, rata-rata per transaksi, total diskon</td></tr>
    <tr><td>Visualisasi</td><td>Chart harian (bar/line), Top 10 produk terlaris (berdasarkan jumlah terjual)</td></tr>
    <tr><td>Tabel</td><td>Daftar transaksi dalam periode (kode, tanggal, kasir, total, metode, status)</td></tr>
    <tr><td>Akses</td><td>Admin + Pemilik</td></tr>
</table>

<h2>9.2 Laporan Laba Rugi</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /laporan/laba-rugi</code></td></tr>
    <tr><td>Filter</td><td>bulan (format YYYY-MM, default: bulan ini)</td></tr>
    <tr><td>Metrik</td><td>Total penjualan, total diskon, HPP (berdasarkan harga_beli), laba kotor, margin %</td></tr>
    <tr><td>Breakdown</td><td>Per kategori: nama kategori, jumlah penjualan, laba, margin</td></tr>
</table>

<h2>9.3 Laporan Neraca</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /laporan/neraca</code></td></tr>
    <tr><td>Filter</td><td>tanggal (default: hari ini)</td></tr>
    <tr><td>Metrik</td><td>Nilai aset persediaan (stok × harga_beli), total pendapatan kumulatif, total HPP kumulatif, laba bersih kumulatif</td></tr>
    <tr><td>Rumus</td><td>Total Aset = Nilai Persediaan + max(0, Laba Bersih). Total Ekuitas = Total Aset (simplified).</td></tr>
</table>

<h2>9.4 Laporan Stok</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /laporan/stok</code></td></tr>
    <tr><td>Filter</td><td>status (normal/menipis/habis)</td></tr>
    <tr><td>Metrik</td><td>Nilai stok total (stok × harga_jual), jumlah produk stok habis, jumlah produk stok menipis</td></tr>
    <tr><td>Akses</td><td>Admin + Pemilik + Gudang</td></tr>
</table>

<h2>9.5 Buku Jurnal</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /laporan/jurnal</code></td></tr>
    <tr><td>Filter</td><td>akun (search di debit & kredit), tanggal_mulai, tanggal_akhir</td></tr>
    <tr><td>Akun</td><td>Kas, Pendapatan Penjualan, Harga Pokok Penjualan, Persediaan Barang</td></tr>
    <tr><td>Data</td><td>tanggal, kode_jurnal, akun debit, akun kredit, nominal, keterangan</td></tr>
    <tr><td>Pagination</td><td>30 item per halaman</td></tr>
</table>

<h2>9.6 Export PDF</h2>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /laporan/{jenis}/pdf</code></td></tr>
    <tr><td>Jenis</td><td>penjualan, stok, laba-rugi, neraca, jurnal</td></tr>
    <tr><td>Library</td><td>barryvdh/laravel-dompdf v3.1.2</td></tr>
    <tr><td>Output</td><td>File PDF langsung di-download</td></tr>
</table>

<!-- ==================== 10. AUDIT TRAIL ==================== -->
<h1>10. Fitur — Audit Trail</h1>
<table>
    <tr><th>Aspek</th><th>Detail</th></tr>
    <tr><td>Route</td><td><code>GET /audit-trail</code></td></tr>
    <tr><td>Middleware</td><td><code>LogActivity</code> — terdaftar di web middleware group, berjalan otomatis</td></tr>
    <tr><td>Yang Dicatat</td><td>Semua request POST, PUT/PATCH, DELETE (bukan GET)</td></tr>
    <tr><td>Data Tersimpan</td><td>user_id, aksi (create/update/delete), model (nama tabel), model_id, ip_address, user_agent, created_at</td></tr>
    <tr><td>Filter</td><td>By user_id, aksi, tanggal_mulai, tanggal_akhir</td></tr>
    <tr><td>Pagination</td><td>30 item per halaman</td></tr>
    <tr><td>Akses</td><td>Admin only</td></tr>
</table>

<!-- ==================== 11. DATABASE ==================== -->
<div class="page-break"></div>
<h1>11. Desain Database (ERD)</h1>

<h2>11.1 Daftar Tabel Bisnis (9 Tabel)</h2>
<table>
    <tr><th>No</th><th>Tabel</th><th>Deskripsi</th><th>Utama</th></tr>
    <tr><td>1</td><td><code>users</code></td><td>Pengguna sistem (admin, kasir, pemilik, gudang)</td><td>✅</td></tr>
    <tr><td>2</td><td><code>kategori</code></td><td>Kategori produk</td><td></td></tr>
    <tr><td>3</td><td><code>produk</code></td><td>Data produk yang dijual</td><td>✅</td></tr>
    <tr><td>4</td><td><code>supplier</code></td><td>Data supplier/pemasok</td><td></td></tr>
    <tr><td>5</td><td><code>transaksi</code></td><td>Header transaksi penjualan</td><td>✅</td></tr>
    <tr><td>6</td><td><code>detail_transaksi</code></td><td>Detail item per transaksi</td><td>✅</td></tr>
    <tr><td>7</td><td><code>stok_history</code></td><td>Riwayat pergerakan stok</td><td></td></tr>
    <tr><td>8</td><td><code>jurnal</code></td><td>Pencatatan jurnal akuntansi (double-entry)</td><td></td></tr>
    <tr><td>9</td><td><code>audit_log</code></td><td>Audit trail aktivitas CRUD</td><td></td></tr>
</table>

<h2>11.2 Relasi Antar Tabel</h2>
<table>
    <tr><th>Parent</th><th>Child</th><th>Foreign Key</th><th>Tipe</th><th>On Delete</th></tr>
    <tr><td>kategori</td><td>produk</td><td>kategori_id</td><td>1:N</td><td>RESTRICT</td></tr>
    <tr><td>users</td><td>transaksi</td><td>user_id</td><td>1:N</td><td>RESTRICT</td></tr>
    <tr><td>transaksi</td><td>detail_transaksi</td><td>transaksi_id</td><td>1:N</td><td>CASCADE</td></tr>
    <tr><td>produk</td><td>detail_transaksi</td><td>produk_id</td><td>1:N</td><td>RESTRICT</td></tr>
    <tr><td>produk</td><td>stok_history</td><td>produk_id</td><td>1:N</td><td>RESTRICT</td></tr>
    <tr><td>users</td><td>stok_history</td><td>user_id</td><td>1:N</td><td>RESTRICT</td></tr>
    <tr><td>transaksi</td><td>jurnal</td><td>transaksi_id</td><td>1:N</td><td>SET NULL</td></tr>
    <tr><td>users</td><td>audit_log</td><td>user_id</td><td>1:N</td><td>SET NULL</td></tr>
</table>

<h2>11.3 Skema Detail</h2>

<h3>users</h3>
<table>
    <tr><th>Kolom</th><th>Tipe</th><th>Constraint</th></tr>
    <tr><td>id</td><td>BIGINT PK</td><td>Auto-increment</td></tr>
    <tr><td>name</td><td>VARCHAR(100)</td><td>NOT NULL</td></tr>
    <tr><td>username</td><td>VARCHAR(50)</td><td>UNIQUE, NOT NULL</td></tr>
    <tr><td>email</td><td>VARCHAR(100)</td><td>UNIQUE, NOT NULL</td></tr>
    <tr><td>password</td><td>VARCHAR(255)</td><td>NOT NULL, hashed</td></tr>
    <tr><td>role</td><td>ENUM</td><td>admin, kasir, pemilik, gudang. Default: kasir</td></tr>
    <tr><td>is_active</td><td>BOOLEAN</td><td>Default: true</td></tr>
    <tr><td>remember_token</td><td>VARCHAR(100)</td><td>Nullable</td></tr>
    <tr><td>created_at / updated_at</td><td>TIMESTAMP</td><td>Auto</td></tr>
</table>

<h3>produk</h3>
<table>
    <tr><th>Kolom</th><th>Tipe</th><th>Constraint</th></tr>
    <tr><td>id</td><td>BIGINT PK</td><td>Auto-increment</td></tr>
    <tr><td>kode_produk</td><td>VARCHAR(50)</td><td>UNIQUE, NOT NULL (barcode)</td></tr>
    <tr><td>nama_produk</td><td>VARCHAR(150)</td><td>NOT NULL</td></tr>
    <tr><td>kategori_id</td><td>BIGINT FK</td><td>→ kategori.id, RESTRICT</td></tr>
    <tr><td>harga_beli</td><td>DECIMAL(15,2)</td><td>Default: 0</td></tr>
    <tr><td>harga_jual</td><td>DECIMAL(15,2)</td><td>Default: 0</td></tr>
    <tr><td>stok</td><td>INT</td><td>Default: 0</td></tr>
    <tr><td>stok_minimum</td><td>INT</td><td>Default: 5</td></tr>
    <tr><td>satuan</td><td>VARCHAR(20)</td><td>Default: 'pcs'</td></tr>
    <tr><td>gambar</td><td>VARCHAR(255)</td><td>Nullable</td></tr>
    <tr><td>is_active</td><td>BOOLEAN</td><td>Default: true</td></tr>
</table>

<h3>transaksi</h3>
<table>
    <tr><th>Kolom</th><th>Tipe</th><th>Constraint</th></tr>
    <tr><td>id</td><td>BIGINT PK</td><td>Auto-increment</td></tr>
    <tr><td>kode_transaksi</td><td>VARCHAR(30)</td><td>UNIQUE (TRX-YYYYMMDD-XXXXX)</td></tr>
    <tr><td>user_id</td><td>BIGINT FK</td><td>→ users.id, RESTRICT</td></tr>
    <tr><td>tanggal_transaksi</td><td>DATETIME</td><td>NOT NULL</td></tr>
    <tr><td>total_harga</td><td>DECIMAL(15,2)</td><td>Default: 0</td></tr>
    <tr><td>diskon</td><td>DECIMAL(15,2)</td><td>Default: 0</td></tr>
    <tr><td>total_bayar</td><td>DECIMAL(15,2)</td><td>Default: 0</td></tr>
    <tr><td>jumlah_bayar</td><td>DECIMAL(15,2)</td><td>Default: 0</td></tr>
    <tr><td>kembalian</td><td>DECIMAL(15,2)</td><td>Default: 0</td></tr>
    <tr><td>metode_pembayaran</td><td>ENUM</td><td>tunai, transfer, qris. Default: tunai</td></tr>
    <tr><td>status</td><td>ENUM</td><td>selesai, dibatalkan. Default: selesai</td></tr>
    <tr><td>catatan</td><td>TEXT</td><td>Nullable</td></tr>
</table>

<!-- ==================== 12. NFR ==================== -->
<div class="page-break"></div>
<h1>12. Non-Functional Requirements</h1>

<h2>12.1 Responsivitas & UI</h2>
<ul>
    <li><strong>Mobile-first:</strong> Semua halaman harus fungsional di layar 375px (iPhone SE) hingga 1920px (desktop).</li>
    <li><strong>Card/Table Switch:</strong> Tabel desktop (≥768px) berubah menjadi kartu di mobile (&lt;768px) menggunakan komponen <code>x-responsive-table</code>.</li>
    <li><strong>Zero horizontal scroll:</strong> Tidak ada overflow horizontal di semua ukuran layar. <code>overflow-x-hidden</code> di body.</li>
    <li><strong>Touch-friendly:</strong> Tombol minimal 44×44px untuk tap target. Spasi antar elemen interaktif minimal 8px.</li>
    <li><strong>Font:</strong> Inter (body) + Outfit (headings) via Google Fonts.</li>
    <li><strong>Warna Primer:</strong> #4F46E5 (Indigo). <strong>Sekunder:</strong> #10B981 (Emerald).</li>
    <li><strong>Modal Custom:</strong> Semua konfirmasi dan notifikasi menggunakan modal custom (appConfirm/appAlert), bukan alert/confirm bawaan browser.</li>
</ul>

<h2>12.2 Keamanan</h2>
<ul>
    <li><strong>CSRF Protection:</strong> Laravel CSRF token pada semua form. Axios otomatis kirim X-CSRF-TOKEN header.</li>
    <li><strong>XSS Prevention:</strong> Blade auto-escaping (<code>@{{ }}</code>). Raw HTML hanya di responsive-table component (<code>@{!! !!}</code>).</li>
    <li><strong>SQL Injection:</strong> Eloquent ORM + Query Builder dengan parameter binding.</li>
    <li><strong>Password Hashing:</strong> Bcrypt (Laravel default Hash::make/Hash::check).</li>
    <li><strong>Role-Based Access:</strong> Middleware <code>CheckRole</code> pada semua route.</li>
    <li><strong>Audit Trail:</strong> Semua operasi CRUD (POST/PUT/PATCH/DELETE) tercatat otomatis via middleware LogActivity.</li>
    <li><strong>Input Validation:</strong> Validasi server-side pada semua form request.</li>
</ul>

<h2>12.3 Performa</h2>
<ul>
    <li><strong>Database Indexing:</strong> Foreign keys terindeks. Kolom yang sering di-query (tanggal, status) seharusnya diindeks.</li>
    <li><strong>Eager Loading:</strong> Menggunakan <code>with()</code> untuk relasi N+1 prevention.</li>
    <li><strong>Pagination:</strong> Semua daftar data menggunakan pagination (15-30 item/halaman).</li>
    <li><strong>Lazy Loading:</strong> Data dashboard dimuat secara parsial.</li>
    <li><strong>Service Layer:</strong> TransaksiService menggunakan <code>DB::transaction()</code> + <code>lockForUpdate()</code> untuk konsistensi data.</li>
    <li><strong>Scanner Optimization:</strong> FPS 12, formatsToSupport terfilter (3 format), debounce 2 detik, qrbox proporsional.</li>
</ul>

<h2>12.4 Kompatibilitas</h2>
<table>
    <tr><th>Platform</th><th>Browser</th><th>Status</th></tr>
    <tr><td>Android</td><td>Chrome 90+</td><td><span class="badge badge-done">Fully Supported</span></td></tr>
    <tr><td>Android</td><td>Firefox 90+</td><td><span class="badge badge-done">Fully Supported</span></td></tr>
    <tr><td>iOS</td><td>Safari 15+</td><td><span class="badge badge-done">Fully Supported</span></td></tr>
    <tr><td>Windows</td><td>Chrome 90+</td><td><span class="badge badge-done">Fully Supported</span></td></tr>
    <tr><td>Windows</td><td>Edge 90+</td><td><span class="badge badge-done">Fully Supported</span></td></tr>
    <tr><td>macOS</td><td>Safari 15+</td><td><span class="badge badge-done">Fully Supported</span></td></tr>
    <tr><td>Printer</td><td>Thermal 80mm</td><td><span class="badge badge-done">Supported</span></td></tr>
</table>

<!-- ==================== 13. INTEGRASI ==================== -->
<h1>13. Integrasi</h1>

<h2>13.1 Integrasi Internal</h2>
<table>
    <tr><th>Modul</th><th>Berinteraksi Dengan</th><th>Mekanisme</th></tr>
    <tr><td>POS</td><td>Produk (search), Transaksi (store), Stok (kurangi)</td><td>AJAX JSON + TransaksiService</td></tr>
    <tr><td>Inventori</td><td>Produk (update stok), Supplier (referensi)</td><td>Server-side form submit</td></tr>
    <tr><td>Laporan</td><td>Transaksi, Detail, Produk, Kategori</td><td>Server-side query + DomPDF</td></tr>
    <tr><td>Audit Trail</td><td>Semua modul</td><td>Middleware otomatis</td></tr>
</table>

<h2>13.2 Integrasi Eksternal</h2>
<table>
    <tr><th>Layanan</th><th>Library/CDN</th><th>Fungsi</th></tr>
    <tr><td>Barcode Scanner</td><td>html5-qrcode@2.3.8 (CDN jsDelivr)</td><td>Scan barcode via kamera</td></tr>
    <tr><td>Font</td><td>Google Fonts (Inter, Outfit)</td><td>Typography</td></tr>
    <tr><td>PDF Engine</td><td>barryvdh/laravel-dompdf (Composer)</td><td>Generate PDF laporan</td></tr>
</table>

<!-- ==================== 14. ROADMAP ==================== -->
<div class="page-break"></div>
<h1>14. Roadmap Pengembangan</h1>

<table>
    <tr><th>Sprint</th><th>Fitur</th><th>Status</th></tr>
    <tr><td>Sprint 1</td><td>Autentikasi, Dashboard, CRUD Kategori & Produk</td><td><span class="badge badge-done">Selesai</span></td></tr>
    <tr><td>Sprint 2</td><td>POS (keranjang, bayar, struk), Service Layer</td><td><span class="badge badge-done">Selesai</span></td></tr>
    <tr><td>Sprint 3</td><td>Riwayat Transaksi, Cetak Ulang Struk Thermal</td><td><span class="badge badge-done">Selesai</span></td></tr>
    <tr><td>Sprint 4</td><td>Inventori (stok, terima, sesuaikan, riwayat), Supplier</td><td><span class="badge badge-done">Selesai</span></td></tr>
    <tr><td>Sprint 5</td><td>Laporan (Penjualan, Laba Rugi, Neraca, Stok, Jurnal) + Export PDF</td><td><span class="badge badge-done">Selesai</span></td></tr>
    <tr><td>Sprint 6</td><td>Audit Trail, Responsive Mobile, Barcode Scanner, Modal Custom</td><td><span class="badge badge-done">Selesai</span></td></tr>
    <tr><td>Sprint 7</td><td>Refactoring: StokService integration, DB indexes, code cleanup</td><td><span class="badge badge-dev">In Progress</span></td></tr>
    <tr><td>Sprint 8</td><td>Pembatalan Transaksi, Chart Dashboard, Rate Limiting Login</td><td><span class="badge badge-todo">Planned</span></td></tr>
    <tr><td>Sprint 9</td><td>Testing (Unit, Feature, E2E), Deployment</td><td><span class="badge badge-todo">Planned</span></td></tr>
</table>

<!-- ==================== 15. LAMPIRAN ==================== -->
<h1>15. Lampiran</h1>

<h2>A. File Struktur Proyek</h2>
<table>
    <tr><th>Path</th><th>Deskripsi</th></tr>
    <tr><td><code>app/Http/Controllers/</code></td><td>12 controller</td></tr>
    <tr><td><code>app/Http/Middleware/</code></td><td>2 middleware (CheckRole, LogActivity)</td></tr>
    <tr><td><code>app/Models/</code></td><td>9 model Eloquent</td></tr>
    <tr><td><code>app/Services/</code></td><td>2 service (TransaksiService, StokService)</td></tr>
    <tr><td><code>database/migrations/</code></td><td>11 migration (16 tabel)</td></tr>
    <tr><td><code>resources/views/</code></td><td>28 blade template</td></tr>
    <tr><td><code>resources/views/components/</code></td><td>1 komponen (responsive-table)</td></tr>
    <tr><td><code>routes/web.php</code></td><td>39 route</td></tr>
    <tr><td><code>DESIGN-GUIDELINES.md</code></td><td>Panduan desain untuk pengembangan</td></tr>
    <tr><td><code>docs/</code></td><td>Dokumentasi UML (STRUKTUR_APLIKASI, ERD, ERD-Chen)</td></tr>
</table>

<h2>B. Default Data</h2>
<table>
    <tr><th>Data</th><th>Nilai</th></tr>
    <tr><td>Admin Default</td><td>Username: admin, Password: admin123, Role: admin</td></tr>
    <tr><td>Kategori Default</td><td>Makanan Ringan, Minuman, Kebutuhan Harian</td></tr>
    <tr><td>Roles</td><td>admin, kasir, pemilik, gudang</td></tr>
    <tr><td>Metode Bayar</td><td>tunai, transfer, qris</td></tr>
    <tr><td>Status Transaksi</td><td>selesai, dibatalkan</td></tr>
    <tr><td>Jenis Stok</td><td>masuk, keluar, penyesuaian</td></tr>
</table>

<h2>C. Glossary Teknis</h2>
<table>
    <tr><th>Istilah</th><th>Definisi</th></tr>
    <tr><td>Service Layer</td><td>Class yang meng encapsulate business logic terpisah dari controller</td></tr>
    <tr><td>Eloquent ORM</td><td>Laravel's Active Record implementation untuk database interaction</td></tr>
    <tr><td>Blade Template</td><td>Templating engine Laravel untuk rendering HTML</td></tr>
    <tr><td>Pessimistic Locking</td><td>DB lock (lockForUpdate) untuk mencegah race condition saat transaksi concurrent</td></tr>
    <tr><td>Double-entry Bookkeeping</td><td>Sistem akuntansi dimana setiap transaksi tercatat sebagai debit DAN kredit</td></tr>
    <tr><td>Middleware</td><td>Class yang menangani request sebelum sampai ke controller</td></tr>
    <tr><td>Tailwind CSS</td><td>Utility-first CSS framework untuk styling</td></tr>
</table>

<p style="text-align:center; margin-top:50px; color:#94a3b8; font-size:9pt;">
    — End of Document —<br>
    KELAR POS v1.0 · Product Requirements Document<br>
    &copy; 2026 Rikni Winur Alam (NIM 3222001)
</p>

</body>
</html>
