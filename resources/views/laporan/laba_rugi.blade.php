@extends('layouts.app')

@section('title', 'Laporan Laba Rugi')
@section('page_title', 'Laporan Laba Rugi')

@section('content')
<div class="space-y-6">

    {{-- Filter Periode --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
        <form method="GET" action="{{ route('laporan.laba-rugi') }}" class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai->format('Y-m-d') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Sampai Tanggal</label>
                <input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir->format('Y-m-d') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-sm transition">
                    Filter Laporan
                </button>
                <a href="{{ route('laporan.laba-rugi') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition text-center">
                    Bulan Ini
                </a>
            </div>
        </form>
    </div>

    {{-- Ringkasan Laba Rugi (SAK EMKM) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Pendapatan Penjualan (Neto)</p>
            <p class="text-3xl font-bold font-display text-emerald-700 mt-2">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
            <p class="text-xs text-muted mt-1">Setelah dikurangi diskon Rp {{ number_format($totalDiskon, 0, ',', '.') }}</p>
        </div>

        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Harga Pokok Penjualan (HPP)</p>
            <p class="text-3xl font-bold font-display text-rose-700 mt-2">Rp {{ number_format($hpp, 0, ',', '.') }}</p>
            <p class="text-xs text-muted mt-1">Berdasarkan akumulasi harga beli/modal</p>
        </div>

        <div class="bg-surface rounded-2xl border border-teal-100 shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Laba Kotor (Gross Profit)</p>
            <p class="text-3xl font-bold font-display text-teal-700 mt-2">Rp {{ number_format($labaKotor, 0, ',', '.') }}</p>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-xs font-semibold text-primary bg-teal-50 px-2 py-0.5 rounded">Margin: {{ $marginPersen }}%</span>
                <span class="text-xs text-muted">Profitabilitas usaha</span>
            </div>
        </div>
    </div>

    {{-- Beban & Laba Bersih --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Beban Operasional</p>
            <p class="text-3xl font-bold font-display text-rose-700 mt-2">Rp {{ number_format($totalBeban, 0, ',', '.') }}</p>
            <p class="text-xs text-muted mt-1">Pengeluaran di luar HPP periode ini</p>
        </div>

        <div class="bg-surface rounded-2xl border {{ $labaBersih >= 0 ? 'border-emerald-100' : 'border-rose-100' }} shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Laba Bersih (Net Profit)</p>
            <p class="text-3xl font-bold font-display {{ $labaBersih >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-2">Rp {{ number_format($labaBersih, 0, ',', '.') }}</p>
            <p class="text-xs text-muted mt-1">Laba Kotor dikurangi total beban</p>
        </div>
    </div>

    {{-- Laba Rugi Berdasarkan Kategori Produk --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="font-bold font-display text-neutral-dark text-lg">Breakdown Kategori</h3>
        <div class="flex gap-2">
            <x-button variant="danger" href="{{ route('laporan.export', 'laba-rugi') }}?tanggal_mulai={{ $tanggalMulai->format('Y-m-d') }}&tanggal_akhir={{ $tanggalAkhir->format('Y-m-d') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </x-button>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-border-soft hover:bg-body-bg rounded-lg text-xs font-semibold text-muted transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-5a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
        </div>
    </div>

    <x-responsive-table :headers="[
        ['label' => 'Kategori', 'class' => 'text-left'],
        ['label' => 'Terjual', 'class' => 'text-right'],
        ['label' => 'Pendapatan', 'class' => 'text-right'],
        ['label' => 'HPP', 'class' => 'text-right'],
        ['label' => 'Laba', 'class' => 'text-right'],
        ['label' => 'Margin', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($kategoriBreakdown as $row)
            <tr class="hover:bg-body-bg/50 transition-colors">
                <td class="px-3 sm:px-6 py-3 font-semibold text-neutral-dark text-sm">{{ $row->nama_kategori }}</td>
                <td class="px-3 sm:px-6 py-3 text-right text-muted text-sm">{{ $row->total_qty }} pcs</td>
                <td class="px-3 sm:px-6 py-3 text-right font-medium text-neutral-dark text-sm">Rp {{ number_format($row->total_penjualan, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-right text-rose-700 text-sm">Rp {{ number_format($row->total_hpp, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-right font-bold text-emerald-700 text-sm">Rp {{ number_format($row->laba, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">{{ $row->margin }}%</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-3 sm:px-6 py-12 text-center text-muted">Tidak ada penjualan pada kategori mana pun di periode ini.</td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($kategoriBreakdown as $row)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <p class="font-semibold text-neutral-dark text-sm">{{ $row->nama_kategori }}</p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Terjual</span>
                    <span class="text-muted">{{ $row->total_qty }} pcs</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Pendapatan</span>
                    <span class="font-medium text-neutral-dark">Rp {{ number_format($row->total_penjualan, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">HPP</span>
                    <span class="text-rose-700">Rp {{ number_format($row->total_hpp, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm pt-2 border-t border-slate-50">
                    <span class="text-muted font-medium">Laba</span>
                    <span class="font-bold text-emerald-700">Rp {{ number_format($row->laba, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Margin</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">{{ $row->margin }}%</span>
                </div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="font-medium text-muted">Tidak ada penjualan pada kategori mana pun di periode ini.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>
</div>
@endsection
