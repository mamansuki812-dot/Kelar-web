@extends('layouts.app')

@section('title', 'Sistem Belum Disiapkan')
@section('page_title', 'Sistem Belum Disiapkan')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="bg-surface rounded-2xl shadow-2xl border border-amber-200 max-w-md w-full p-8 text-center">
        <div class="h-14 w-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold font-display text-neutral-dark">Sistem Belum Disiapkan</h2>
        <p class="text-sm text-muted mt-3 leading-relaxed">
            Pembukuan (jurnal pembukaan) belum dibuat oleh admin/pemilik.
            Mohon hubungi <strong>admin atau pemilik</strong> untuk melakukan
            <strong>Setup Awal</strong> sebelum Anda dapat mengakses fitur.
        </p>
    </div>
</div>
@endsection