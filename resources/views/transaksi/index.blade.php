@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page_title', 'Riwayat Transaksi')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">
                @if(auth()->user()->role === 'kasir')
                    Transaksi yang Anda proses.
                @else
                    Seluruh transaksi toko.
                @endif
            </p>
        </div>
        @if(in_array(auth()->user()->role, ['kasir','admin']))
        <x-button variant="success" size="lg" class="w-full sm:w-auto" href="{{ route('pos.index') }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Transaksi Baru</span>
        </x-button>
        @endif
    </div>

    {{-- Filter Tanggal --}}
    <form method="GET" action="{{ route('transaksi.index') }}"
        class="bg-surface rounded-2xl border border-border-soft shadow-sm p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex items-center gap-2 flex-1">
            <label class="text-sm font-semibold text-muted whitespace-nowrap">Tanggal:</label>
            <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                class="flex-1 px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
        </div>
        @if(auth()->user()->role === 'admin')
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <label class="text-sm font-semibold text-muted whitespace-nowrap">Kasir:</label>
            <select name="user_id"
                class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted">
                <option value="">Semua Kasir</option>
                @foreach(\App\Models\User::where('role','kasir')->orderBy('name')->get() as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="flex gap-3 w-full sm:w-auto">
        <button type="submit"
            class="flex-1 sm:flex-none px-5 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl transition">Filter</button>
        @if(request()->hasAny(['tanggal','user_id']))
            <a href="{{ route('transaksi.index') }}"
                class="flex-1 sm:flex-none px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted text-sm font-semibold rounded-xl transition text-center">Reset</a>
        @endif
        </div>
    </form>

    {{-- Ringkasan Hari Ini --}}
    @if(!request('tanggal'))
    @php
        $hariIni = auth()->user()->role === 'kasir'
            ? \App\Models\Transaksi::where('user_id', auth()->id())->whereDate('tanggal_transaksi', today())->where('status','selesai')
            : \App\Models\Transaksi::whereDate('tanggal_transaksi', today())->where('status','selesai');
        $jumlahHariIni = (clone $hariIni)->count();
        $omzetHariIni  = (clone $hariIni)->sum('total_bayar');
    @endphp
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Transaksi Hari Ini</p>
            <p class="text-3xl font-bold font-display text-primary">{{ $jumlahHariIni }}</p>
        </div>
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-1">Omzet Hari Ini</p>
            <p class="text-2xl font-bold font-display text-emerald-700">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</p>
        </div>
    </div>
    @endif

    {{-- Tabel (Desktop) / Kartu (Mobile) Transaksi --}}
    <x-responsive-table :headers="[
        ['label' => 'Transaksi', 'class' => 'text-left'],
        ['label' => 'Total', 'class' => 'text-right'],
        ['label' => 'Status', 'class' => 'text-center'],
        ['label' => 'Aksi', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($transaksis as $t)
            <tr class="hover:bg-body-bg/70 transition-colors">
                <td class="px-3 sm:px-6 py-4">
                    <p class="font-mono text-sm font-semibold text-neutral-dark">{{ $t->kode_transaksi }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $t->tanggal_transaksi->format('d/m/Y H:i') }}</p>
                    @if(auth()->user()->role === 'admin')
                    <p class="text-xs text-muted mt-0.5 hidden sm:block">{{ $t->user->name ?? '-' }}</p>
                    @endif
                    <p class="text-xs text-muted mt-0.5 hidden md:block">
                        @php $metodeLabel = ['tunai'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS','midtrans'=>'Midtrans']; @endphp
                        {{ $metodeLabel[$t->metode_pembayaran] ?? $t->metode_pembayaran }}
                    </p>
                </td>
                <td class="px-3 sm:px-6 py-4 text-right">
                    <p class="font-bold text-sm text-neutral-dark">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</p>
                    @if($t->diskon > 0)
                    <p class="text-xs text-rose-400">- Rp {{ number_format($t->diskon, 0, ',', '.') }}</p>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-4 text-center">
                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full
                        {{ $t->status === 'selesai' ? 'bg-emerald-100 text-emerald-700' : ($t->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                        {{ ucfirst($t->status) }}
                    </span>
                </td>
                <td class="px-3 sm:px-6 py-4 text-center">
                    @if($t->metode_pembayaran === 'midtrans' && $t->status === 'pending' && (auth()->user()->role === 'admin' || $t->user_id === auth()->id()))
                    <button type="button" onclick="batalPending({{ $t->id }}, '{{ $t->kode_transaksi }}')"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 rounded-lg transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="hidden sm:inline">Batalkan</span>
                    </button>
                    @endif
                    <a href="{{ route('transaksi.show', $t->id) }}"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-primary hover:bg-teal-50 rounded-lg transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="hidden sm:inline">Detail</span>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-3 sm:px-6 py-16 text-center text-muted">
                    <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="font-medium">Belum ada transaksi.</p>
                    <p class="text-sm mt-1">Mulai transaksi dari halaman POS.</p>
                </td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @php $metodeLabel = ['tunai'=>'Tunai','transfer'=>'Transfer','qris'=>'QRIS','midtrans'=>'Midtrans']; @endphp
            @forelse($transaksis as $t)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-mono font-semibold text-neutral-dark text-sm">{{ $t->kode_transaksi }}</p>
                        <p class="text-xs text-muted mt-0.5">{{ $t->tanggal_transaksi->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                        {{ $t->status === 'selesai' ? 'bg-emerald-100 text-emerald-700' : ($t->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                        {{ ucfirst($t->status) }}
                    </span>
                </div>
                @if(auth()->user()->role === 'admin')
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Kasir</span>
                    <span class="text-xs text-muted">{{ $t->user->name ?? '-' }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Metode</span>
                    <span class="text-xs text-muted">{{ $metodeLabel[$t->metode_pembayaran] ?? $t->metode_pembayaran }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Total</span>
                    <div class="text-right">
                        <p class="font-bold text-sm text-neutral-dark">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</p>
                        @if($t->diskon > 0)
                        <p class="text-xs text-rose-400">Diskon: Rp {{ number_format($t->diskon, 0, ',', '.') }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex justify-end pt-2 border-t border-slate-50 gap-2">
                    @if($t->metode_pembayaran === 'midtrans' && $t->status === 'pending' && (auth()->user()->role === 'admin' || $t->user_id === auth()->id()))
                    <button type="button" onclick="batalPending({{ $t->id }}, '{{ $t->kode_transaksi }}')"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batalkan
                    </button>
                    @endif
                    <a href="{{ route('transaksi.show', $t->id) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-primary bg-teal-50 hover:bg-teal-100 rounded-xl transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Detail
                    </a>
                </div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="font-medium text-muted">Belum ada transaksi.</p>
                <p class="text-sm text-muted mt-1">Mulai transaksi dari halaman POS.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    @if($transaksis->hasPages())
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm px-3 sm:px-6 py-4">{{ $transaksis->links() }}</div>
    @endif
</div>
@endsection

@section('scripts')
<script>
async function batalPending(id, kode){
    const ok = await appConfirm('Batalkan Transaksi',
        'Batalkan transaksi pending ' + kode + '? Stok tidak terpengaruh karena belum dikurangi.',
        { confirmText: 'Ya, Batalkan' });
    if(!ok) return;
    try{
        const res = await fetch('/transaksi/' + id + '/batal-pending', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await res.json();
        if(!res.ok) throw new Error(data.message || 'Gagal membatalkan transaksi.');
        appAlert('Berhasil', data.message, { type: 'success' }).then(function(){ window.location.reload(); });
    }catch(e){
        appAlert('Error', e.message, { type: 'error' });
    }
}
</script>
@endsection
