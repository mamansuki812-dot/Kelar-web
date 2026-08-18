<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KELAR POS') - KELAR</title>
    <!-- Font Google: Plus Jakarta Sans (heading) & Inter (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, .font-display {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
        }
    </style>
    
    <!-- Memanggil CSS & JS Laravel Vite (Tailwind CSS v3.4) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @yield('styles')
</head>
<body class="bg-body-bg text-neutral-dark flex min-h-screen overflow-x-hidden">

    <!-- SIDEBAR (Navigasi Kiri) -->
    <aside id="sidebar" class="bg-neutral-dark text-slate-200 w-64 fixed inset-y-0 left-0 z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col shadow-xl">
        <!-- Logo Brand -->
        <div class="h-16 flex items-center justify-between px-6 bg-slate-950 border-b border-slate-800">
            <a href="{{ url('/dashboard') }}" class="flex items-center space-x-2">
                <span class="text-2xl font-bold font-display text-white tracking-wider">KELAR<span class="text-primary font-extrabold">.</span></span>
            </a>
            <!-- Mobile Close Button -->
            <button onclick="toggleSidebar()" class="lg:hidden text-muted hover:text-white focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Menu Navigasi -->
        <div class="flex-1 overflow-y-auto px-4 py-6 space-y-7">
            <!-- Navigasi Utama -->
            <div>
                <p class="px-2 text-xs font-semibold text-muted uppercase tracking-wider mb-3">Menu Utama</p>
                <nav class="space-y-1">
                    @if(in_array(auth()->user()->role, ['admin', 'pemilik']))
                        <a href="{{ url('/dashboard') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('dashboard*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->role, ['kasir', 'admin']))
                        <a href="{{ url('/pos') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('pos*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="font-medium {{ Request::is('pos*') ? 'text-white' : 'text-secondary' }}">Transaksi POS</span>
                        </a>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin', 'kasir']))
                        <a href="{{ url('/transaksi') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('transaksi') || Request::is('transaksi/*') && !Request::is('transaksi/baru*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Riwayat Transaksi</span>
                        </a>

                        {{-- Shift Kasir — FASE 1 (revisi dosen) --}}
                        <a href="{{ url('/shift/riwayat') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('shift*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Shift Kasir</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Master Data -->
            @if(in_array(auth()->user()->role, ['admin', 'gudang']))
                <div>
                    <p class="px-2 text-xs font-semibold text-muted uppercase tracking-wider mb-3">Master Data</p>
                    <nav class="space-y-1">
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ url('/kategori') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('kategori*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span>Kelola Kategori</span>
                            </a>

                            <a href="{{ url('/aturan-diskon') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('aturan-diskon*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Aturan Diskon</span>
                            </a>
                        @endif

                        <a href="{{ url('/produk') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('produk*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Kelola Produk</span>
                        </a>

                        @if(auth()->user()->role === 'admin')
                            <a href="{{ url('/users') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('users*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span>Kelola Pengguna</span>
                            </a>
                        @endif
                    </nav>
                </div>
            @endif

            <!-- Inventori & Supplier -->
            @if(in_array(auth()->user()->role, ['admin', 'gudang']))
                <div>
                    <p class="px-2 text-xs font-semibold text-muted uppercase tracking-wider mb-3">Manajemen Inventori</p>
                    <nav class="space-y-1">
                        <a href="{{ url('/inventori') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('inventori*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Stok Barang</span>
                        </a>

                        <a href="{{ url('/supplier') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('supplier*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Kelola Supplier</span>
                        </a>
                    </nav>
                </div>
            @endif

            <!-- Keuangan & Laporan -->
            @if(in_array(auth()->user()->role, ['admin', 'pemilik', 'gudang']))
                <div>
                    <p class="px-2 text-xs font-semibold text-muted uppercase tracking-wider mb-3">Keuangan & Laporan</p>
                    <nav class="space-y-1">
                        @if(in_array(auth()->user()->role, ['admin', 'pemilik']))
                            <a href="{{ url('/laporan/penjualan') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('laporan/penjualan*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span>Laporan Penjualan</span>
                            </a>

                            <a href="{{ url('/laporan/laba-rugi') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('laporan/laba-rugi*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Laporan Laba Rugi</span>
                            </a>

                            <a href="{{ url('/laporan/neraca') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('laporan/neraca*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                </svg>
                                <span>Laporan Neraca</span>
                            </a>

                            <a href="{{ url('/laporan/calk') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('laporan/calk*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Catatan atas Laporan (CaLK)</span>
                            </a>

                            <a href="{{ url('/beban') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('beban*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Beban Operasional</span>
                            </a>
                        @endif

                        @if(in_array(auth()->user()->role, ['admin', 'pemilik']))
                        <a href="{{ url('/aset-tetap') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('aset-tetap*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span>Aset Tetap</span>
                        </a>
                        @endif

                        <a href="{{ url('/laporan/stok') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('laporan/stok*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Laporan Stok</span>
                        </a>

                        @if(in_array(auth()->user()->role, ['admin', 'pemilik']))
                            <a href="{{ url('/laporan/jurnal') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('laporan/jurnal*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span>Buku Jurnal</span>
                            </a>
                        @endif
                    </nav>
                </div>
            @endif

            <!-- Pengaturan (Reset Data & Setup Awal) — admin/pemilik -->
            @if(in_array(auth()->user()->role, ['admin', 'pemilik']))
                <div>
                    <p class="px-2 text-xs font-semibold text-muted uppercase tracking-wider mb-3">Pengaturan</p>
                    <nav class="space-y-1">
                        <a href="{{ route('pengaturan.setup-awal') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('pengaturan/setup-awal*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Setup Awal</span>
                        </a>

                        <a href="{{ route('pengaturan.reset-data') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('pengaturan/reset-data*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Reset Data</span>
                        </a>
                    </nav>
                </div>
            @endif

            <!-- Audit Trail -->
            @if(auth()->user()->role === 'admin')
                <div>
                    <p class="px-2 text-xs font-semibold text-muted uppercase tracking-wider mb-3">Keamanan</p>
                    <nav class="space-y-1">
                        <a href="{{ url('/audit-trail') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 hover:text-white transition-all {{ Request::is('audit-trail*') ? 'bg-primary text-white font-medium shadow-md shadow-primary-dark/30' : '' }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Audit Trail</span>
                        </a>
                    </nav>
                </div>
            @endif
        </div>

        <!-- Profil Pengguna & Keluar -->
        <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
            <div class="flex items-center space-x-3 overflow-hidden">
                <div class="h-9 w-9 rounded-full bg-primary flex items-center justify-center text-white font-bold font-display flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="overflow-hidden">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-muted capitalize truncate">{{ auth()->user()->role }}</p>
                </div>
            </div>
            
            <form action="{{ route('logout') }}" method="POST" class="flex-shrink-0">
                @csrf
                <button type="submit" class="text-muted hover:text-white p-2 rounded-lg hover:bg-slate-800 transition-colors" title="Keluar">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- SIDEBAR OVERLAY (Mobile) -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- CONTENT WRAPPER -->
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen overflow-x-hidden">
        
        <!-- TOP NAVBAR -->
        <header class="h-16 bg-surface border-b border-border-soft flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
            <!-- Mobile Menu Toggle Button -->
            <button onclick="toggleSidebar()" class="lg:hidden text-muted hover:text-slate-900 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Judul Halaman / Pencarian / Context Info -->
            <div class="font-display font-semibold text-lg text-neutral-dark">
                @yield('page_title', 'Sistem POS KELAR')
            </div>

            <!-- Bagian Kanan Header -->
            <div class="flex items-center space-x-4">
                <span class="text-xs text-primary-dark bg-teal-50 px-2.5 py-1 rounded-full font-medium capitalize">{{ auth()->user()->role }}</span>
                <div class="text-sm font-medium text-muted hidden md:block">
                    {{ date('d F Y') }}
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="flex-1 p-4 sm:p-6 md:p-8">
            <!-- Alert Session / Notifikasi Global -->
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-lg text-sm flex items-center space-x-2">
                    <svg class="h-5 w-5 text-emerald-700 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-lg text-sm flex items-center space-x-2">
                    <svg class="h-5 w-5 text-rose-700 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="py-4 px-6 md:px-8 border-t border-border-soft bg-surface text-center text-xs text-muted">
            &copy; {{ date('Y') }} KELAR POS. Hak Cipta Dilindungi. Rebranded by Rikni Winur Alam (NIM 3222001).
        </footer>

    </div>

    <!-- GLOBAL MODAL (appConfirm / appAlert) -->
    @include('components.global-modal')

    <!-- SCRIPT UNTUK SIDEBAR MOBILE TOGGLE -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const isHidden = sidebar.classList.contains('-translate-x-full');
            if (isHidden) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
    </script>

    @yield('scripts')
</body>
</html>
