<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\BebanController;
use App\Http\Controllers\AturanDiskonController;
use Illuminate\Support\Facades\Auth;

// Jika user buka alamat utama aplikasi, langsung lempar ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

// Peta rute untuk tamu (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Webhook notifikasi Midtrans (tanpa auth; CSRF di-exempt di bootstrap/app.php)
Route::post('/midtrans/webhook', [\App\Http\Controllers\MidtransController::class, 'webhook'])
    ->name('midtrans.webhook');

// Peta rute untuk karyawan yang sudah lolos satpam (Auth)
Route::middleware(['auth', 'pengaturan-ready'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/laba-rugi', [DashboardController::class, 'labaRugi'])->name('dashboard.laba-rugi')
        ->middleware('role:admin,pemilik');

    // POS Kasir — diakses kasir & admin
    Route::get('/pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index')
        ->middleware('role:kasir,admin');

    // Shift Kasir (buka/tutup kasir) — FASE 1 (revisi dosen)
    Route::middleware('role:kasir,admin')->group(function () {
        Route::get('/shift/buka', [\App\Http\Controllers\ShiftKasirController::class, 'bukaForm'])->name('shift.buka');
        Route::post('/shift/buka', [\App\Http\Controllers\ShiftKasirController::class, 'buka'])->name('shift.buka.store');
        Route::get('/shift/tutup', [\App\Http\Controllers\ShiftKasirController::class, 'tutupForm'])->name('shift.tutup');
        Route::post('/shift/tutup', [\App\Http\Controllers\ShiftKasirController::class, 'tutup'])->name('shift.tutup.store');
        Route::get('/shift/riwayat', [\App\Http\Controllers\ShiftKasirController::class, 'riwayat'])->name('shift.riwayat');
    });

    // Transaksi: simpan (AJAX dari POS) + riwayat + detail
    Route::middleware('role:kasir,admin')->group(function () {
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'show'])->name('transaksi.show');
        Route::post('/transaksi/{transaksi}/batal-pending', [TransaksiController::class, 'batalPending'])->name('transaksi.batal-pending');

        // Midtrans: inisiasi snap + cek status (AJAX dari POS)
        Route::post('/midtrans/create-transaction', [\App\Http\Controllers\MidtransController::class, 'createTransaction'])->name('midtrans.create');
        Route::get('/midtrans/status', [\App\Http\Controllers\MidtransController::class, 'status'])->name('midtrans.status');
    });

    // Pencarian produk — diakses kasir & admin (untuk live search dan hasil scan barcode)
    Route::get('/produk/search', [ProdukController::class, 'search'])->name('produk.search')
        ->middleware('role:kasir,admin,gudang');

    // Cek aturan diskon aktif saat scan/tambah produk di POS (Fase 2)
    Route::get('/aturan-diskon/cek-aktif', [AturanDiskonController::class, 'cekAktif'])
        ->name('aturan-diskon.cek-aktif')
        ->middleware('role:kasir,admin,gudang');

    // Rute Inventori & Produk — diakses admin & gudang
    Route::middleware('role:admin,gudang')->group(function () {
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/inventori', [\App\Http\Controllers\InventoriController::class, 'index'])->name('inventori.index');
        Route::get('/inventori/terima', [\App\Http\Controllers\InventoriController::class, 'receive'])->name('inventori.receive');
        Route::post('/inventori/terima', [\App\Http\Controllers\InventoriController::class, 'storeReceive'])->name('inventori.storeReceive');
        Route::get('/inventori/sesuaikan', [\App\Http\Controllers\InventoriController::class, 'adjust'])->name('inventori.adjust');
        Route::post('/inventori/sesuaikan', [\App\Http\Controllers\InventoriController::class, 'storeAdjust'])->name('inventori.storeAdjust');
        Route::get('/inventori/riwayat', [\App\Http\Controllers\InventoriController::class, 'history'])->name('inventori.history');
    });

    // Admin only: Master Data (tambah/ubah/toggle)
    Route::middleware('role:admin')->group(function () {
        Route::resource('aturan-diskon', AturanDiskonController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('kategori', KategoriController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('supplier', \App\Http\Controllers\SupplierController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
        Route::patch('/produk/{produk}/toggle', [ProdukController::class, 'toggle'])->name('produk.toggle');

        // CRUD Karyawan / Users
        Route::resource('users', UserController::class)->only(['index', 'store', 'update']);
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');

        // Audit Trail
        Route::get('/audit-trail', [\App\Http\Controllers\AuditController::class, 'index'])->name('audit-trail.index');

        // PRD Export
        Route::get('/prd/pdf', [\App\Http\Controllers\PrdController::class, 'export'])->name('prd.export');
    });

    // Keuangan & Laporan
    Route::middleware('role:admin,pemilik')->group(function () {
        Route::get('/laporan/penjualan', [\App\Http\Controllers\LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('/laporan/laba-rugi', [\App\Http\Controllers\LaporanController::class, 'labaRugi'])->name('laporan.laba-rugi');
        Route::get('/laporan/neraca', [\App\Http\Controllers\LaporanController::class, 'neraca'])->name('laporan.neraca');
        Route::get('/laporan/calk', [\App\Http\Controllers\LaporanController::class, 'calk'])->name('laporan.calk');
        Route::get('/laporan/jurnal', [\App\Http\Controllers\LaporanController::class, 'jurnal'])->name('laporan.jurnal');
        Route::get('/laporan/{jenis}/pdf', [\App\Http\Controllers\LaporanController::class, 'export'])->name('laporan.export');

        Route::resource('beban', BebanController::class)->only(['index', 'store']);

        // FASE 5: Aset Tetap (admin & pemilik)
        Route::get('/aset-tetap', [\App\Http\Controllers\AsetTetapController::class, 'index'])->name('aset-tetap.index');
        Route::post('/aset-tetap', [\App\Http\Controllers\AsetTetapController::class, 'store'])->name('aset-tetap.store');
        Route::put('/aset-tetap/{aset_tetap}', [\App\Http\Controllers\AsetTetapController::class, 'update'])->name('aset-tetap.update');
        Route::patch('/aset-tetap/{aset_tetap}/toggle', [\App\Http\Controllers\AsetTetapController::class, 'toggle'])->name('aset-tetap.toggle');
    });

    Route::middleware('role:admin,pemilik,gudang')->group(function () {
        Route::get('/laporan/stok', [\App\Http\Controllers\LaporanController::class, 'stok'])->name('laporan.stok');
    });

    // Pengaturan — Reset Data & Setup Awal (eksklusif admin/pemilik)
    Route::middleware('role:admin,pemilik')->group(function () {
        Route::get('/pengaturan/reset-data', [\App\Http\Controllers\PengaturanController::class, 'resetDataForm'])
            ->name('pengaturan.reset-data');
        Route::post('/pengaturan/reset-data', [\App\Http\Controllers\PengaturanController::class, 'resetData'])
            ->name('pengaturan.reset-data.store');

        Route::get('/pengaturan/setup-awal', [\App\Http\Controllers\PengaturanController::class, 'setupAwalForm'])
            ->name('pengaturan.setup-awal');
        Route::post('/pengaturan/setup-awal', [\App\Http\Controllers\PengaturanController::class, 'setupAwal'])
            ->name('pengaturan.setup-awal.store');
    });
});