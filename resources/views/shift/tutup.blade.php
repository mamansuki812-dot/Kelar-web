@extends('layouts.app')

@section('title', 'Tutup Shift Kasir')
@section('page_title', 'Tutup Shift Kasir')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">
                @if(!empty($sudahTutup))
                    Shift terakhir Anda sudah ditutup.
                @else
                    Tutup shift setelah selesai bertugas &amp; hitung uang fisik di laci kas.
                @endif
            </p>
        </div>
        @if(in_array(auth()->user()->role, ['kasir', 'admin']))
        <x-button variant="neutral" href="{{ route('shift.riwayat') }}">Riwayat Shift</x-button>
        @endif
    </div>

    @if(!empty($sudahTutup))
        {{-- Sesi shift terakhir sudah tutup — tampilkan ringkasan --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="text-xs text-muted uppercase tracking-wider">Shift Terakhir Ditutup</p>
                    <p class="text-xl font-bold font-display text-neutral-dark mt-1">
                        {{ $shift->jam_buka->format('d M Y H:i') }} – {{ $shift->jam_tutup->format('H:i') }}
                    </p>
                    <p class="text-xs text-muted mt-1">Saldo awal Rp {{ number_format($shift->saldo_awal, 0, ',', '.') }}</p>
                </div>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-body-bg rounded-xl px-4 py-3">
                        <p class="text-xs text-muted">Saldo Sistem</p>
                        <p class="font-bold text-neutral-dark mt-0.5">Rp {{ number_format($shift->saldo_akhir_sistem, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-body-bg rounded-xl px-4 py-3">
                        <p class="text-xs text-muted">Saldo Fisik</p>
                        <p class="font-bold text-neutral-dark mt-0.5">Rp {{ number_format($shift->saldo_akhir_fisik, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-body-bg rounded-xl px-4 py-3">
                        <p class="text-xs text-muted">Selisih</p>
                        <p class="font-bold {{ $shift->selisih >= 0 ? 'text-emerald-700' : 'text-rose-600' }} mt-0.5">
                            {{ $shift->selisih >= 0 ? '+' : '' }}Rp {{ number_format($shift->selisih, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex gap-3">
                <x-button variant="success" href="{{ route('shift.buka') }}">Buka Shift Baru</x-button>
            </div>
        </div>
    @else
        {{-- Shift aktif — ringkasan & form tutup --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6">
            <p class="text-xs text-muted uppercase tracking-wider mb-3">Shift Aktif Anda</p>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="font-semibold text-neutral-dark">{{ $shift->user->name }}</p>
                    <p class="text-sm text-muted">Dibuka {{ $shift->jam_buka->format('d M Y H:i') }}</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Shift Berlangsung</span>
            </div>
        </div>

        {{-- Ringkasan transaksi selama shift --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Total Transaksi</p>
                <p class="text-2xl font-bold font-display text-neutral-dark mt-2">{{ $ringkasan['total_transaksi'] }} Trx</p>
                <p class="text-xs text-muted mt-1">Selama shift berjalan</p>
            </div>
            <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Omzet Tunai</p>
                <p class="text-2xl font-bold font-display text-teal-700 mt-2">Rp {{ number_format($ringkasan['total_tunai'], 0, ',', '.') }}</p>
                <p class="text-xs text-muted mt-1">{{ $ringkasan['jumlah_tunai'] }} transaksi tunai</p>
            </div>
            <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Omzet Non-Tunai</p>
                <p class="text-2xl font-bold font-display text-neutral-dark mt-2">Rp {{ number_format($ringkasan['total_non_tunai'], 0, ',', '.') }}</p>
                <p class="text-xs text-muted mt-1">{{ $ringkasan['jumlah_non_tunai'] }} transaksi non-tunai</p>
            </div>
            <div class="bg-surface rounded-2xl border border-emerald-100 shadow-sm p-5">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider">Saldo Sistem</p>
                <p class="text-2xl font-bold font-display text-emerald-700 mt-2">Rp {{ number_format($shift->saldo_awal + $ringkasan['total_tunai'], 0, ',', '.') }}</p>
                <p class="text-xs text-muted mt-1">Saldo awal + omzet tunai</p>
            </div>
        </div>

        {{-- Form Tutup Shift --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-4">Input Penutupan Shift</p>
            <form method="POST" action="{{ route('shift.tutup.store') }}" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Saldo Akhir Fisik (Rp) *</label>
                        <input type="text" name="saldo_akhir_fisik" id="saldo_akhir_fisik" inputmode="numeric" required
                            placeholder="jumlah uang fisik di laci kas"
                            oninput="this.value = this.value.replace(/[^\d]/g,'').slice(0,12)"
                            class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Catatan (opsional)</label>
                        <input type="text" name="catatan" maxlength="500"
                            placeholder="contoh: kurang langkah, uang sobek, dll"
                            class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 leading-relaxed">
                    Selisih = <strong>saldo fisik − saldo sistem</strong>. Selisih <strong>negatif</strong> menandakan kas kurang,
                    selisih <strong>positif</strong> menandakan kas lebih. Sistem hanya mencatat selisih, tidak mengubah jurnal otomatis.
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 bg-rose-700 hover:bg-rose-800 text-white font-semibold rounded-xl text-sm transition">
                        Tutup Shift Sekarang
                    </button>
                    <a href="{{ route('pos.index') }}"
                        class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition text-center">
                        Kembali ke POS
                    </a>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection