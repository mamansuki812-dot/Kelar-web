@extends('layouts.app')

@section('title', 'Setup Awal')
@section('page_title', 'Setup Awal (Onboarding)')

@section('content')
<div class="space-y-6 max-w-2xl">
    <div>
        <p class="text-sm text-muted">
            Sistem belum memiliki jurnal pembukaan. Isi <strong>kas awal</strong> dan
            <strong>tanggal mulai pembukuan</strong> untuk menghasilkan jurnal pembukaan modal
            (Debit Persediaan / Kredit Modal Pemilik, plus Kas bila diisi).
        </p>
    </div>

    @if($sudahAdaJurnal)
        <div class="bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded-lg text-sm">
            Tabel jurnal sudah berisi data. Setup awal hanya boleh dilakukan saat jurnal pembukaan
            belum ada — hubungi admin/pemilik jika perlu reset.
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-2xl border border-primary/20 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-soft bg-primary/5">
            <h3 class="font-semibold text-neutral-dark flex items-center space-x-2">
                <svg class="h-5 w-5 text-primary-dark" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Jurnal Pembukaan Modal</span>
            </h3>
        </div>
        <form action="{{ route('pengaturan.setup-awal.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Kas Awal (Rp) <span class="text-rose-700">*</span></label>
                <input type="number" name="kas_awal" value="{{ old('kas_awal', 0) }}" min="0" step="0.01"
                       class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                       placeholder="cth. 500000" required>
                <p class="text-xs text-muted mt-1">0 = kas belum dicatat, hanya persediaan yang di-masukkan.</p>
                @error('kas_awal')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Tanggal Mulai (Cutover) <span class="text-rose-700">*</span></label>
                <input type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                       required>
                @error('tanggal')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl transition shadow-sm">
                    Simpan Setup Awal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection