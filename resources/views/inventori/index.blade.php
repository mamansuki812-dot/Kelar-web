@extends('layouts.app')

@section('title', 'Inventori & Stok')
@section('page_title', 'Manajemen Inventori')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Pantau stok real-time, terima barang, dan lakukan penyesuaian stok.</p>
        </div>
        @if(in_array(auth()->user()->role, ['admin', 'gudang']))
        <div class="flex flex-wrap gap-2">
            <x-button variant="success" href="{{ route('inventori.receive') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Terima Barang
            </x-button>
            <x-button variant="primary" href="{{ route('inventori.adjust') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                Penyesuaian
            </x-button>
            <x-button variant="neutral" href="{{ route('inventori.history') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Riwayat Stok
            </x-button>
            <x-button variant="neutral" href="{{ route('supplier.index') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Supplier
            </x-button>
        </div>
        @endif
    </div>

    {{-- Stats Cards --}}
    @php
        $totalProduk = \App\Models\Produk::where('is_active', true)->count();
        $stokNormal  = \App\Models\Produk::where('is_active', true)->whereRaw('stok > stok_minimum')->count();
        $stokMenipis = \App\Models\Produk::where('is_active', true)->whereRaw('stok <= stok_minimum')->where('stok', '>', 0)->count();
        $stokHabis   = \App\Models\Produk::where('is_active', true)->where('stok', '<=', 0)->count();
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Produk --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-xl bg-teal-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-primary bg-teal-50 px-2 py-0.5 rounded-full">Semua</span>
            </div>
            <p class="text-3xl font-bold font-display text-neutral-dark">{{ $totalProduk }}</p>
            <p class="text-sm text-muted mt-1">Total Produk Aktif</p>
        </div>

        {{-- Stok Normal --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Aman</span>
            </div>
            <p class="text-3xl font-bold font-display text-emerald-700">{{ $stokNormal }}</p>
            <p class="text-sm text-muted mt-1">Stok Normal</p>
        </div>

        {{-- Stok Menipis --}}
        <div class="bg-surface rounded-2xl border border-amber-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">Perhatian</span>
            </div>
            <p class="text-3xl font-bold font-display text-amber-700">{{ $stokMenipis }}</p>
            <p class="text-sm text-muted mt-1">Stok Menipis</p>
        </div>

        {{-- Stok Habis --}}
        <div class="bg-surface rounded-2xl border border-rose-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="h-10 w-10 rounded-xl bg-rose-50 flex items-center justify-center">
                    <svg class="h-5 w-5 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full">Kritis</span>
            </div>
            <p class="text-3xl font-bold font-display text-rose-700">{{ $stokHabis }}</p>
            <p class="text-sm text-muted mt-1">Stok Habis</p>
        </div>
    </div>

    {{-- Peringatan Stok Menipis/Habis --}}
    @if($stokMenipis > 0 || $stokHabis > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-3">
        <svg class="h-5 w-5 text-amber-700 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div class="text-sm">
            <p class="font-semibold text-amber-800">Perhatian Stok!</p>
            <p class="text-amber-700 mt-0.5">
                Terdapat <strong>{{ $stokHabis }}</strong> produk stok habis dan <strong>{{ $stokMenipis }}</strong> produk stok menipis.
                Segera lakukan penerimaan barang.
            </p>
        </div>
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('inventori.index') }}" class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 flex flex-col md:flex-row gap-3 items-end">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-3 flex items-center text-muted pointer-events-none">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode produk..."
                class="w-full pl-10 pr-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
        </div>

        <div class="flex flex-wrap gap-2">
            <select name="kategori_id" class="flex-1 sm:flex-none px-3 sm:px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted bg-surface">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                @endforeach
            </select>

            <select name="status" class="flex-1 sm:flex-none px-3 sm:px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted bg-surface">
                <option value="">Semua Status</option>
                <option value="normal" {{ request('status') === 'normal' ? 'selected' : '' }}>ðŸŸ¢ Normal</option>
                <option value="minimum" {{ request('status') === 'minimum' ? 'selected' : '' }}>ðŸŸ¡ Menipis</option>
                <option value="habis" {{ request('status') === 'habis' ? 'selected' : '' }}>ðŸ”´ Habis</option>
            </select>

            <div class="flex gap-2 flex-1 sm:flex-none">
                <button type="submit" class="flex-1 sm:flex-none px-3 sm:px-5 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-sm transition">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'kategori_id', 'status']))
                    <a href="{{ route('inventori.index') }}" class="flex-1 sm:flex-none px-3 sm:px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition text-center">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Stock Table --}}
    <x-responsive-table :headers="[
        ['label' => 'Produk', 'class' => 'text-left'],
        ['label' => 'Stok', 'class' => 'text-center'],
        ['label' => 'Status', 'class' => 'text-center'],
        ['label' => 'Riwayat', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($produks as $prod)
            @php
                if ($prod->stok <= 0) { $badgeBg='bg-rose-100 text-rose-700'; $rowBg='bg-rose-50/30'; $barColor='bg-rose-400'; $statusText='Habis'; $stokColor='text-rose-700'; $barPct=0;
                } elseif ($prod->stok <= $prod->stok_minimum) { $badgeBg='bg-amber-100 text-amber-700'; $rowBg='bg-amber-50/30'; $barColor='bg-amber-400'; $statusText='Menipis'; $stokColor='text-amber-700'; $max=max($prod->stok_minimum*2,1); $barPct=min(100,round(($prod->stok/$max)*100));
                } else { $badgeBg='bg-emerald-100 text-emerald-700'; $rowBg=''; $barColor='bg-emerald-400'; $statusText='Normal'; $stokColor='text-emerald-700'; $max=max($prod->stok_minimum*3,1); $barPct=min(100,round(($prod->stok/$max)*100)); }
            @endphp
            <tr class="hover:bg-body-bg/70 transition-colors {{ $rowBg }}">
                <td class="px-3 sm:px-5 py-3">
                    <div class="flex items-center gap-2 sm:gap-3">
                        @if($prod->gambar)
                            <img src="{{ asset('storage/' . $prod->gambar) }}" class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg object-cover flex-shrink-0" alt="">
                        @else
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300 flex-shrink-0">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-neutral-dark leading-tight truncate">{{ $prod->nama_produk }}</p>
                            <p class="text-xs text-muted mt-0.5 font-mono">{{ $prod->kode_produk }}</p>
                            <p class="text-xs text-muted sm:hidden">Min: {{ $prod->stok_minimum }} {{ $prod->satuan }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-3 sm:px-5 py-3 text-center">
                    <span class="text-lg font-bold {{ $stokColor }}">{{ $prod->stok }}</span>
                    <span class="text-xs text-muted ml-0.5">{{ $prod->satuan }}</span>
                    <p class="text-xs text-muted hidden sm:block">Min: {{ $prod->stok_minimum }}</p>
                    <div class="mt-1.5 h-1.5 bg-slate-100 rounded-full overflow-hidden w-16 sm:w-24 mx-auto">
                        <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ $barPct }}%"></div>
                    </div>
                </td>
                <td class="px-3 sm:px-5 py-3 text-center">
                    <x-stock-badge dot :status="$statusText === 'Habis' ? 'habis' : ($statusText === 'Menipis' ? 'menipis' : 'aman')">
                        {{ $statusText }}
                    </x-stock-badge>
                </td>
                <td class="px-3 sm:px-5 py-3 text-center">
                    <a href="{{ route('inventori.history', ['produk_id' => $prod->id]) }}"
                       class="inline-flex items-center gap-1 px-2 sm:px-3 py-1.5 text-xs font-semibold text-primary hover:bg-teal-50 hover:text-primary-dark rounded-lg transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="hidden sm:inline">Lihat</span>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-3 sm:px-6 py-16 text-center text-muted">
                    <svg class="h-12 w-12 mx-auto mb-3 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="font-semibold text-muted">Tidak ada produk ditemukan.</p>
                    <p class="text-sm mt-1">Coba ubah filter pencarian Anda.</p>
                </td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($produks as $prod)
            @php
                if ($prod->stok <= 0) { $badgeBg='bg-rose-100 text-rose-700'; $barColor='bg-rose-400'; $statusText='Habis'; $stokColor='text-rose-700'; $barPct=0;
                } elseif ($prod->stok <= $prod->stok_minimum) { $badgeBg='bg-amber-100 text-amber-700'; $barColor='bg-amber-400'; $statusText='Menipis'; $stokColor='text-amber-700'; $max=max($prod->stok_minimum*2,1); $barPct=min(100,round(($prod->stok/$max)*100));
                } else { $badgeBg='bg-emerald-100 text-emerald-700'; $barColor='bg-emerald-400'; $statusText='Normal'; $stokColor='text-emerald-700'; $max=max($prod->stok_minimum*3,1); $barPct=min(100,round(($prod->stok/$max)*100)); }
            @endphp
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-start gap-3">
                    @if($prod->gambar)
                        <img src="{{ asset('storage/' . $prod->gambar) }}" class="h-10 w-10 rounded-lg object-cover flex-shrink-0" alt="">
                    @else
                        <div class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-300 flex-shrink-0">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-neutral-dark text-sm">{{ $prod->nama_produk }}</p>
                        <p class="text-xs text-muted font-mono">{{ $prod->kode_produk }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Stok</span>
                    <div class="text-right">
                        <span class="text-lg font-bold {{ $stokColor }}">{{ $prod->stok }}</span>
                        <span class="text-xs text-muted ml-0.5">{{ $prod->satuan }}</span>
                        <p class="text-xs text-muted">Min: {{ $prod->stok_minimum }}</p>
                    </div>
                </div>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ $barPct }}%"></div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Status</span>
                    <x-stock-badge dot :status="$statusText === 'Habis' ? 'habis' : ($statusText === 'Menipis' ? 'menipis' : 'aman')">
                        {{ $statusText }}
                    </x-stock-badge>
                </div>
                <div class="flex justify-end pt-2 border-t border-slate-50">
                    <a href="{{ route('inventori.history', ['produk_id' => $prod->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-primary bg-teal-50 hover:bg-teal-100 rounded-xl transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Riwayat
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <svg class="h-12 w-12 mx-auto mb-3 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="font-semibold text-muted">Tidak ada produk ditemukan.</p>
                <p class="text-sm text-muted mt-1">Coba ubah filter pencarian Anda.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    @if($produks->hasPages())
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm px-3 sm:px-6 py-4">{{ $produks->links() }}</div>
    @endif
</div>
@endsection
