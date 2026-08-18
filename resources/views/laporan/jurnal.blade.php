@extends('layouts.app')

@section('title', 'Buku Jurnal Keuangan')
@section('page_title', 'Buku Jurnal Keuangan')

@section('content')
<div class="space-y-6">

    {{-- Filter Jurnal --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
        <form method="GET" action="{{ route('laporan.jurnal') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Akun Keuangan</label>
                <select name="akun" class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted bg-surface">
                    <option value="">Semua Akun</option>
                    @foreach($akuns as $a)
                    <option value="{{ $a }}" {{ request('akun') == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex-1 min-w-0">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-sm transition">
                    Filter
                </button>
                @if(request()->hasAny(['akun', 'tanggal_mulai', 'tanggal_akhir']))
                <a href="{{ route('laporan.jurnal') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition text-center">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Daftar Jurnal --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="font-bold font-display text-neutral-dark text-lg">Jurnal Double-Entry</h3>
            <p class="text-xs text-muted mt-0.5">Semua entri jurnal otomatis dari aktivitas penjualan kasir.</p>
        </div>
        <div class="flex gap-2">
            @php $jurnalParams = array_filter(['akun' => request('akun'), 'tanggal_mulai' => request('tanggal_mulai'), 'tanggal_akhir' => request('tanggal_akhir')]); @endphp
            <x-button variant="danger" href="{{ route('laporan.export', 'jurnal') }}{{ $jurnalParams ? '?' . http_build_query($jurnalParams) : '' }}">
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
        ['label' => 'Tanggal', 'class' => 'text-left'],
        ['label' => 'Kode', 'class' => 'text-left'],
        ['label' => 'Akun & Keterangan', 'class' => 'text-left'],
        ['label' => 'Debet', 'class' => 'text-right'],
        ['label' => 'Kredit', 'class' => 'text-right'],
    ]">
        <x-slot:desktop>
            @forelse($jurnals as $j)
            {{-- Row 1: Debet --}}
            <tr class="hover:bg-body-bg/20">
                <td class="px-3 sm:px-6 py-3 text-muted font-medium align-top text-xs sm:text-sm whitespace-nowrap" rowspan="2">{{ $j->tanggal->format('d M Y') }}</td>
                <td class="px-3 sm:px-6 py-3 font-mono text-xs text-muted align-top" rowspan="2">{{ $j->kode_jurnal }}</td>
                <td class="px-3 sm:px-6 py-2"><span class="font-semibold text-neutral-dark text-sm">{{ $j->akun_debit }}</span></td>
                <td class="px-3 sm:px-6 py-2 text-right font-bold text-neutral-dark text-sm">Rp {{ number_format($j->nominal, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-2 text-right text-muted">—</td>
            </tr>
            <tr class="border-b border-border-soft hover:bg-body-bg/20 bg-body-bg/5">
                <td class="px-3 sm:px-6 py-2">
                    <span class="font-semibold text-neutral-dark pl-4 sm:pl-6 text-sm">{{ $j->akun_kredit }}</span>
                    @if($j->keterangan)
                    <p class="text-xs text-muted mt-0.5 pl-4 sm:pl-6 font-normal">({{ $j->keterangan }})</p>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-2 text-right text-muted">—</td>
                <td class="px-3 sm:px-6 py-2 text-right font-bold text-neutral-dark text-sm">Rp {{ number_format($j->nominal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-3 sm:px-6 py-12 text-center text-muted">Tidak ada entri pencatatan jurnal keuangan ditemukan.</td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($jurnals as $j)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-mono text-xs font-semibold text-muted">{{ $j->kode_jurnal }}</p>
                        <p class="text-xs text-muted">{{ $j->tanggal->format('d M Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="font-semibold text-neutral-dark">{{ $j->akun_debit }}</span>
                    <span class="font-bold text-neutral-dark">Rp {{ number_format($j->nominal, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="font-semibold text-neutral-dark pl-3 border-l-2 border-border-soft">{{ $j->akun_kredit }}</span>
                    <span class="font-bold text-neutral-dark">Rp {{ number_format($j->nominal, 0, ',', '.') }}</span>
                </div>
                @if($j->keterangan)
                <p class="text-xs text-muted italic">({{ $j->keterangan }})</p>
                @endif
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <p class="font-medium text-muted">Tidak ada entri pencatatan jurnal keuangan ditemukan.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    @if($jurnals->hasPages())
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm px-3 sm:px-6 py-4">{{ $jurnals->links() }}</div>
    @endif
</div>
@endsection
