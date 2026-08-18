@extends('layouts.app')

@section('title', 'Laporan Stok')
@section('page_title', 'Laporan Stok Barang')

@section('content')
<div class="space-y-6">

    {{-- Header Laporan Stok --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Snapshot nilai modal investasi barang dagangan saat ini.</p>
        </div>
        <div class="flex gap-2">
            <x-button variant="danger" href="{{ route('laporan.export', 'stok') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export PDF
            </x-button>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-border-soft hover:bg-body-bg rounded-lg text-xs font-semibold text-muted transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-5a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Estimasi Nilai Aset (Harga Jual)</p>
            <p class="text-3xl font-bold font-display text-neutral-dark mt-2">Rp {{ number_format($totalNilaiStok, 0, ',', '.') }}</p>
            <p class="text-xs text-muted mt-1">Total valuasi barang dagangan aktif</p>
        </div>

        <div class="bg-surface rounded-2xl border border-rose-100 shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Produk Kosong (Stok Habis)</p>
            <p class="text-3xl font-bold font-display text-rose-700 mt-2">{{ $totalStokHabis }} Produk</p>
            <p class="text-xs text-muted mt-1">Butuh restock darurat</p>
        </div>

        <div class="bg-surface rounded-2xl border border-amber-100 shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Produk Menipis</p>
            <p class="text-3xl font-bold font-display text-amber-700 mt-2">{{ $totalMenipis }} Produk</p>
            <p class="text-xs text-muted mt-1">Stok di bawah batas minimum</p>
        </div>
    </div>

    {{-- Filter Status + Periode --}}
    <form method="GET" action="{{ route('laporan.stok') }}" class="flex flex-wrap items-end gap-2">
        <div>
            <label class="block text-[11px] font-semibold text-muted uppercase tracking-wide mb-1">Status Stok</label>
            <select name="status" class="px-4 py-2 border border-border-soft rounded-xl text-xs outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted bg-surface">
                <option value="">Semua Status Stok</option>
                <option value="normal" {{ request('status') === 'normal' ? 'selected' : '' }}>🟢 Stok Normal</option>
                <option value="menipis" {{ request('status') === 'menipis' ? 'selected' : '' }}>🟡 Stok Menipis</option>
                <option value="habis" {{ request('status') === 'habis' ? 'selected' : '' }}>🔴 Stok Habis</option>
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-muted uppercase tracking-wide mb-1">Periode Mutasi Dari</label>
            <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai->format('Y-m-d') }}" class="px-4 py-2 border border-border-soft rounded-xl text-xs outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted bg-surface">
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-muted uppercase tracking-wide mb-1">Sampai</label>
            <input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir->format('Y-m-d') }}" class="px-4 py-2 border border-border-soft rounded-xl text-xs outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted bg-surface">
        </div>
        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-xl text-xs font-semibold hover:bg-primary-dark transition">Terapkan</button>
        @if(request('status') || request('tanggal_mulai') || request('tanggal_akhir'))
        <a href="{{ route('laporan.stok') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-muted rounded-xl text-xs font-semibold">Reset</a>
        @endif
    </form>

    {{-- Ringkasan Mutasi Periode --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-neutral-dark">Ringkasan Pergerakan Stok</h2>
            <span class="text-xs font-mono text-muted">{{ $tanggalMulai->format('d M Y') }} — {{ $tanggalAkhir->format('d M Y') }}</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="rounded-xl bg-emerald-50 p-4">
                <p class="text-xs text-emerald-700 font-semibold">Barang Masuk</p>
                <p class="text-2xl font-bold text-emerald-800 mt-1">{{ number_format($totalMasuk, 0, ',', '.') }}</p>
                <p class="text-[11px] text-emerald-600 mt-0.5">unit diterima periode</p>
            </div>
            <div class="rounded-xl bg-rose-50 p-4">
                <p class="text-xs text-rose-700 font-semibold">Barang Keluar</p>
                <p class="text-2xl font-bold text-rose-800 mt-1">{{ number_format($totalKeluar, 0, ',', '.') }}</p>
                <p class="text-[11px] text-rose-600 mt-0.5">unit terjual/keluar periode</p>
            </div>
            <div class="rounded-xl bg-slate-100 p-4">
                <p class="text-xs text-slate-600 font-semibold">Penyesuaian</p>
                <p class="text-2xl font-bold text-slate-700 mt-1">{{ $totalPenyesuaian }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">buah penyesuaian stok</p>
            </div>
            <div class="rounded-xl bg-primary/10 p-4">
                <p class="text-xs text-primary-dark font-semibold">Total Mutasi</p>
                <p class="text-2xl font-bold text-primary-dark mt-1">{{ $jumlahRecord }}</p>
                <p class="text-[11px] text-muted mt-0.5">catatan pergerakan</p>
            </div>
        </div>

        @if($mutasiTerakhir->count())
        <div class="mt-5">
            <h3 class="text-xs font-semibold text-muted uppercase tracking-wide mb-2">Mutasi Terakhir dalam Periode</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-border-soft">
                            <th class="text-left py-2 pr-4 text-xs font-semibold text-muted uppercase">Tanggal</th>
                            <th class="text-left py-2 pr-4 text-xs font-semibold text-muted uppercase">Produk</th>
                            <th class="text-left py-2 pr-4 text-xs font-semibold text-muted uppercase">Jenis</th>
                            <th class="text-right py-2 pr-4 text-xs font-semibold text-muted uppercase">Jumlah</th>
                            <th class="text-left py-2 text-xs font-semibold text-muted uppercase">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mutasiTerakhir as $m)
                        <tr class="border-b border-border-soft/60">
                            <td class="py-2 pr-4 text-muted text-xs font-mono">{{ $m->created_at->format('d M Y H:i') }}</td>
                            <td class="py-2 pr-4 font-medium text-neutral-dark text-xs">{{ $m->produk->nama_produk ?? '(produk dihapus)' }}</td>
                            <td class="py-2 pr-4">
                                @if($m->jenis === 'masuk')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">Masuk</span>
                                @elseif($m->jenis === 'keluar')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">Keluar</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">Penyesuaian</span>
                                @endif
                            </td>
                            <td class="py-2 pr-4 text-right font-semibold text-neutral-dark text-xs">{{ number_format($m->jumlah, 0, ',', '.') }} {{ $m->produk->satuan ?? '' }}</td>
                            <td class="py-2 text-muted text-xs">{{ $m->keterangan }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <x-responsive-table :headers="[
        ['label' => 'Produk', 'class' => 'text-left'],
        ['label' => 'Kategori', 'class' => 'text-left'],
        ['label' => 'Harga Beli', 'class' => 'text-right'],
        ['label' => 'Harga Jual', 'class' => 'text-right'],
        ['label' => 'Stok', 'class' => 'text-right'],
        ['label' => 'Total Aset', 'class' => 'text-right'],
        ['label' => 'Status', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($produks as $p)
                @php
                    $totalAsset = $p->stok * $p->harga_jual;
                    if($p->stok <= 0) { $badgeBg='bg-rose-100 text-rose-800'; $statusText='Habis'; $rowBg='bg-rose-50/20';
                    } elseif($p->stok <= $p->stok_minimum) { $badgeBg='bg-amber-100 text-amber-800'; $statusText='Menipis'; $rowBg='bg-amber-50/10';
                    } else { $badgeBg='bg-emerald-100 text-emerald-800'; $statusText='Aman'; $rowBg=''; }
                @endphp
                <tr class="hover:bg-body-bg/50 transition-colors {{ $rowBg }}">
                    <td class="px-3 sm:px-6 py-3">
                        <span class="text-xs font-mono text-muted block">{{ $p->kode_produk }}</span>
                        <span class="font-semibold text-neutral-dark text-sm block mt-0.5">{{ $p->nama_produk }}</span>
                    </td>
                    <td class="px-3 sm:px-6 py-3 text-muted text-sm">{{ $p->kategori->nama_kategori ?? '—' }}</td>
                    <td class="px-3 sm:px-6 py-3 text-right text-muted text-sm">Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</td>
                    <td class="px-3 sm:px-6 py-3 text-right text-muted font-medium text-sm">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                    <td class="px-3 sm:px-6 py-3 text-right">
                        <span class="font-bold text-neutral-dark text-sm">{{ $p->stok }}</span>
                        <span class="text-xs text-muted ml-0.5">{{ $p->satuan }}</span>
                    </td>
                    <td class="px-3 sm:px-6 py-3 text-right font-bold text-teal-700 text-sm">Rp {{ number_format($totalAsset, 0, ',', '.') }}</td>
                    <td class="px-3 sm:px-6 py-3 text-center">
                        <x-stock-badge :status="$statusText === 'Habis' ? 'habis' : ($statusText === 'Menipis' ? 'menipis' : 'aman')">{{ $statusText }}</x-stock-badge>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-3 sm:px-6 py-12 text-center text-muted">Tidak ada produk dagangan aktif terdaftar.</td>
                </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($produks as $p)
                @php
                    $totalAsset = $p->stok * $p->harga_jual;
                    if($p->stok <= 0) { $badgeBg='bg-rose-100 text-rose-800'; $statusText='Habis'; $barColor='bg-rose-400'; $barPct=0;
                    } elseif($p->stok <= $p->stok_minimum) { $badgeBg='bg-amber-100 text-amber-800'; $statusText='Menipis'; $barColor='bg-amber-400'; $max=max($p->stok_minimum*2,1); $barPct=min(100,round(($p->stok/$max)*100));
                    } else { $badgeBg='bg-emerald-100 text-emerald-800'; $statusText='Aman'; $barColor='bg-emerald-400'; $max=max($p->stok_minimum*3,1); $barPct=min(100,round(($p->stok/$max)*100)); }
                @endphp
                <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                    <div>
                        <p class="font-semibold text-neutral-dark text-sm">{{ $p->nama_produk }}</p>
                        <p class="text-xs text-muted font-mono mt-0.5">{{ $p->kode_produk }}</p>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-muted">Kategori</span>
                        <span class="text-muted">{{ $p->kategori->nama_kategori ?? '—' }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <p class="text-xs text-muted">Harga Beli</p>
                            <p class="font-medium text-muted">Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted">Harga Jual</p>
                            <p class="font-medium text-neutral-dark">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted font-medium">Stok</span>
                        <div class="text-right">
                            <span class="font-bold text-neutral-dark">{{ $p->stok }}</span>
                            <span class="text-xs text-muted ml-0.5">{{ $p->satuan }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted font-medium">Total Aset</span>
                        <span class="font-bold text-teal-700">Rp {{ number_format($totalAsset, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ $barPct }}%"></div>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs text-muted font-medium">Status</span>
                        <x-stock-badge :status="$statusText === 'Habis' ? 'habis' : ($statusText === 'Menipis' ? 'menipis' : 'aman')">{{ $statusText }}</x-stock-badge>
                    </div>
                </div>
            @empty
                <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                    <p class="font-medium text-muted">Tidak ada produk dagangan aktif terdaftar.</p>
                </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>
@endsection
