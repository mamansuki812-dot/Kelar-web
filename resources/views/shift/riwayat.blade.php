@extends('layouts.app')

@section('title', 'Riwayat Shift Kasir')
@section('page_title', 'Riwayat Shift Kasir')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">
                @if(auth()->user()->role === 'kasir')
                    Riwayat shift yang Anda buka.
                @else
                    Riwayat buka/tutup kasir seluruh kasir.
                @endif
            </p>
        </div>
        @if(auth()->user()->role === 'kasir')
        <x-button variant="success" size="lg" href="{{ route('shift.buka') }}">
            <span>Buka Shift</span>
        </x-button>
        @endif
    </div>

    {{-- Filter (khusus admin: per user; semua role: status & tanggal) --}}
    <form method="GET" action="{{ route('shift.riwayat') }}"
        class="bg-surface rounded-2xl border border-border-soft shadow-sm p-4 flex flex-col sm:flex-row gap-3 flex-wrap items-end">
        @if(auth()->user()->role !== 'kasir')
        <div class="flex items-center gap-2">
            <label class="text-sm font-semibold text-muted whitespace-nowrap">Kasir:</label>
            <select name="user_id"
                class="px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted bg-surface">
                <option value="">Semua Kasir</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="flex items-center gap-2">
            <label class="text-sm font-semibold text-muted whitespace-nowrap">Status:</label>
            <select name="status"
                class="px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted bg-surface">
                <option value="">Semua Status</option>
                <option value="buka" {{ request('status') === 'buka' ? 'selected' : '' }}>Berlangsung</option>
                <option value="tutup" {{ request('status') === 'tutup' ? 'selected' : '' }}>Ditutup</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm font-semibold text-muted whitespace-nowrap">Mulai:</label>
            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                class="px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm font-semibold text-muted whitespace-nowrap">Sampai:</label>
            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                class="px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-primary-dark text-white text-sm font-semibold rounded-xl transition">Filter</button>
            @if(request()->hasAny(['user_id', 'status', 'tanggal_mulai', 'tanggal_akhir']))
            <a href="{{ route('shift.riwayat') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted text-sm font-semibold rounded-xl transition text-center">Reset</a>
            @endif
        </div>
    </form>

    {{-- Tabel Riwayat Shift --}}
    <x-responsive-table :headers="[
        ['label' => 'Kasir', 'class' => 'text-left'],
        ['label' => 'Waktu Buka', 'class' => 'text-left'],
        ['label' => 'Saldo Awal', 'class' => 'text-right'],
        ['label' => 'Saldo Sistem', 'class' => 'text-right'],
        ['label' => 'Saldo Fisik', 'class' => 'text-right'],
        ['label' => 'Selisih', 'class' => 'text-right'],
        ['label' => 'Status', 'class' => 'text-center'],
        ['label' => 'Catatan', 'class' => 'text-left hidden lg:table-cell'],
    ]">
        <x-slot:desktop>
            @forelse($shifts as $s)
            <tr class="hover:bg-body-bg/50 transition-colors">
                <td class="px-3 sm:px-6 py-3">
                    @if(auth()->user()->role === 'kasir')
                        <span class="font-semibold text-neutral-dark">{{ $s->user->name }}</span>
                    @else
                        <span class="font-semibold text-neutral-dark">{{ $s->user->name }}</span>
                        <p class="text-xs text-muted">{{ $s->user->role }}</p>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-3 text-muted text-sm">
                    <p>{{ $s->jam_buka->format('d M Y H:i') }}</p>
                    @if($s->jam_tutup)
                    <p class="text-xs text-muted">tutup {{ $s->jam_tutup->format('d M Y H:i') }}</p>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-3 text-right text-muted text-sm">Rp {{ number_format($s->saldo_awal, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-right text-muted text-sm">{{ $s->saldo_akhir_sistem !== null ? 'Rp ' . number_format($s->saldo_akhir_sistem, 0, ',', '.') : '—' }}</td>
                <td class="px-3 sm:px-6 py-3 text-right text-muted text-sm">{{ $s->saldo_akhir_fisik !== null ? 'Rp ' . number_format($s->saldo_akhir_fisik, 0, ',', '.') : '—' }}</td>
                <td class="px-3 sm:px-6 py-3 text-right text-sm font-semibold {{ $s->selisih !== null ? ($s->selisih >= 0 ? 'text-emerald-700' : 'text-rose-600') : 'text-muted' }}">
                    {{ $s->selisih !== null ? ($s->selisih >= 0 ? '+' : '') . 'Rp ' . number_format($s->selisih, 0, ',', '.') : '—' }}
                </td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    @if($s->status === 'buka')
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Berlangsung</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-slate-100 text-muted">Ditutup</span>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-3 text-muted text-xs hidden lg:table-cell">{{ $s->catatan ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-3 sm:px-6 py-16 text-center text-muted">Belum ada data shift kasir.</td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($shifts as $s)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-neutral-dark">{{ $s->user->name }}</p>
                        <p class="text-xs text-muted">{{ $s->jam_buka->format('d M Y H:i') }}</p>
                    </div>
                    @if($s->status === 'buka')
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Berlangsung</span>
                    @else
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-slate-100 text-muted">Ditutup</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-xs text-muted">Saldo Awal</p>
                        <p class="font-medium text-muted">Rp {{ number_format($s->saldo_awal, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted">Saldo Sistem</p>
                        <p class="font-medium text-muted">{{ $s->saldo_akhir_sistem !== null ? 'Rp ' . number_format($s->saldo_akhir_sistem, 0, ',', '.') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted">Saldo Fisik</p>
                        <p class="font-medium text-muted">{{ $s->saldo_akhir_fisik !== null ? 'Rp ' . number_format($s->saldo_akhir_fisik, 0, ',', '.') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted">Selisih</p>
                        <p class="font-bold {{ $s->selisih !== null ? ($s->selisih >= 0 ? 'text-emerald-700' : 'text-rose-600') : 'text-muted' }}">
                            {{ $s->selisih !== null ? ($s->selisih >= 0 ? '+' : '') . 'Rp ' . number_format($s->selisih, 0, ',', '.') : '—' }}
                        </p>
                    </div>
                </div>
                @if($s->catatan)
                <p class="text-xs text-muted border-t border-slate-50 pt-2">Catatan: {{ $s->catatan }}</p>
                @endif
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <p class="font-medium text-muted">Belum ada data shift kasir.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    @if($shifts->hasPages())
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm px-3 sm:px-6 py-4">{{ $shifts->links() }}</div>
    @endif
</div>
@endsection