@extends('layouts.app')

@section('title', 'Tambah Aturan Diskon')
@section('page_title', 'Tambah Aturan Diskon')

@section('content')
<div class="max-w-2xl">
    <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-6">
        <form action="{{ route('aturan-diskon.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Produk <span class="text-rose-700">*</span></label>
                <select name="produk_id" required
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition">
                    <option value="">Pilih produk...</option>
                    @foreach($produks as $produk)
                    <option value="{{ $produk->id }}" @selected(old('produk_id') == $produk->id)>
                        {{ $produk->nama_produk }} ({{ $produk->kode_produk }})
                    </option>
                    @endforeach
                </select>
                @error('produk_id')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Tipe Diskon <span class="text-rose-700">*</span></label>
<select name="tipe_diskon" id="tipeDiskon" required
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition">
                        <option value="nominal" @selected(old('tipe_diskon') == 'nominal')>Nominal (Rp)</option>
                        <option value="persen" @selected(old('tipe_diskon') == 'persen')>Persen (%)</option>
                        <option value="free-packaging" @selected(old('tipe_diskon') == 'free-packaging')>Gratis Pengemasan (Rp/unit)</option>
                    </select>
                    @error('tipe_diskon')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nilai Diskon <span class="text-rose-700">*</span></label>
                    <input type="number" step="0.01" min="0" name="nilai_diskon" value="{{ old('nilai_diskon') }}"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                        placeholder="cth. 5000 atau 10" required>
                    @error('nilai_diskon')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}"
                        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                        class="w-full px-4 py-2.5 border border-border rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition">
                    @error('tanggal_selesai')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <label class="flex items-center space-x-3 pt-1">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                    class="h-4 w-4 text-primary border-border-soft rounded">
                <span class="text-sm font-semibold text-neutral-dark">Aktif</span>
            </label>

            <div class="flex justify-end space-x-3 pt-2">
                <a href="{{ route('aturan-diskon.index') }}"
                    class="px-5 py-2.5 text-sm font-semibold text-muted bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</a>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection