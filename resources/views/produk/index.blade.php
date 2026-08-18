@extends('layouts.app')

@section('title', 'Kelola Produk')
@section('page_title', 'Kelola Produk')

@section('content')
<div class="space-y-6">

    {{-- Header + Tombol --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Kelola produk, stok, dan kode barcode toko.</p>
        </div>
        <x-button variant="primary" size="lg" class="w-full sm:w-auto" onclick="openModal('modalTambah')">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Tambah Produk</span>
        </x-button>
    </div>

    {{-- Filter / Search Bar --}}
    <form method="GET" action="{{ route('produk.index') }}" class="bg-surface rounded-2xl border border-border-soft shadow-sm p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-muted">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama atau kode produk..."
                class="w-full pl-10 pr-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition text-sm">
        </div>
        <select name="kategori_id"
            class="w-full sm:w-auto px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition text-sm text-muted">
            <option value="">Semua Kategori</option>
            @foreach($kategoris as $kat)
                <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
            @endforeach
        </select>
        <div class="flex gap-3">
        <button type="submit" class="flex-1 sm:flex-none px-5 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl transition">Filter</button>
        @if(request('search') || request('kategori_id'))
            <a href="{{ route('produk.index') }}" class="flex-1 sm:flex-none px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted text-sm font-semibold rounded-xl transition text-center">Reset</a>
        @endif
        </div>
    </form>

    {{-- Tabel (Desktop) / Kartu (Mobile) Produk --}}
    <x-responsive-table :headers="[
        ['label' => 'Produk', 'class' => 'text-left'],
        ['label' => 'Harga', 'class' => 'text-right'],
        ['label' => 'Stok', 'class' => 'text-center'],
        ['label' => 'Aksi', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($produks as $i => $p)
            <tr class="hover:bg-body-bg/70 transition-colors">
                <td class="px-3 sm:px-6 py-3">
                    <div class="flex items-center space-x-3">
                        @if($p->gambar)
                            <img src="{{ Storage::url($p->gambar) }}" alt="{{ $p->nama_produk }}"
                                class="h-10 w-10 rounded-lg object-cover border border-border-soft flex-shrink-0">
                        @else
                            <div class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center text-muted flex-shrink-0">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="font-semibold text-neutral-dark text-sm truncate">{{ $p->nama_produk }}</p>
                            <p class="text-xs text-muted">{{ $p->satuan }}</p>
                            <p class="text-xs text-muted hidden sm:block">{{ $p->kode_produk }}</p>
                            <p class="text-xs text-muted hidden md:block">{{ $p->kategori->nama_kategori ?? '—' }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-3 sm:px-6 py-3 text-right">
                    <p class="text-sm font-semibold text-neutral-dark">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</p>
                    <p class="text-xs text-muted hidden sm:block">Beli: Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</p>
                </td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    @php $stokKritis = $p->stok <= 0; $stokTipis = !$stokKritis && $p->stok <= $p->stok_minimum; @endphp
                    <x-stock-badge :status="$stokKritis ? 'habis' : ($stokTipis ? 'menipis' : 'aman')">
                        {{ $p->stok }} {{ $p->satuan }}
                    </x-stock-badge>
                    @if(!$stokKritis && $p->is_active)
                    <form action="{{ route('produk.toggle', $p->id) }}" method="POST" class="mt-1">
                        @csrf @method('PATCH')
                        <button type="submit"
                            class="text-xs font-semibold rounded-full px-2 py-0.5 cursor-pointer transition
                            {{ $p->is_active ? 'text-emerald-700 hover:bg-emerald-50' : 'text-muted hover:bg-slate-100' }}">
                            {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </form>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    <button onclick="openEditModal({{ $p->id }}, {{ $p->toJson() }})"
                        class="p-2 text-primary hover:bg-teal-50 rounded-lg transition-colors" title="Edit">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-3 sm:px-6 py-16 text-center text-muted">
                    <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="font-medium">Belum ada produk.</p>
                    <p class="text-sm mt-1">Klik "Tambah Produk" untuk mulai mengisi data produk.</p>
                </td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($produks as $i => $p)
            @php $stokKritis = $p->stok <= 0; $stokTipis = !$stokKritis && $p->stok <= $p->stok_minimum; @endphp
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-start gap-3">
                    @if($p->gambar)
                        <img src="{{ Storage::url($p->gambar) }}" alt="{{ $p->nama_produk }}"
                            class="h-12 w-12 rounded-lg object-cover border border-border-soft flex-shrink-0">
                    @else
                        <div class="h-12 w-12 rounded-lg bg-slate-100 flex items-center justify-center text-muted flex-shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-neutral-dark text-sm">{{ $p->nama_produk }}</p>
                        <p class="text-xs text-muted">{{ $p->kode_produk }} · {{ $p->kategori->nama_kategori ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Harga Jual</span>
                    <span class="text-sm font-semibold text-neutral-dark">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Harga Beli</span>
                    <span class="text-sm text-muted">Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Stok</span>
                    <div class="flex items-center gap-2">
                        <x-stock-badge :status="$stokKritis ? 'habis' : ($stokTipis ? 'menipis' : 'aman')">
                            {{ $p->stok }} {{ $p->satuan }}
                        </x-stock-badge>
                        @if(!$stokKritis && $p->is_active)
                        <form action="{{ route('produk.toggle', $p->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs font-semibold {{ $p->is_active ? 'text-emerald-700' : 'text-muted' }}">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="flex justify-end pt-2 border-t border-slate-50">
                    <button onclick="openEditModal({{ $p->id }}, {{ $p->toJson() }})"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-primary bg-teal-50 hover:bg-teal-100 rounded-xl transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>
                </div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="font-medium text-muted">Belum ada produk.</p>
                <p class="text-sm text-muted mt-1">Klik "Tambah Produk" untuk mulai mengisi data produk.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    @if($produks->hasPages())
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm px-3 sm:px-6 py-4">{{ $produks->links() }}</div>
    @endif
</div>

{{-- Komponen Form Produk (dipakai oleh modal tambah & edit) --}}
@php
function formProduk($kategoris, $isEdit = false) {
    // Dirender langsung di Blade — tidak pakai PHP function, lihat blade template di bawah
}
@endphp

{{-- MODAL TAMBAH PRODUK --}}
<div id="modalTambah" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalTambah')"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-soft sticky top-0 bg-surface z-10">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Tambah Produk Baru</h3>
            <button onclick="closeModal('modalTambah')" class="text-muted hover:text-muted">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('produk.store') }}" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="_modal" value="modalTambah">
            {{-- Kode Produk (auto-focus untuk siap terima scan barcode — US-029) --}}
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">
                    Kode Produk / Barcode <span class="text-rose-700">*</span>
                    <span class="text-xs font-normal text-muted ml-1">(scan atau ketik manual)</span>
                </label>
                <div class="flex gap-2">
                    <input type="text" id="kode_produk_tambah" name="kode_produk" value="{{ old('kode_produk') }}"
                        class="flex-1 px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition font-mono tracking-widest"
                        placeholder="Scan barcode atau ketik kode produk" required>
                    <button type="button" onclick="bukaScanKamera('kode_produk_tambah')"
                        class="px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl transition flex items-center gap-2 text-sm font-semibold shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span class="hidden sm:inline">Scan</span>
                    </button>
                </div>
                @error('kode_produk')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nama Produk <span class="text-rose-700">*</span></label>
                <input type="text" name="nama_produk" value="{{ old('nama_produk') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="cth. Aqua 600ml" required>
                @error('nama_produk')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Kategori <span class="text-rose-700">*</span></label>
                    <select name="kategori_id" class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition text-sm" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                    @error('kategori_id')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Satuan <span class="text-rose-700">*</span></label>
                    <input type="text" name="satuan" value="{{ old('satuan', 'pcs') }}"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    @error('satuan')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Harga Beli (Rp) <span class="text-rose-700">*</span></label>
                    <input type="number" name="harga_beli" value="{{ old('harga_beli', 0) }}" min="0" step="100"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    @error('harga_beli')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Harga Jual (Rp) <span class="text-rose-700">*</span></label>
                    <input type="number" name="harga_jual" value="{{ old('harga_jual', 0) }}" min="1" step="100"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    @error('harga_jual')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Stok Awal <span class="text-rose-700">*</span></label>
                    <input type="number" name="stok" value="{{ old('stok', 0) }}" min="0"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    @error('stok')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Stok Minimum <span class="text-rose-700">*</span></label>
                    <input type="number" name="stok_minimum" value="{{ old('stok_minimum', 5) }}" min="0"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    @error('stok_minimum')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Gambar Produk</label>
                <input type="file" name="gambar" accept="image/jpg,image/jpeg,image/png"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition text-sm text-muted
                    file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-primary hover:file:bg-teal-100">
                <p class="text-xs text-muted mt-1">Format: JPG, PNG. Maks 2 MB.</p>
                @error('gambar')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeModal('modalTambah')"
                    class="px-5 py-2.5 text-sm font-semibold text-muted bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl transition shadow-sm">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT PRODUK --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalEdit')"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-soft sticky top-0 bg-surface z-10">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Edit Produk</h3>
            <button onclick="closeModal('modalEdit')" class="text-muted hover:text-muted">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formEdit" action="" method="POST" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="_modal" value="modalEdit">
            <input type="hidden" name="_produk_id" id="edit_produk_id" value="">
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">
                    Kode Produk / Barcode <span class="text-rose-700">*</span>
                    <span class="text-xs font-normal text-muted ml-1">(scan atau ketik manual)</span>
                </label>
                <div class="flex gap-2">
                    <input type="text" id="edit_kode" name="kode_produk"
                        class="flex-1 px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition font-mono tracking-widest" required>
                    <button type="button" onclick="bukaScanKamera('edit_kode')"
                        class="px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl transition flex items-center gap-2 text-sm font-semibold shadow-sm">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span class="hidden sm:inline">Scan</span>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nama Produk <span class="text-rose-700">*</span></label>
                <input type="text" id="edit_nama" name="nama_produk"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Kategori <span class="text-rose-700">*</span></label>
                    <select id="edit_kategori" name="kategori_id" class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition text-sm" required>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Satuan <span class="text-rose-700">*</span></label>
                    <input type="text" id="edit_satuan" name="satuan"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Harga Beli (Rp)</label>
                    <input type="number" id="edit_harga_beli" name="harga_beli" min="0" step="100"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    @error('harga_beli')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Harga Jual (Rp)</label>
                    <input type="number" id="edit_harga_jual" name="harga_jual" min="1" step="100"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    @error('harga_jual')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Stok</label>
                    <input type="number" id="edit_stok" name="stok" min="0"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    @error('stok')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Stok Minimum</label>
                    <input type="number" id="edit_stok_min" name="stok_minimum" min="0"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    @error('stok_minimum')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Ganti Gambar (opsional)</label>
                <div id="edit_preview_container" class="hidden mb-2">
                    <img id="edit_gambar_preview" src="" alt="Gambar saat ini" class="h-20 w-20 object-cover rounded-xl border border-border-soft">
                    <p class="text-xs text-muted mt-1">Gambar saat ini</p>
                </div>
                <input type="file" name="gambar" accept="image/jpg,image/jpeg,image/png"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl outline-none transition text-sm text-muted
                    file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-primary hover:file:bg-teal-100">
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeModal('modalEdit')"
                    class="px-5 py-2.5 text-sm font-semibold text-muted bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl transition shadow-sm">Perbarui Produk</button>
            </div>
        </form>
    </div>
</div>

{{-- Buka modal yang benar jika ada error validasi (tambah vs edit) --}}
@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', () => {
    const target = '{{ old('_modal', 'modalTambah') }}';
    openModal(target);
    if (target === 'modalEdit') {
        const pid = '{{ old('_produk_id', '') }}';
        if (pid) document.getElementById('formEdit').action = '/produk/' + pid;
        document.getElementById('edit_kode').value = @json(old('kode_produk', ''));
        document.getElementById('edit_nama').value = @json(old('nama_produk', ''));
        document.getElementById('edit_kategori').value = @json(old('kategori_id', ''));
        document.getElementById('edit_satuan').value = @json(old('satuan', ''));
        document.getElementById('edit_harga_beli').value = @json(old('harga_beli', ''));
        document.getElementById('edit_harga_jual').value = @json(old('harga_jual', ''));
        document.getElementById('edit_stok').value = @json(old('stok', ''));
        document.getElementById('edit_stok_min').value = @json(old('stok_minimum', ''));
    }
});
</script>
@endif

{{-- MODAL SCAN KAMERA --}}
<div id="modalScanKamera" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" onclick="tutupScanKamera()"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-border-soft">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Scan Barcode</h3>
            <button onclick="tutupScanKamera()" class="text-muted hover:text-muted">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-4">
            <div id="reader-produk" style="width:100%;"></div>
            <p id="scan-status" class="text-xs text-muted text-center mt-3">Arahkan kamera ke barcode...</p>
            <p id="scan-hint-produk" class="text-xs text-muted text-center mt-1.5 leading-relaxed hidden">
                Jaga jarak 10-15cm, pastikan barcode tidak buram/silau. Gunakan lampu jika perlu.
            </p>
            <div class="flex justify-center mt-3 gap-3">
                <button id="btn-torch-produk" onclick="toggleTorchProduk()" class="hidden items-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-muted rounded-xl text-xs font-semibold transition">
                    <svg id="torch-icon-produk" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span id="torch-text-produk">Lampu</span>
                </button>
            </div>
            <div id="scan-controls-produk" class="mt-3 space-y-2"></div>
        </div>
        <div class="px-5 pb-4">
            <button onclick="tutupScanKamera()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition">Tutup</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/barcode-engine.js')
@include('components.scanner-engine')
<script>
    // ===== DOM Functions =====
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
        if (id === 'modalTambah') {
            setTimeout(() => document.getElementById('kode_produk_tambah')?.focus(), 100);
        }
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
    function openEditModal(id, data) {
        document.getElementById('formEdit').action = `/produk/${id}`;
        document.getElementById('edit_produk_id').value = id;
        document.getElementById('edit_kode').value = data.kode_produk;
        document.getElementById('edit_nama').value = data.nama_produk;
        document.getElementById('edit_kategori').value = data.kategori_id;
        document.getElementById('edit_satuan').value = data.satuan;
        document.getElementById('edit_harga_beli').value = data.harga_beli;
        document.getElementById('edit_harga_jual').value = data.harga_jual;
        document.getElementById('edit_stok').value = data.stok;
        document.getElementById('edit_stok_min').value = data.stok_minimum;

        if (data.gambar) {
            document.getElementById('edit_gambar_preview').src = '/storage/' + data.gambar;
            document.getElementById('edit_preview_container').classList.remove('hidden');
        } else {
            document.getElementById('edit_preview_container').classList.add('hidden');
        }

        openModal('modalEdit');
        setTimeout(() => document.getElementById('edit_kode')?.focus(), 100);
    }

    // ===== Scanner State =====
    let scanTargetInput = null;
    let isScanningProduk = false;

    // ===== bukaScanKamera =====
    async function bukaScanKamera(targetInputId) {
        if (isScanningProduk) return;
        isScanningProduk = true;

        scanTargetInput = document.getElementById(targetInputId);
        var modal = document.getElementById('modalScanKamera');
        var status = document.getElementById('scan-status');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        status.textContent = 'Memulai kamera...';
        status.className = 'text-xs text-muted text-center mt-3';

        KELARScanner.open({
            containerId: 'reader-produk',
            statusId: 'scan-status',
            tipsId: 'scan-hint-produk',
            torchBtnId: 'btn-torch-produk',
            torchIconId: 'torch-icon-produk',
            torchTextId: 'torch-text-produk',
            controlsId: 'scan-controls-produk',
            onSuccess: onScanSuccess
        });
    }

    // ===== onScanSuccess =====
    function onScanSuccess(kode) {
        if (!isScanningProduk) return;
        isScanningProduk = false;

        document.getElementById('scan-status').textContent = 'Berhasil: ' + kode;
        document.getElementById('scan-status').className = 'text-xs text-emerald-700 font-semibold text-center mt-3';

        KELARScanner.close();

        if (scanTargetInput) scanTargetInput.value = kode;

        setTimeout(function() {
            document.getElementById('modalScanKamera').classList.add('hidden');
            document.getElementById('modalScanKamera').classList.remove('flex');
            if (scanTargetInput) { scanTargetInput.focus(); scanTargetInput = null; }
        }, 400);
    }

    // ===== tutupScanKamera =====
    function tutupScanKamera() {
        isScanningProduk = false;
        KELARScanner.close();
        document.getElementById('reader-produk').innerHTML = '';
        document.getElementById('modalScanKamera').classList.add('hidden');
        document.getElementById('modalScanKamera').classList.remove('flex');
    }

    // ===== Torch / Flash =====
    function toggleTorchProduk() {
        KELARScanner.toggleTorch();
    }
</script>
@endsection
