@extends('layouts.app')

@section('title', 'Audit Trail')
@section('page_title', 'Audit Trail')

@section('content')
<div class="space-y-6">

    {{-- Filter --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
        <form method="GET" action="{{ route('audit-trail.index') }}" class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Pengguna</label>
                <select name="user_id" class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition bg-surface">
                    <option value="">Semua Pengguna</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Jenis Aksi</label>
                <select name="aksi" class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition bg-surface">
                    <option value="">Semua Aksi</option>
                    <option value="create" {{ request('aksi') === 'create' ? 'selected' : '' }}>Create (Tambah)</option>
                    <option value="update" {{ request('aksi') === 'update' ? 'selected' : '' }}>Update (Ubah)</option>
                    <option value="delete" {{ request('aksi') === 'delete' ? 'selected' : '' }}>Delete (Hapus)</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-sm transition">Filter</button>
                @if(request()->hasAny(['user_id', 'aksi', 'tanggal_mulai', 'tanggal_akhir']))
                <a href="{{ route('audit-trail.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabel Audit Log --}}
    <x-responsive-table :headers="[
        ['label' => 'Waktu', 'class' => 'text-left'],
        ['label' => 'Pengguna', 'class' => 'text-left'],
        ['label' => 'Aksi', 'class' => 'text-left'],
        ['label' => 'Modul', 'class' => 'text-left'],
        ['label' => 'ID Data', 'class' => 'text-left'],
        ['label' => 'IP Address', 'class' => 'text-left hidden lg:table-cell'],
        ['label' => 'Detail', 'class' => 'text-left hidden xl:table-cell'],
    ]">
        <x-slot:desktop>
            @forelse($logs as $log)
            <tr class="hover:bg-body-bg/50 transition-colors">
                <td class="px-3 sm:px-6 py-4 text-muted text-xs whitespace-nowrap">{{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : '-' }}</td>
                <td class="px-3 sm:px-6 py-4">
                    <span class="font-semibold text-neutral-dark">{{ $log->user->name ?? 'System' }}</span>
                    <span class="text-xs text-muted ml-1">({{ $log->user->role ?? '-' }})</span>
                </td>
                <td class="px-3 sm:px-6 py-4">
                    @if($log->aksi === 'create')
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">Create</span>
                    @elseif($log->aksi === 'update')
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-xs font-semibold">Update</span>
                    @elseif($log->aksi === 'delete')
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-rose-50 text-rose-700 text-xs font-semibold">Delete</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-muted text-xs font-semibold">{{ $log->aksi }}</span>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-4 font-medium text-neutral-dark">{{ $log->model }}</td>
                <td class="px-3 sm:px-6 py-4 text-xs font-mono text-muted">{{ $log->model_id ?? '-' }}</td>
                <td class="px-3 sm:px-6 py-4 text-xs text-muted hidden lg:table-cell">{{ $log->ip_address ?? '-' }}</td>
                <td class="px-3 sm:px-6 py-4 hidden xl:table-cell">
                    @php
                        $ringkas = collect($log->data_baru ?? [])->take(5)->map(fn ($v, $k) => $k . ': ' . (is_scalar($v) ? $v : json_encode($v)))->implode('<br>');
                    @endphp
                    @if($ringkas)
                        <span class="text-xs text-muted">{!! $ringkas !!}</span>
                    @else
                        <span class="text-xs text-muted">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-3 sm:px-6 py-12 text-center text-muted">Tidak ada aktivitas tercatat.</td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($logs as $log)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-neutral-dark text-sm">{{ $log->user->name ?? 'System' }}</p>
                        <p class="text-xs text-muted">{{ $log->created_at ? $log->created_at->format('d M Y H:i:s') : '-' }}</p>
                    </div>
                    @if($log->aksi === 'create')
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">Create</span>
                    @elseif($log->aksi === 'update')
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-50 text-amber-700 text-xs font-semibold">Update</span>
                    @elseif($log->aksi === 'delete')
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-rose-50 text-rose-700 text-xs font-semibold">Delete</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-muted text-xs font-semibold">{{ $log->aksi }}</span>
                    @endif
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Modul</span>
                    <span class="font-medium text-neutral-dark">{{ $log->model }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">ID Data</span>
                    <span class="text-xs font-mono text-muted">{{ $log->model_id ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-muted">IP Address</span>
                    <span class="text-muted">{{ $log->ip_address ?? '-' }}</span>
                </div>
                @if(!empty($log->data_baru))
                <div class="rounded-lg bg-slate-50 p-3">
                    <p class="text-[10px] font-semibold text-muted uppercase tracking-wide mb-1">Detail Perubahan</p>
                    @foreach(collect($log->data_baru)->take(5) as $k => $v)
                    <p class="text-xs text-muted"><span class="font-medium text-neutral-dark">{{ $k }}</span>: {{ is_scalar($v) ? $v : json_encode($v) }}</p>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <p class="font-medium text-muted">Tidak ada aktivitas tercatat.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    @if($logs->hasPages())
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm px-3 sm:px-6 py-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
