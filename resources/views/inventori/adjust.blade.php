@extends('layouts.app')

@section('title', 'Penyesuaian Stok')
@section('page_title', 'Penyesuaian Stok (Stock Opname)')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-soft flex items-center justify-between">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Form Penyesuaian Stok</h3>
            <a href="{{ route('inventori.index') }}" class="text-sm font-semibold text-primary hover:text-primary-dark">Batal & Kembali</a>
        </div>
        <form action="{{ route('inventori.storeAdjust') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Pilih Produk <span class="text-rose-700">*</span></label>
                <select name="produk_id" class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                    <option value="" disabled selected>-- Pilih Produk --</option>
                    @foreach($produks as $p)
                    <option value="{{ $p->id }}" {{ old('produk_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->kode_produk }} - {{ $p->nama_produk }} (Stok saat ini: {{ $p->stok }})
                    </option>
                    @endforeach
                </select>
                @error('produk_id')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Jenis Penyesuaian <span class="text-rose-700">*</span></label>
                    <select name="jenis" class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
                        <option value="" disabled selected>-- Pilih --</option>
                        <option value="masuk" {{ old('jenis') == 'masuk' ? 'selected' : '' }}>Tambah Stok (+)</option>
                        <option value="keluar" {{ old('jenis') == 'keluar' ? 'selected' : '' }}>Kurangi Stok (-)</option>
                    </select>
                    @error('jenis')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Jumlah <span class="text-rose-700">*</span></label>
                    <input type="number" name="jumlah" min="1" value="{{ old('jumlah') }}"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                        placeholder="Contoh: 5" required>
                    @error('jumlah')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Alasan/Keterangan <span class="text-rose-700">*</span></label>
                <textarea name="keterangan" rows="2"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition resize-none"
                    placeholder="Contoh: Barang rusak / Hilang / Selisih opname" required>{{ old('keterangan') }}</textarea>
                @error('keterangan')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            
            <div class="pt-4 flex justify-end space-x-3">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl transition shadow-sm flex items-center space-x-2">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Penyesuaian</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
