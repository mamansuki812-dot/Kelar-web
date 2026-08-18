@extends('layouts.app')

@section('title', 'Beban Operasional')
@section('page_title', 'Beban Operasional')

@section('content')
<div class="space-y-6">

    {{-- Header + Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Catat beban operasional harian (listrik, gaji, sewa, dll).</p>
        </div>
        <x-button variant="primary" size="lg" class="w-full sm:w-auto" onclick="openModal('modalTambah')">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Catat Beban</span>
        </x-button>
    </div>

    {{-- Tabel (Desktop) / Kartu (Mobile) --}}
    <x-responsive-table :headers="[
        ['label' => 'Tanggal', 'class' => 'text-left'],
        ['label' => 'Akun Beban', 'class' => 'text-left'],
        ['label' => 'Nominal', 'class' => 'text-right'],
        ['label' => 'Keterangan', 'class' => 'text-left'],
        ['label' => 'Dicatat Oleh', 'class' => 'text-left'],
    ]">
        <x-slot:desktop>
            @forelse($beBans as $b)
            <tr class="hover:bg-body-bg/70 transition-colors">
                <td class="px-3 sm:px-6 py-3">
                    <p class="font-semibold text-neutral-dark text-sm">{{ $b->tanggal->translatedFormat('d M Y') }}</p>
                </td>
                <td class="px-3 sm:px-6 py-3">
                    <p class="font-medium text-neutral-dark text-sm">{{ $b->akun?->nama_akun ?? '-' }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $b->akun?->kode_akun }}</p>
                </td>
                <td class="px-3 sm:px-6 py-3 text-right">
                    <span class="font-bold text-rose-700 text-sm">Rp {{ number_format($b->nominal, 0, ',', '.') }}</span>
                </td>
                <td class="px-3 sm:px-6 py-3 text-sm text-muted max-w-[240px]">
                    <span class="truncate block">{{ $b->keterangan ?: '-' }}</span>
                </td>
                <td class="px-3 sm:px-6 py-3 text-sm text-muted">{{ $b->user?->name ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-3 sm:px-6 py-16 text-center text-muted">
                    <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="font-medium">Belum ada beban operasional.</p>
                    <p class="text-sm mt-1">Klik tombol "Catat Beban" untuk mencatat pengeluaran pertama.</p>
                </td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($beBans as $b)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted font-medium">{{ $b->tanggal->translatedFormat('d M Y') }}</span>
                    <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700">Rp {{ number_format($b->nominal, 0, ',', '.') }}</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-neutral-dark">{{ $b->akun?->nama_akun ?? '-' }}</p>
                    @if($b->keterangan)
                    <p class="text-xs text-muted mt-1">{{ $b->keterangan }}</p>
                    @endif
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                    <span class="text-xs text-muted font-medium">Dicatat oleh</span>
                    <span class="text-xs text-muted">{{ $b->user?->name ?? '-' }}</span>
                </div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <p class="font-medium text-muted">Belum ada beban operasional.</p>
                <p class="text-sm text-muted mt-1">Klik tombol "Catat Beban" untuk memulai.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    {{-- Pagination --}}
    @if($beBans->hasPages())
    <div class="px-3 sm:px-6 py-4 border-t border-border-soft bg-body-bg/50">
        {{ $beBans->links() }}
    </div>
    @endif
</div>

{{-- MODAL TAMBAH BEBAN --}}
<div id="modalTambah" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalTambah')"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-soft sticky top-0 bg-surface z-10">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Catat Beban Operasional</h3>
            <button onclick="closeModal('modalTambah')" class="text-muted hover:text-muted">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formBeban" action="{{ route('beban.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Tanggal <span class="text-rose-700">*</span></label>
                <input type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    required>
                @error('tanggal')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Akun Beban <span class="text-rose-700">*</span></label>
                <select name="akun_id"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    required>
                    <option value="">-- Pilih Akun Beban --</option>
                    @foreach($akuns as $akun)
                    <option value="{{ $akun->id }}" @selected(old('akun_id') == $akun->id)>
                        {{ $akun->kode_akun }} — {{ $akun->nama_akun }}
                    </option>
                    @endforeach
                </select>
                @error('akun_id')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Nominal (Rp) <span class="text-rose-700">*</span></label>
                <input type="number" name="nominal" value="{{ old('nominal') }}" min="0" step="0.01"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="cth. 50000" required>
                @error('nominal')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-dark mb-1.5">Keterangan</label>
                <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                    placeholder="cth. Pembayaran listrik bulanan (opsional)" maxlength="255">
                @error('keterangan')<p class="text-rose-700 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeModal('modalTambah')"
                    class="px-5 py-2.5 text-sm font-semibold text-muted bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="button" onclick="submitBeban()"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Buka modal jika ada error validasi dari server --}}
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
    function submitBeban() {
        const nominal = document.querySelector('input[name="nominal"]').value;
        const akun = document.querySelector('select[name="akun_id"]');
        const namaAkun = akun.options[akun.selectedIndex] ? akun.options[akun.selectedIndex].text : '';
        appConfirm('Catat Beban', 'Yakin ingin mencatat beban sebesar Rp ' + Number(nominal).toLocaleString('id-ID') + ' (' + namaAkun + ')?')
            .then(function (ok) { if (ok) document.getElementById('formBeban').submit(); });
    }
</script>
@endsection
