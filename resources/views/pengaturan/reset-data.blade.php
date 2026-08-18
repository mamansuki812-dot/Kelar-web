@extends('layouts.app')

@section('title', 'Reset Data')
@section('page_title', 'Reset Data')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div>
        <p class="text-sm text-muted">
            Hapus <strong>seluruh data transaksional</strong> dan kembalikan stok ke nol, tanpa
            menghapus master data. Digunakan saat ingin memulai ulang pembukuan / demo dengan kondisi bersih.
        </p>
    </div>

    {{-- Ringkasan yang akan dihapus --}}
    <div class="bg-surface rounded-xl border border-border-soft shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-border-soft bg-rose-50/50">
            <h3 class="font-semibold text-neutral-dark flex items-center space-x-2">
                <svg class="h-5 w-5 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Akan DIHAPUS (transaksional)
            </h3>
        </div>
        <div class="p-6">
            <x-responsive-table :headers="[
                ['label' => 'Data', 'class' => 'text-left'],
                ['label' => 'Jumlah', 'class' => 'text-right'],
            ]">
                <x-slot:desktop>
                    @foreach($ringkasan as $label => $jumlah)
                    <tr>
                        <td class="px-6 py-2 text-sm text-neutral-dark">{{ $label }}</td>
                        <td class="px-6 py-2 text-right text-sm font-semibold text-rose-700">{{ number_format($jumlah, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </x-slot:desktop>
                <x-slot:mobile>
                    @foreach($ringkasan as $label => $jumlah)
                    <div class="flex items-center justify-between px-6 py-2">
                        <span class="text-sm text-neutral-dark">{{ $label }}</span>
                        <span class="text-sm font-semibold text-rose-700">{{ number_format($jumlah, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </x-slot:mobile>
            </x-responsive-table>
        </div>
    </div>

    {{-- Master data yang tetap --}}
    <div class="bg-white rounded-xl shadow-sm border border-emerald-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-emerald-200 bg-emerald-50/60">
            <h3 class="font-semibold text-neutral-dark flex items-center space-x-2">
                <svg class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Data yang TETAP (tidak dihapus)
            </h3>
        </div>
        <div class="p-6 text-sm text-neutral-dark leading-relaxed">
            <p>Stok seluruh produk di-set ulang menjadi <strong>0</strong>.</p>
            <ul class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-muted">
                @foreach($masterTetap as $label => $jumlah)
                <li class="flex items-center justify-between">
                    <span>{{ $label }}</span>
                    <span class="font-semibold text-neutral-dark">{{ number_format($jumlah, 0, ',', '.') }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Form konfirmasi --}}
    <div class="bg-white rounded-2xl shadow-2xl border border-rose-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-border-soft bg-rose-600 text-white">
            <h3 class="font-semibold flex items-center space-x-2">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Konfirmasi Berlapis</span>
            </h3>
        </div>
        <form action="{{ route('pengaturan.reset-data.store') }}" method="POST" class="px-6 py-5 space-y-4"
              onsubmit="return confirmReset()">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">
                    Ketik teks berikut untuk melanjutkan: <span class="font-mono text-rose-700">HAPUS SEMUA DATA</span>
                </label>
                <input type="text" name="konfirmasi_teks" value="{{ old('konfirmasi_teks') }}"
                       placeholder="HAPUS SEMUA DATA"
                       class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500 outline-none transition"
                       required autocomplete="off">
                @error('konfirmasi_teks')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Password (re-entry akun saat ini)</label>
                <input type="password" name="password" placeholder="••••••••"
                       class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-rose-500/30 focus:border-rose-500 outline-none transition"
                       required autocomplete="current-password">
                @error('password')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <a href="{{ route('dashboard') }}"
                   class="px-5 py-2.5 text-center text-sm font-semibold text-muted bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition shadow-sm">
                    Saya Mengerti — Hapus Semua Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmReset() {
        return window.confirm('PERINGATAN: Seluruh data transaksional akan dihapus permanen dan tidak dapat dikembalikan. Lanjutkan?');
    }
</script>
@endsection