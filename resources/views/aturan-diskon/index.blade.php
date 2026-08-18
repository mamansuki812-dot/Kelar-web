@extends('layouts.app')

@section('title', 'Aturan Diskon')
@section('page_title', 'Aturan Diskon')

@section('content')
<div class="space-y-6">

    {{-- Header + Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Diskon otomatis per produk saat ditambahkan ke keranjang POS.</p>
        </div>
        <a href="{{ route('aturan-diskon.create') }}">
            <x-button variant="primary" size="lg" class="w-full sm:w-auto">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Tambah Aturan</span>
            </x-button>
        </a>
    </div>

    {{-- Flash success --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    {{-- Tabel (Desktop) / Kartu (Mobile) --}}
    <x-responsive-table :headers="[
        ['label' => 'Produk', 'class' => 'text-left w-2/5'],
        ['label' => 'Diskon', 'class' => 'text-center'],
        ['label' => 'Periode', 'class' => 'text-center'],
        ['label' => 'Status', 'class' => 'text-center'],
        ['label' => 'Aksi', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($aturanDiskons as $ad)
            <tr class="hover:bg-body-bg/70 transition-colors">
                <td class="px-3 sm:px-6 py-3">
                    <p class="font-semibold text-neutral-dark text-sm">{{ $ad->produk?->nama_produk }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $ad->produk?->kode_produk }}</p>
                </td>
                @php $labelDiskon = $ad->tipe_diskon === 'persen' ? $ad->nilai_diskon . '%' : ($ad->tipe_diskon === 'free-packaging' ? 'Gratis kemasan ' . $ad->nilai_diskon . '/unit' : 'Rp ' . number_format($ad->nilai_diskon, 0, ',', '.')); @endphp
                <td class="px-3 sm:px-6 py-3 text-center">
                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full bg-teal-50 text-teal-700">
                        {{ $labelDiskon }}
                    </span>
                </td>
                <td class="px-3 sm:px-6 py-3 text-center text-sm text-muted">
                    {{ $ad->tanggal_mulai?->format('d M Y') ?? '—' }}
                    s/d
                    {{ $ad->tanggal_selesai?->format('d M Y') ?? '—' }}
                </td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full
                        {{ $ad->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-muted' }}">
                        {{ $ad->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="px-3 sm:px-6 py-3">
                    <div class="flex items-center justify-center space-x-2">
                        <a href="{{ route('aturan-diskon.edit', $ad->id) }}"
                            class="p-2 text-primary hover:bg-teal-50 rounded-lg transition-colors" title="Edit">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form id="del-ad-{{ $ad->id }}" action="{{ route('aturan-diskon.destroy', $ad->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="appConfirm('Hapus Aturan','Yakin ingin menghapus aturan diskon untuk {{ addslashes($ad->produk?->nama_produk) }}?').then(function(ok){ if(ok) document.getElementById('del-ad-{{ $ad->id }}').submit(); })" class="p-2 text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-3 sm:px-6 py-16 text-center text-muted">
                    <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="font-medium">Belum ada aturan diskon.</p>
                    <p class="text-sm mt-1">Klik tombol "Tambah Aturan" untuk membuat aturan pertama.</p>
                </td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($aturanDiskons as $ad)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Produk</span>
                    <span class="text-sm font-semibold text-neutral-dark text-right">{{ $ad->produk?->nama_produk }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Diskon</span>
                    <span class="text-sm font-semibold text-teal-700">{{ $labelDiskon }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Status</span>
                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full
                        {{ $ad->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-muted' }}">
                        {{ $ad->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="flex justify-end gap-3 pt-2 border-t border-slate-50">
                    <a href="{{ route('aturan-diskon.edit', $ad->id) }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-primary bg-teal-50 hover:bg-teal-100 rounded-xl transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <form id="del-ad-m-{{ $ad->id }}" action="{{ route('aturan-diskon.destroy', $ad->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="appConfirm('Hapus Aturan','Yakin ingin menghapus aturan diskon untuk {{ addslashes($ad->produk?->nama_produk) }}?').then(function(ok){ if(ok) document.getElementById('del-ad-m-{{ $ad->id }}').submit(); })"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="font-medium text-muted">Belum ada aturan diskon.</p>
                <p class="text-sm text-muted mt-1">Klik tombol "Tambah Aturan" untuk memulai.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    {{-- Pagination --}}
    @if($aturanDiskons->hasPages())
    <div class="px-3 sm:px-6 py-4 border-t border-border-soft bg-body-bg/50">
        {{ $aturanDiskons->links() }}
    </div>
    @endif
</div>
@endsection
