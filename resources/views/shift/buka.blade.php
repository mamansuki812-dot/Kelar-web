@extends('layouts.app')

@section('title', 'Buka Shift Kasir')
@section('page_title', 'Buka Shift Kasir')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Buka shift kasir sebelum mulai melayani transaksi POS.</p>
        </div>
        @if(in_array(auth()->user()->role, ['kasir', 'admin']))
        <x-button variant="neutral" href="{{ route('shift.riwayat') }}">Riwayat Shift</x-button>
        @endif
    </div>

    {{-- Info Cara Kerja --}}
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-5 text-sm text-teal-900 leading-relaxed">
        <p class="font-bold font-display">Kenapa harus buka shift?</p>
        <p class="mt-1 text-teal-900/80">
            Setiap transaksi POS akan dikaitkan ke shift yang sedang berjalan. Saat menutup shift,
            sistem menghitung <strong>saldo akhir otomatis</strong> (saldo awal + total transaksi tunai)
            dan membandingkannya dengan <strong>saldo fisik</strong> yang Anda input untuk mencatat selisih kas.
        </p>
    </div>

    {{-- Form Buka Shift --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6 max-w-xl">
        <form method="POST" action="{{ route('shift.buka.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Saldo Awal Kas (Rp)</label>
                <input type="text" name="saldo_awal" id="saldo_awal" inputmode="numeric" required autofocus
                    placeholder="contoh: 100000"
                    oninput="this.value = this.value.replace(/[^\d]/g,'').slice(0,12)"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
                <p class="text-xs text-muted mt-1.5">Jumlah uang tunai yang ada di laci kas saat shift dimulai.</p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-sm transition">
                    Buka Shift Kasir
                </button>
                <a href="{{ route('dashboard') }}"
                    class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection