@extends('layouts.app')

@section('title', 'Kelola Pengguna')
@section('page_title', 'Kelola Pengguna')

@section('content')
<div class="space-y-6">

    {{-- Header + Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Kelola data login, peran (role), dan status aktif karyawan toko.</p>
        </div>
        <x-button variant="primary" size="lg" class="w-full sm:w-auto" onclick="openModal('modalTambah')">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Tambah Karyawan</span>
        </x-button>
    </div>

    {{-- Filter / Search Bar --}}
    <form method="GET" action="{{ route('users.index') }}" class="bg-surface rounded-2xl border border-border-soft shadow-sm p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <span class="absolute inset-y-0 left-3 flex items-center text-muted">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama, username, atau email..."
                class="w-full pl-10 pr-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition text-sm">
        </div>
        <select name="role"
            class="w-full sm:w-auto px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition text-sm text-muted">
            <option value="">Semua Peran</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
            <option value="pemilik" {{ request('role') == 'pemilik' ? 'selected' : '' }}>Pemilik</option>
            <option value="gudang" {{ request('role') == 'gudang' ? 'selected' : '' }}>Petugas Gudang</option>
        </select>
        <div class="flex gap-3">
        <button type="submit" class="flex-1 sm:flex-none px-5 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl transition">Filter</button>
        @if(request('search') || request('role'))
            <a href="{{ route('users.index') }}" class="flex-1 sm:flex-none px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted text-sm font-semibold rounded-xl transition text-center">Reset</a>
        @endif
        </div>
    </form>

    {{-- Tabel (Desktop) / Kartu (Mobile) Pengguna --}}
    <x-responsive-table :headers="[
        ['label' => 'Pengguna', 'class' => 'text-left'],
        ['label' => 'Peran', 'class' => 'text-center'],
        ['label' => 'Status', 'class' => 'text-center'],
        ['label' => 'Aksi', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($users as $i => $u)
            <tr class="hover:bg-body-bg/70 transition-colors">
                <td class="px-3 sm:px-6 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="h-9 w-9 rounded-full bg-teal-50 text-primary flex items-center justify-center font-bold font-display text-sm flex-shrink-0">
                            {{ strtoupper(substr($u->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-neutral-dark text-sm truncate">{{ $u->name }}</p>
                            <p class="text-xs text-muted font-mono">{{ $u->username }}</p>
                            <p class="text-xs text-muted hidden sm:block">{{ $u->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    @php
                        $roleColors = ['admin' => 'bg-rose-100 text-rose-700', 'kasir' => 'bg-emerald-100 text-emerald-700', 'pemilik' => 'bg-teal-100 text-teal-700', 'gudang' => 'bg-amber-100 text-amber-700'];
                        $roleNames = ['admin' => 'Admin', 'kasir' => 'Kasir', 'pemilik' => 'Pemilik', 'gudang' => 'Gudang'];
                    @endphp
                    <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $roleColors[$u->role] ?? 'bg-slate-100 text-muted' }}">
                        {{ $roleNames[$u->role] ?? $u->role }}
                    </span>
                </td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    @if(auth()->id() === $u->id)
                        <span class="text-xs font-semibold text-emerald-700" title="Akun Anda">Aktif</span>
                    @else
                        <form action="{{ route('users.toggle', $u->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full cursor-pointer transition
                                {{ $u->is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-muted hover:bg-slate-200' }}"
                                title="{{ $u->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                                {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    <button onclick="openEditModal({{ $u->id }}, {{ $u->toJson() }})"
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="font-medium">Belum ada karyawan.</p>
                </td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @php
                $roleColors = ['admin' => 'bg-rose-100 text-rose-700', 'kasir' => 'bg-emerald-100 text-emerald-700', 'pemilik' => 'bg-teal-100 text-teal-700', 'gudang' => 'bg-amber-100 text-amber-700'];
                $roleNames = ['admin' => 'Admin', 'kasir' => 'Kasir', 'pemilik' => 'Pemilik', 'gudang' => 'Gudang'];
            @endphp
            @forelse($users as $i => $u)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-teal-50 text-primary flex items-center justify-center font-bold font-display text-sm flex-shrink-0">
                        {{ strtoupper(substr($u->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-neutral-dark text-sm">{{ $u->name }}</p>
                        <p class="text-xs text-muted font-mono">{{ $u->username }} · {{ $u->email }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Peran</span>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $roleColors[$u->role] ?? 'bg-slate-100 text-muted' }}">
                        {{ $roleNames[$u->role] ?? $u->role }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">Status</span>
                    @if(auth()->id() === $u->id)
                        <span class="text-xs font-semibold text-emerald-700">Aktif (Akun Anda)</span>
                    @else
                        <form action="{{ route('users.toggle', $u->id) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="px-3 py-1.5 text-xs font-semibold rounded-full cursor-pointer transition
                                {{ $u->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-muted' }}">
                                {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    @endif
                </div>
                <div class="flex justify-end pt-2 border-t border-slate-50">
                    <button onclick="openEditModal({{ $u->id }}, {{ $u->toJson() }})"
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="font-medium text-muted">Belum ada karyawan.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    @if($users->hasPages())
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm px-3 sm:px-6 py-4">{{ $users->links() }}</div>
    @endif
</div>

{{-- MODAL TAMBAH KARYAWAN --}}
<div id="modalTambah" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalTambah')"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-soft sticky top-0 bg-surface z-10">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Tambah Karyawan Baru</h3>
            <button onclick="closeModal('modalTambah')" class="text-muted hover:text-muted">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('users.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nama Lengkap <span class="text-rose-700">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="cth. Rikni Winur" required autofocus>
                @error('name')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Username <span class="text-rose-700">*</span></label>
                <input type="text" name="username" value="{{ old('username') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition font-mono"
                    placeholder="cth. rikni12" required>
                @error('username')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Email <span class="text-rose-700">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="cth. rikni@toko.com" required>
                @error('email')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Password <span class="text-rose-700">*</span></label>
                <input type="password" name="password"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="Minimal 6 karakter" required>
                @error('password')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Peran / Role <span class="text-rose-700">*</span></label>
                <select name="role" class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition text-sm" required>
                    <option value="">Pilih Role</option>
                    <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="gudang" {{ old('role') == 'gudang' ? 'selected' : '' }}>Gudang</option>
                    <option value="pemilik" {{ old('role') == 'pemilik' ? 'selected' : '' }}>Pemilik</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
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

{{-- MODAL EDIT KARYAWAN --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalEdit')"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-soft sticky top-0 bg-surface z-10">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Edit Karyawan</h3>
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
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nama Lengkap <span class="text-rose-700">*</span></label>
                <input type="text" id="edit_name" name="name"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Username <span class="text-rose-700">*</span></label>
                <input type="text" id="edit_username" name="username"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition font-mono" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Email <span class="text-rose-700">*</span></label>
                <input type="email" id="edit_email" name="email"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Ganti Password <span class="text-xs font-normal text-muted ml-1">(kosongkan jika tidak diganti)</span></label>
                <input type="password" name="password"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="Masukkan password baru">
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Peran / Role <span class="text-rose-700">*</span></label>
                <select id="edit_role" name="role" class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition text-sm" required>
                    <option value="kasir">Kasir</option>
                    <option value="gudang">Gudang</option>
                    <option value="pemilik">Pemilik</option>
                    <option value="admin">Admin</option>
                </select>
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

{{-- Buka modal jika ada error validasi --}}
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded', () => openModal('modalTambah'));</script>
@endif
@endsection

@section('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
    function openEditModal(id, data) {
        document.getElementById('formEdit').action = `/users/${id}`;
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_username').value = data.username;
        document.getElementById('edit_email').value = data.email;
        document.getElementById('edit_role').value = data.role;
        openModal('modalEdit');
    }
</script>
@endsection
