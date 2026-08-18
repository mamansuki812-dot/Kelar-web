@extends('layouts.app')

@section('title', 'Aset Tetap')
@section('page_title', 'Aset Tetap')

@section('content')
<div class="space-y-6">

    {{-- Header + Tombol Tambah --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">Kelola aset tetap (peralatan, kendaraan, dll) untuk laporan neraca.</p>
        </div>
        <x-button variant="primary" size="lg" class="w-full sm:w-auto" onclick="openModal('modalTambah')">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Tambah Aset Tetap</span>
        </x-button>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Total Harga Perolehan</p>
            <p class="text-2xl font-bold font-display text-neutral-dark mt-2">Rp {{ number_format($totalHarga, 0, ',', '.') }}</p>
        </div>
        <div class="bg-surface rounded-2xl border border-amber-100 shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Akumulasi Penyusutan</p>
            <p class="text-2xl font-bold font-display text-amber-700 mt-2">Rp {{ number_format($totalPenyusutan, 0, ',', '.') }}</p>
        </div>
        <div class="bg-surface rounded-2xl border border-teal-100 shadow-sm p-6">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Total Nilai Buku</p>
            <p class="text-2xl font-bold font-display text-teal-700 mt-2">Rp {{ number_format($totalNilaiBuku, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Filter Status --}}
    <form method="GET" action="{{ route('aset-tetap.index') }}" class="flex items-center gap-2">
        <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-border-soft rounded-xl text-xs outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-muted bg-surface">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        @if(request('status'))
        <a href="{{ route('aset-tetap.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-muted rounded-xl text-xs font-semibold">Reset</a>
        @endif
    </form>

    {{-- Tabel (Desktop) / Kartu (Mobile) --}}
    <x-responsive-table :headers="[
        ['label' => 'Aset', 'class' => 'text-left'],
        ['label' => 'Tanggal Perolehan', 'class' => 'text-left'],
        ['label' => 'Harga Perolehan', 'class' => 'text-right'],
        ['label' => 'Akumulasi', 'class' => 'text-right'],
        ['label' => 'Nilai Buku', 'class' => 'text-right'],
        ['label' => 'Status', 'class' => 'text-center'],
        ['label' => 'Aksi', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($asets as $a)
            <tr class="hover:bg-body-bg/70 transition-colors">
                <td class="px-3 sm:px-6 py-3">
                    <p class="font-semibold text-neutral-dark text-sm">{{ $a->nama_aset }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $a->kode_aset ?: '—' }} @if($a->kategori_aset) · {{ $a->kategori_aset }} @endif</p>
                </td>
                <td class="px-3 sm:px-6 py-3">
                    <p class="text-sm text-muted">{{ $a->tanggal_perolehan?->translatedFormat('d M Y') ?? '-' }}</p>
                    @if($a->masa_manfaat_bulan)
                    <p class="text-xs text-muted mt-0.5">{{ $a->masa_manfaat_bulan }} bulan</p>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-3 text-right text-sm font-medium text-neutral-dark">Rp {{ number_format($a->harga_perolehan, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-right text-sm text-amber-700">Rp {{ number_format($a->akumulasi_penyusutan, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-right text-sm font-bold text-teal-700">Rp {{ number_format($a->nilai_buku, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    @if($a->is_active)
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                    @else
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-100 text-slate-500">Nonaktif</span>
                    @endif
                </td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <button onclick="openEdit({{ $a->id }})" class="px-2.5 py-1 text-[11px] font-semibold text-primary border border-primary/30 rounded-lg hover:bg-primary/10 transition">Edit</button>
                        <form action="{{ route('aset-tetap.toggle', $a->id) }}" method="POST" onsubmit="return confirm('Ubah status aset ini?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold text-muted border border-border-soft rounded-lg hover:bg-slate-100 transition">{{ $a->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-3 sm:px-6 py-16 text-center text-muted">
                    <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="font-medium">Belum ada aset tetap.</p>
                    <p class="text-sm mt-1">Klik tombol "Tambah Aset Tetap" untuk mencatat aset pertama.</p>
                </td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($asets as $a)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-neutral-dark">{{ $a->nama_aset }}</p>
                        <p class="text-xs text-muted mt-0.5">{{ $a->tanggal_perolehan?->translatedFormat('d M Y') ?? '-' }}</p>
                    </div>
                    @if($a->is_active)
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                    @else
                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-full bg-slate-100 text-slate-500">Nonaktif</span>
                    @endif
                </div>
                <div class="grid grid-cols-3 gap-2 text-sm">
                    <div>
                        <p class="text-xs text-muted">Perolehan</p>
                        <p class="font-medium text-neutral-dark">Rp {{ number_format($a->harga_perolehan, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted">Penyusutan</p>
                        <p class="font-medium text-amber-700">Rp {{ number_format($a->akumulasi_penyusutan, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted">Nilai Buku</p>
                        <p class="font-bold text-teal-700">Rp {{ number_format($a->nilai_buku, 0, ',', '.') }}</p>
                    </div>
                </div>
                @if($a->keterangan)
                <p class="text-xs text-muted">{{ $a->keterangan }}</p>
                @endif
                <div class="flex items-center gap-2 pt-2 border-t border-slate-50">
                    <button onclick="openEdit({{ $a->id }})" class="flex-1 py-1.5 text-[11px] font-semibold text-primary border border-primary/30 rounded-lg hover:bg-primary/10 transition">Edit</button>
                    <form action="{{ route('aset-tetap.toggle', $a->id) }}" method="POST" class="flex-1">
                        @csrf
                        @method('PATCH')
                        <button type="submit" onclick="return confirm('Ubah status aset ini?')" class="w-full py-1.5 text-[11px] font-semibold text-muted border border-border-soft rounded-lg hover:bg-slate-100 transition">{{ $a->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <p class="font-medium text-muted">Belum ada aset tetap.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    {{-- Pagination --}}
    @if($asets->hasPages())
    <div>
        {{ $asets->links() }}
    </div>
    @endif
</div>

{{-- MODAL TAMBAH ASET --}}
<div id="modalTambah" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalTambah')"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-soft sticky top-0 bg-surface z-10">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Tambah Aset Tetap</h3>
            <button onclick="closeModal('modalTambah')" class="text-muted hover:text-muted">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formTambah" action="{{ route('aset-tetap.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @include('aset_tetap._form', ['aset' => null])
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeModal('modalTambah')"
                    class="px-5 py-2.5 text-sm font-semibold text-muted bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="button" onclick="submitAset('tambah')"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-primary hover:bg-primary-dark rounded-xl transition shadow-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT ASET --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal('modalEdit')"></div>
    <div class="relative bg-surface rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-border-soft sticky top-0 bg-surface z-10">
            <h3 class="text-lg font-bold font-display text-neutral-dark">Edit Aset Tetap</h3>
            <button onclick="closeModal('modalEdit')" class="text-muted hover:text-muted">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formEdit" action="" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            @method('PUT')
            @include('aset_tetap._form', ['aset' => null])
            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" onclick="closeModal('modalEdit')"
                    class="px-5 py-2.5 text-sm font-semibold text-muted bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="button" onclick="submitAset('edit')"
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
    @php
        $asetJson = collect($asets->items())->map(fn ($a) => [
            'id' => $a->id,
            'nama_aset' => $a->nama_aset,
            'kode_aset' => $a->kode_aset,
            'kategori_aset' => $a->kategori_aset,
            'tanggal_perolehan' => $a->tanggal_perolehan?->format('Y-m-d'),
            'harga_perolehan' => $a->harga_perolehan,
            'akumulasi_penyusutan' => $a->akumulasi_penyusutan,
            'nilai_residu' => $a->nilai_residu,
            'masa_manfaat_bulan' => $a->masa_manfaat_bulan,
            'keterangan' => $a->keterangan,
        ])->values()->all();
    @endphp
    const daftarAset = @json($asetJson);

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
    function openEdit(id) {
        const a = daftarAset.find(x => x.id === id);
        if (!a) return;
        const form = document.getElementById('formEdit');
        form.action = '{{ route("aset-tetap.index") }}/' + id;
        setField(form, 'nama_aset', a.nama_aset);
        setField(form, 'kode_aset', a.kode_aset || '');
        setField(form, 'kategori_aset', a.kategori_aset || '');
        setField(form, 'tanggal_perolehan', a.tanggal_perolehan || '');
        setField(form, 'harga_perolehan', a.harga_perolehan);
        setField(form, 'akumulasi_penyusutan', a.akumulasi_penyusutan);
        setField(form, 'nilai_residu', a.nilai_residu);
        setField(form, 'masa_manfaat_bulan', a.masa_manfaat_bulan || '');
        setField(form, 'keterangan', a.keterangan || '');
        openModal('modalEdit');
    }
    function setField(form, name, value) {
        const el = form.querySelector('[name="' + name + '"]');
        if (el) el.value = value;
    }
    function submitAset(mode) {
        const form = document.getElementById(mode === 'tambah' ? 'formTambah' : 'formEdit');
        const nama = form.querySelector('[name="nama_aset"]').value;
        const harga = form.querySelector('[name="harga_perolehan"]').value;
        appConfirm('Aset Tetap', 'Simpan aset "' + nama + '" dengan harga perolehan Rp ' + Number(harga || 0).toLocaleString('id-ID') + '?')
            .then(function (ok) { if (ok) form.submit(); });
    }
</script>
@endsection