@extends('layouts.app')

@section('title', 'Riwayat Stok')
@section('page_title', 'Riwayat Pergerakan Stok')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Daftar keluar masuk barang.</p>
        </div>
        <div class="flex space-x-2">
            <x-button variant="neutral" href="{{ route('inventori.index') }}">Kembali ke Inventori</x-button>
        </div>
    </div>

    {{-- Filter Form --}}
    <form action="{{ route('inventori.history') }}" method="GET" class="bg-surface p-5 rounded-2xl border border-border-soft shadow-sm flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-1">Produk</label>
            <select name="produk_id" class="w-full px-3 py-2 border border-border-soft rounded-xl text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                <option value="">Semua Produk</option>
                @foreach($produks as $p)
                <option value="{{ $p->id }}" {{ request('produk_id') == $p->id ? 'selected' : '' }}>{{ $p->nama_produk }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-1">Jenis</label>
            <select name="jenis" class="w-full px-3 py-2 border border-border-soft rounded-xl text-sm focus:ring-2 focus:ring-primary/30 outline-none">
                <option value="">Semua Jenis</option>
                <option value="masuk" {{ request('jenis') == 'masuk' ? 'selected' : '' }}>Stok Masuk</option>
                <option value="keluar" {{ request('jenis') == 'keluar' ? 'selected' : '' }}>Stok Keluar</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-1">Mulai</label>
            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="px-3 py-2 border border-border-soft rounded-xl text-sm focus:ring-2 focus:ring-primary/30 outline-none">
        </div>
        <div>
            <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-1">Sampai</label>
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="px-3 py-2 border border-border-soft rounded-xl text-sm focus:ring-2 focus:ring-primary/30 outline-none">
        </div>
        <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl transition">Filter</button>
        <a href="{{ route('inventori.history') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-muted text-sm font-semibold rounded-xl transition text-center">Reset</a>
    </form>

    {{-- Tabel Riwayat --}}
    <x-responsive-table :headers="[
        ['label' => 'Riwayat', 'class' => 'text-left'],
        ['label' => 'Perubahan', 'class' => 'text-left'],
        ['label' => 'Stok Akhir', 'class' => 'text-center'],
        ['label' => 'User', 'class' => 'text-left hidden lg:table-cell'],
    ]">
        <x-slot:desktop>
            @forelse($histories as $h)
            <tr class="hover:bg-body-bg/70 transition-colors text-sm">
                <td class="px-3 sm:px-6 py-3">
                    <p class="font-medium text-neutral-dark text-sm">{{ $h->produk->nama_produk ?? 'Terhapus' }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $h->created_at->format('d M Y H:i') }}</p>
                    @if($h->keterangan)
                    <p class="text-xs text-muted mt-0.5 truncate max-w-[180px]" title="{{ $h->keterangan }}">{{ $h->keterangan }}</p>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-3">
                    <div class="flex items-center gap-2">
                        @if($h->jenis == 'masuk')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Masuk</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Keluar</span>
                        @endif
                        <span class="font-semibold {{ $h->jenis == 'masuk' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $h->jenis == 'masuk' ? '+' : '-' }}{{ $h->jumlah }}
                        </span>
                    </div>
                </td>
                <td class="px-3 sm:px-6 py-3 text-center text-muted font-medium">{{ $h->stok_sesudah }}</td>
                <td class="px-3 sm:px-6 py-3 text-muted hidden lg:table-cell">{{ $h->user->name ?? 'Sistem' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-3 sm:px-6 py-16 text-center text-muted">
                    <p class="font-medium">Tidak ada riwayat stok yang ditemukan.</p>
                </td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($histories as $h)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div>
                    <p class="font-medium text-neutral-dark text-sm">{{ $h->produk->nama_produk ?? 'Terhapus' }}</p>
                    <p class="text-xs text-muted">{{ $h->created_at->format('d M Y H:i') }}</p>
                </div>
                @if($h->keterangan)
                <p class="text-xs text-muted">{{ $h->keterangan }}</p>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Perubahan</span>
                    <div class="flex items-center gap-2">
                        @if($h->jenis == 'masuk')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Masuk</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">Keluar</span>
                        @endif
                        <span class="font-semibold {{ $h->jenis == 'masuk' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $h->jenis == 'masuk' ? '+' : '-' }}{{ $h->jumlah }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Stok Akhir</span>
                    <span class="text-muted font-semibold">{{ $h->stok_sesudah }}</span>
                </div>
                <div class="text-xs text-muted">User: {{ $h->user->name ?? 'Sistem' }}</div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <p class="font-medium text-muted">Tidak ada riwayat stok yang ditemukan.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    @if($histories->hasPages())
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm px-3 sm:px-6 py-4">{{ $histories->links() }}</div>
    @endif
</div>
@endsection
