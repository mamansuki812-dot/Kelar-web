@extends('layouts.app')

@section('title', 'Kelola Supplier')
@section('page_title', 'Kelola Supplier')

@section('content')
<div class="space-y-6">

    {{-- Header + Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Kelola data pemasok barang untuk toko.</p>
        </div>
        <x-button variant="primary" size="lg" class="w-full sm:w-auto" onclick="openModal('modalTambah')">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Tambah Supplier</span>
        </x-button>
    </div>

    {{-- Tabel (Desktop) / Kartu (Mobile) Supplier --}}
    <x-responsive-table :headers="[
        ['label' => 'Supplier', 'class' => 'text-left'],
        ['label' => 'Kontak', 'class' => 'text-left hidden sm:table-cell'],
        ['label' => 'Email', 'class' => 'text-left hidden md:table-cell'],
        ['label' => 'Aksi', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($suppliers as $i => $sup)
            <tr class="hover:bg-body-bg/70 transition-colors">
                <td class="px-3 sm:px-6 py-3">
                    <p class="font-semibold text-neutral-dark text-sm">{{ $sup->nama_supplier }}</p>
                    <p class="text-xs text-muted sm:hidden">{{ $sup->kontak ?? '—' }}</p>
                </td>
                <td class="px-3 sm:px-6 py-3 text-sm text-muted hidden sm:table-cell">
                    {{ $sup->kontak ?? '—' }}
                </td>
                <td class="px-3 sm:px-6 py-3 text-sm text-muted hidden md:table-cell">
                    {{ $sup->email ?? '—' }}
                </td>
                <td class="px-3 sm:px-6 py-3">
                    <div class="flex items-center justify-center space-x-2">
                        <button onclick="openEditModal({{ $sup->id }}, '{{ addslashes($sup->nama_supplier) }}', '{{ addslashes($sup->kontak) }}', '{{ addslashes($sup->email) }}', '{{ addslashes($sup->alamat) }}')"
                            class="p-2 text-primary hover:bg-teal-50 rounded-lg transition-colors" title="Edit">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <form id="del-sup-{{ $sup->id }}" action="{{ route('supplier.destroy', $sup->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="button" onclick="appConfirm('Hapus Supplier','Yakin ingin menghapus supplier {{ $sup->nama_supplier }}?').then(function(ok){ if(ok) document.getElementById('del-sup-{{ $sup->id }}').submit(); })" class="p-2 text-rose-700 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus">
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
                <td colspan="4" class="px-3 sm:px-6 py-16 text-center text-muted">
                    <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="font-medium">Belum ada data supplier.</p>
                    <p class="text-sm mt-1">Klik tombol "Tambah Supplier" untuk memulai.</p>
                </td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($suppliers as $i => $sup)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Supplier</span>
                    <span class="text-sm font-semibold text-neutral-dark text-right">{{ $sup->nama_supplier }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Kontak</span>
                    <span class="text-sm text-muted">{{ $sup->kontak ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Email</span>
                    <span class="text-sm text-muted">{{ $sup->email ?? '—' }}</span>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-50">
                    <button onclick="openEditModal({{ $sup->id }}, '{{ addslashes($sup->nama_supplier) }}', '{{ addslashes($sup->kontak) }}', '{{ addslashes($sup->email) }}', '{{ addslashes($sup->alamat) }}')"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold text-primary bg-teal-50 hover:bg-teal-100 rounded-xl transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </button>
                    <form id="del-sup-m-{{ $sup->id }}" action="{{ route('supplier.destroy', $sup->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="button" onclick="appConfirm('Hapus Supplier','Yakin ingin menghapus supplier {{ $sup->nama_supplier }}?').then(function(ok){ if(ok) document.getElementById('del-sup-m-{{ $sup->id }}').submit(); })"
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
                <p class="font-medium text-muted">Belum ada data supplier.</p>
                <p class="text-sm text-muted mt-1">Klik tombol "Tambah Supplier" untuk memulai.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>
</div>

{{-- MODAL TAMBAH SUPPLIER --}}
<div id="modalTambah" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalTambah')"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-soft">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Tambah Supplier Baru</h3>
            <button onclick="closeModal('modalTambah')" class="text-muted hover:text-muted">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('supplier.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nama Supplier <span class="text-rose-700">*</span></label>
                <input type="text" name="nama_supplier" value="{{ old('nama_supplier') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="cth. PT. Maju Bersama" required autofocus>
                @error('nama_supplier')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Kontak</label>
                <input type="text" name="kontak" value="{{ old('kontak') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="cth. 08123456789">
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="cth. info@majubersama.com">
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Alamat</label>
                <textarea name="alamat" rows="2"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition resize-none"
                    placeholder="Alamat lengkap supplier (opsional)">{{ old('alamat') }}</textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeModal('modalTambah')"
                    class="px-5 py-2.5 text-sm font-semibold text-muted bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT SUPPLIER --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalEdit')"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-soft">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Edit Supplier</h3>
            <button onclick="closeModal('modalEdit')" class="text-muted hover:text-muted">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formEdit" action="" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nama Supplier <span class="text-rose-700">*</span></label>
                <input type="text" id="editNama" name="nama_supplier"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Kontak</label>
                <input type="text" id="editKontak" name="kontak"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Email</label>
                <input type="email" id="editEmail" name="email"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Alamat</label>
                <textarea id="editAlamat" name="alamat" rows="2"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition resize-none"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeModal('modalEdit')"
                    class="px-5 py-2.5 text-sm font-semibold text-muted bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl transition shadow-sm">Perbarui</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.addEventListener('DOMContentLoaded', () => openModal('modalTambah'));</script>
@endif
@endsection

@section('scripts')
<script>
    function openModal(id) {
        const el = document.getElementById(id);
        el.classList.remove('hidden');
        el.classList.add('flex');
    }
    function closeModal(id) {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
    }
    function openEditModal(id, nama, kontak, email, alamat) {
        document.getElementById('formEdit').action = `/supplier/${id}`;
        document.getElementById('editNama').value = nama;
        document.getElementById('editKontak').value = kontak;
        document.getElementById('editEmail').value = email;
        document.getElementById('editAlamat').value = alamat;
        openModal('modalEdit');
    }
</script>
@endsection
