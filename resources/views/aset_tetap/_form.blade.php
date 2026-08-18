@php $aset = $aset ?? null; @endphp
<div>
    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nama Aset <span class="text-rose-700">*</span></label>
    <input type="text" name="nama_aset" value="{{ old('nama_aset', $aset?->nama_aset) }}"
        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
        placeholder="cth. Mesin Kasir, Kulkas, Sepeda Motor" required maxlength="150">
    @error('nama_aset')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Kode Aset</label>
        <input type="text" name="kode_aset" value="{{ old('kode_aset', $aset?->kode_aset) }}"
            class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
            placeholder="cth. AT-001" maxlength="30">
        @error('kode_aset')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Kategori</label>
        <input type="text" name="kategori_aset" value="{{ old('kategori_aset', $aset?->kategori_aset) }}"
            class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
            placeholder="cth. Peralatan" maxlength="100">
    </div>
</div>
<div>
    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Tanggal Perolehan <span class="text-rose-700">*</span></label>
    <input type="date" name="tanggal_perolehan" value="{{ old('tanggal_perolehan', $aset?->tanggal_perolehan?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
    @error('tanggal_perolehan')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Harga Perolehan (Rp) <span class="text-rose-700">*</span></label>
        <input type="number" name="harga_perolehan" value="{{ old('harga_perolehan', $aset?->harga_perolehan) }}" min="0" step="0.01"
            class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
            placeholder="cth. 5000000" required>
        @error('harga_perolehan')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nilai Residu (Rp)</label>
        <input type="number" name="nilai_residu" value="{{ old('nilai_residu', $aset?->nilai_residu ?? 0) }}" min="0" step="0.01"
            class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition">
    </div>
    <div>
        <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Masa Manfaat (Bulan)</label>
        <input type="number" name="masa_manfaat_bulan" value="{{ old('masa_manfaat_bulan', $aset?->masa_manfaat_bulan) }}" min="1"
            class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
            placeholder="cth. 60">
    </div>
</div>
<div>
    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Akumulasi Penyusutan (Rp)</label>
    <input type="number" name="akumulasi_penyusutan" value="{{ old('akumulasi_penyusutan', $aset?->akumulasi_penyusutan ?? 0) }}" min="0" step="0.01"
        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition">
    <p class="text-xs text-muted mt-1">Nilai buku = harga perolehan − akumulasi penyusutan. Kosongkan/mulai dari 0 untuk aset baru.</p>
</div>
<div>
    <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Keterangan</label>
    <input type="text" name="keterangan" value="{{ old('keterangan', $aset?->keterangan) }}"
        class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
        placeholder="opsional" maxlength="255">
</div>