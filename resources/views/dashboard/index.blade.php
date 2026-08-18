@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-br from-teal-700 via-teal-800 to-teal-950 rounded-2xl p-6 md:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none">
            <svg class="h-56 w-56" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
            </svg>
        </div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="bg-surface/10 text-teal-100 text-xs px-3 py-1 rounded-full font-medium tracking-wide uppercase">Selamat Datang</span>
                <h1 class="text-2xl md:text-3xl font-bold font-display mt-2">Halo, {{ $user->name }}! 👋</h1>
                <p class="text-teal-200 text-sm mt-1">
                    Login sebagai <span class="font-semibold capitalize text-white">{{ $user->role }}</span> — {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>
            @if(in_array($user->role, ['kasir', 'admin']))
            <a href="{{ url('/pos') }}"
               class="inline-flex items-center gap-2 bg-surface text-teal-700 hover:bg-teal-50 font-semibold px-5 py-2.5 rounded-xl transition shadow-lg flex-shrink-0 text-sm">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Buka Kasir POS
            </a>
            @endif
        </div>
    </div>

    {{-- Alert Stok Kritis --}}
    @if($stats['stok_habis'] > 0 || $stats['stok_menipis'] > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="h-5 w-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-amber-800 text-sm">Peringatan Stok!</p>
                <p class="text-amber-700 text-sm mt-0.5">
                    @if($stats['stok_habis'] > 0)<span class="font-bold text-rose-700">{{ $stats['stok_habis'] }} produk stok habis</span>@endif
                    @if($stats['stok_habis'] > 0 && $stats['stok_menipis'] > 0) dan @endif
                    @if($stats['stok_menipis'] > 0)<span class="font-bold">{{ $stats['stok_menipis'] }} produk stok menipis</span>@endif.
                    Segera lakukan penerimaan barang.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($produkKritis as $pk)
                    <x-stock-badge dot :status="$pk->stok <= 0 ? 'habis' : 'menipis'">
                        {{ $pk->nama_produk }} (Stok: {{ $pk->stok }})
                    </x-stock-badge>
                    @endforeach
                    @if(in_array($user->role, ['admin', 'gudang']))
                    <a href="{{ route('inventori.receive') }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-teal-100 text-teal-700 hover:bg-teal-200 transition">
                        + Terima Barang
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Grafik Tren Omzet 7 Hari --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
        <h3 class="font-bold font-display text-neutral-dark text-lg">Tren Omzet 7 Hari Terakhir</h3>
        <p class="text-xs text-muted mt-0.5">Grafik fluktuasi penjualan harian minggu ini.</p>
        <div class="mt-4 h-56 relative">
            <canvas id="tren7HariChart"></canvas>
        </div>
    </div>

    @if($labaRugi)
    {{-- Dashboard Laba Rugi (Fase 1 revisi dosen) --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="font-bold font-display text-neutral-dark text-lg">Dashboard Laba Rugi</h3>
                <p class="text-xs text-muted mt-0.5">Pendapatan, laba kotor, beban operasional & laba bersih dari buku besar (jurnal).</p>
            </div>
            <div class="inline-flex rounded-xl border border-border-soft bg-body-bg p-1" id="periodeToggle">
                <button type="button" data-periode="bulanan"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-colors bg-teal-700 text-white shadow-sm">Bulanan</button>
                <button type="button" data-periode="harian"
                    class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-colors text-muted hover:text-neutral-dark">Harian</button>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="rounded-xl border border-border-soft p-3">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Estimasi Biaya Bulan Depan</p>
                <p class="text-lg font-bold font-display text-neutral-dark mt-1 leading-tight">
                    @if($labaRugi['estimasi'] !== null)
                        Rp {{ number_format($labaRugi['estimasi'], 0, ',', '.') }}
                    @else
                        <span class="text-sm font-normal text-muted">Belum ada data</span>
                    @endif
                </p>
                <p class="text-[11px] text-muted mt-1">Estimasi berbasis rata-rata 3 bulan terakhir, bukan prediksi statistik.</p>
            </div>
            <div class="rounded-xl border border-border-soft p-3">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Pendapatan</p>
                <p class="text-lg font-bold font-display text-neutral-dark mt-1" id="lrPendapatan">Rp 0</p>
            </div>
            <div class="rounded-xl border border-border-soft p-3">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Laba Kotor</p>
                <p class="text-lg font-bold font-display text-neutral-dark mt-1" id="lrLabaKotor">Rp 0</p>
            </div>
            <div class="rounded-xl border border-border-soft p-3">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Laba Bersih</p>
                <p class="text-lg font-bold font-display text-neutral-dark mt-1" id="lrLabaBersih">Rp 0</p>
            </div>
        </div>

        <div class="mt-4 h-64 relative">
            <canvas id="labaRugiChart"></canvas>
        </div>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Penjualan Hari Ini --}}
        <div class="bg-surface p-5 rounded-2xl border border-border-soft shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="h-10 w-10 rounded-xl bg-teal-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs text-muted font-medium">Hari ini</span>
            </div>
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Omzet</p>
            <h3 class="text-xl font-bold font-display text-neutral-dark mt-1 leading-tight">Rp {{ number_format($stats['total_penjualan'], 0, ',', '.') }}</h3>
        </div>

        {{-- Transaksi Hari Ini --}}
        <div class="bg-surface p-5 rounded-2xl border border-border-soft shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-xs text-muted font-medium">Hari ini</span>
            </div>
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Transaksi</p>
            <h3 class="text-xl font-bold font-display text-neutral-dark mt-1">{{ $stats['total_transaksi'] }} <span class="text-sm font-normal text-muted">trx</span></h3>
        </div>

        {{-- Produk Aktif --}}
        <div class="bg-surface p-5 rounded-2xl border border-border-soft shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="h-10 w-10 rounded-xl bg-teal-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <a href="{{ url('/produk') }}" class="text-xs font-semibold text-primary hover:text-primary-dark">Lihat</a>
            </div>
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Produk Aktif</p>
            <h3 class="text-xl font-bold font-display text-neutral-dark mt-1">{{ $stats['produk_aktif'] }} <span class="text-sm font-normal text-muted">item</span></h3>
        </div>

        {{-- Stok Bermasalah --}}
        <div class="bg-surface p-5 rounded-2xl border {{ ($stats['stok_habis'] + $stats['stok_menipis']) > 0 ? 'border-amber-200' : 'border-border-soft' }} shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="h-10 w-10 rounded-xl {{ ($stats['stok_habis'] + $stats['stok_menipis']) > 0 ? 'bg-amber-50' : 'bg-body-bg' }} flex items-center justify-center">
                    <svg class="h-5 w-5 {{ ($stats['stok_habis'] + $stats['stok_menipis']) > 0 ? 'text-amber-700' : 'text-slate-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <a href="{{ url('/inventori') }}" class="text-xs font-semibold text-primary hover:text-primary-dark">Cek</a>
            </div>
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Stok Bermasalah</p>
            <h3 class="text-xl font-bold font-display {{ ($stats['stok_habis'] + $stats['stok_menipis']) > 0 ? 'text-amber-700' : 'text-neutral-dark' }} mt-1">
                {{ $stats['stok_habis'] + $stats['stok_menipis'] }} <span class="text-sm font-normal text-muted">produk</span>
            </h3>
        </div>
    </div>

    {{-- Dua kolom bawah: Transaksi Terakhir + Navigasi Cepat --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Transaksi Terakhir --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-soft flex items-center justify-between">
                <h3 class="font-bold font-display text-neutral-dark">Transaksi Terakhir</h3>
                <a href="{{ url('/transaksi') }}" class="text-xs font-semibold text-primary hover:text-primary-dark">Lihat Semua →</a>
            </div>
            @if($transaksiTerakhir->isEmpty())
            <div class="px-6 py-10 text-center text-muted">
                <svg class="h-10 w-10 mx-auto mb-2 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm">Belum ada transaksi hari ini.</p>
            </div>
            @else
            <div class="divide-y divide-slate-50">
                @foreach($transaksiTerakhir as $trx)
                <a href="{{ route('transaksi.show', $trx->id) }}" class="flex items-center gap-3 px-6 py-3.5 hover:bg-body-bg transition-colors">
                    <div class="h-9 w-9 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0">
                        <svg class="h-4 w-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-neutral-dark truncate">{{ $trx->kode_transaksi }}</p>
                        <p class="text-xs text-muted">{{ $trx->tanggal_transaksi->format('d M Y H:i') }} — {{ $trx->user->name ?? '-' }}</p>
                    </div>
                    <span class="text-sm font-bold text-emerald-700 flex-shrink-0">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</span>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Navigasi Cepat --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-soft">
                <h3 class="font-bold font-display text-neutral-dark">Navigasi Cepat</h3>
            </div>
            <div class="p-4 grid grid-cols-2 gap-3">
                @if(in_array($user->role, ['admin', 'kasir']))
                <a href="{{ url('/pos') }}" class="p-4 rounded-xl border border-border-soft hover:border-primary hover:bg-teal-50/30 transition-all flex flex-col gap-2">
                    <div class="h-9 w-9 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-neutral-dark text-sm">Kasir POS</h4>
                        <p class="text-xs text-muted mt-0.5">Buka mesin kasir</p>
                    </div>
                </a>
                <a href="{{ url('/transaksi') }}" class="p-4 rounded-xl border border-border-soft hover:border-primary hover:bg-teal-50/30 transition-all flex flex-col gap-2">
                    <div class="h-9 w-9 rounded-lg bg-teal-50 flex items-center justify-center text-primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-neutral-dark text-sm">Riwayat</h4>
                        <p class="text-xs text-muted mt-0.5">Lihat transaksi</p>
                    </div>
                </a>
                @endif

                @if(in_array($user->role, ['admin', 'gudang']))
                <a href="{{ url('/inventori') }}" class="p-4 rounded-xl border border-border-soft hover:border-primary hover:bg-teal-50/30 transition-all flex flex-col gap-2">
                    <div class="h-9 w-9 rounded-lg bg-teal-50 flex items-center justify-center text-primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-neutral-dark text-sm">Inventori</h4>
                        <p class="text-xs text-muted mt-0.5">Pantau stok barang</p>
                    </div>
                </a>
                <a href="{{ url('/supplier') }}" class="p-4 rounded-xl border border-border-soft hover:border-primary hover:bg-teal-50/30 transition-all flex flex-col gap-2">
                    <div class="h-9 w-9 rounded-lg bg-teal-50 flex items-center justify-center text-primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-neutral-dark text-sm">Supplier</h4>
                        <p class="text-xs text-muted mt-0.5">Kelola pemasok</p>
                    </div>
                </a>
                @endif

                @if($user->role === 'admin')
                <a href="{{ url('/produk') }}" class="p-4 rounded-xl border border-border-soft hover:border-primary hover:bg-teal-50/30 transition-all flex flex-col gap-2">
                    <div class="h-9 w-9 rounded-lg bg-teal-50 flex items-center justify-center text-primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-neutral-dark text-sm">Produk</h4>
                        <p class="text-xs text-muted mt-0.5">Kelola master produk</p>
                    </div>
                </a>
                <a href="{{ url('/users') }}" class="p-4 rounded-xl border border-border-soft hover:border-primary hover:bg-teal-50/30 transition-all flex flex-col gap-2">
                    <div class="h-9 w-9 rounded-lg bg-teal-50 flex items-center justify-center text-primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-neutral-dark text-sm">Pengguna</h4>
                        <p class="text-xs text-muted mt-0.5">Kelola akun karyawan</p>
                    </div>
                </a>
                @endif

                @if(in_array($user->role, ['admin', 'pemilik']))
                <a href="{{ url('/laporan/penjualan') }}" class="p-4 rounded-xl border border-border-soft hover:border-primary hover:bg-teal-50/30 transition-all flex flex-col gap-2">
                    <div class="h-9 w-9 rounded-lg bg-teal-50 flex items-center justify-center text-primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-neutral-dark text-sm">Laporan</h4>
                        <p class="text-xs text-muted mt-0.5">Lihat penjualan & keuangan</p>
                    </div>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const rawLabels = {!! json_encode($grafik7Hari->pluck('tanggal')) !!};
        const rawData = {!! json_encode($grafik7Hari->pluck('total')->map(fn($v) => (float) $v)) !!};

        // Build 7 hari lengkap
        const allLabels = [];
        const allData = [];
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            const key = d.toISOString().slice(0, 10);
            const label = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            allLabels.push(label);
            const idx = rawLabels.indexOf(key);
            allData.push(idx !== -1 ? rawData[idx] : 0);
        }

        new Chart(document.getElementById('tren7HariChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: allLabels,
                datasets: [{
                    label: 'Omzet (Rp)',
                    data: allData,
                    backgroundColor: 'rgba(14, 131, 136, 0.18)',
                    borderColor: '#0E8388',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'Omzet: Rp ' + ctx.raw.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => v >= 1000000 ? (v/1000000)+'jt' : v >= 1000 ? (v/1000)+'rb' : v,
                            font: { size: 10 }
                        },
                        grid: { color: '#e2e8f0' }
                    },
                    x: {
                        ticks: { font: { size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>

@if($labaRugi)
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('labaRugiChart');
        if (!ctx) return;

        const fmtRp = v => 'Rp ' + Number(v).toLocaleString('id-ID');
        const fmtShort = v => v >= 1000000 ? (v/1000000)+'jt' : v >= 1000 ? (v/1000)+'rb' : v;

        let lrChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: { labels: [], datasets: [] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: { callbacks: { label: c => c.dataset.label + ': ' + fmtRp(c.raw) } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: fmtShort, font: { size: 10 } },
                        grid: { color: '#e2e8f0' }
                    },
                    x: { ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 0 }, grid: { display: false } }
                }
            }
        });

        const datasetDefs = [
            { key: 'pendapatan', label: 'Pendapatan', color: '#0E8388' },
            { key: 'labaKotor',  label: 'Laba Kotor',  color: '#22c55e' },
            { key: 'beban',      label: 'Beban Operasional', color: '#f59e0b' },
            { key: 'labaBersih', label: 'Laba Bersih',  color: '#6366f1' }
        ];

        function total(arr) { return arr.reduce((a, b) => a + Number(b), 0); }

        async function muatLabaRugi(periode) {
            try {
                const res = await fetch('{{ route("dashboard.laba-rugi") }}?periode=' + periode, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();

                lrChart.data.labels = data.labels;
                lrChart.data.datasets = datasetDefs.map(d => ({
                    label: d.label,
                    data: data[d.key],
                    borderColor: d.color,
                    backgroundColor: d.color + '22',
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    fill: d.key === 'labaBersih'
                }));
                lrChart.update();

                document.getElementById('lrPendapatan').textContent = fmtRp(total(data.pendapatan));
                document.getElementById('lrLabaKotor').textContent = fmtRp(total(data.labaKotor));
                document.getElementById('lrLabaBersih').textContent = fmtRp(total(data.labaBersih));
            } catch (e) {
                console.error('Gagal memuat dashboard laba rugi:', e);
            }
        }

        const tombol = document.querySelectorAll('#periodeToggle button');
        tombol.forEach(btn => {
            btn.addEventListener('click', () => {
                tombol.forEach(b => {
                    b.classList.remove('bg-teal-700', 'text-white', 'shadow-sm');
                    b.classList.add('text-muted');
                });
                btn.classList.add('bg-teal-700', 'text-white', 'shadow-sm');
                btn.classList.remove('text-muted');
                muatLabaRugi(btn.dataset.periode);
            });
        });

        // Muat default = bulanan (data server sudah berisi untuk render awal)
        muatLabaRugi('bulanan');
    });
</script>
@endif
