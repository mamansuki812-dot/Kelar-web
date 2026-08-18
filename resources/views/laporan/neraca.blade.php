@extends('layouts.app')

@section('title', 'Laporan Neraca')
@section('page_title', 'Laporan Neraca Keuangan')

@section('content')
<div class="space-y-6">

    {{-- Filter Tanggal Neraca --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
        <form method="GET" action="{{ route('laporan.neraca') }}" class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Posisi Keuangan Per Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-sm transition">
                    Tampilkan Neraca
                </button>
                <a href="{{ route('laporan.neraca') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition text-center">
                    Hari Ini
                </a>
                <x-button variant="danger" href="{{ route('laporan.export', 'neraca') }}?tanggal={{ $tanggal->format('Y-m-d') }}">
                    Export PDF
                </x-button>
            </div>
        </form>
    </div>

    {{-- Neraca Layout (Dual Columns) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- AKTIVA (Aset) --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm flex flex-col justify-between overflow-hidden">
            <div>
                <div class="bg-body-bg px-6 py-4 border-b border-border-soft">
                    <h3 class="font-bold font-display text-neutral-dark text-lg">AKTIVA (Aset)</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-muted uppercase tracking-wider mb-3">Aset Lancar</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-muted">Kas (Saldo Jurnal)</span>
                                <span class="font-semibold text-neutral-dark">Rp {{ number_format($kasEstimasi, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-muted">Bank (Saldo Jurnal)</span>
                                <span class="font-semibold text-neutral-dark">Rp {{ number_format($bank, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-muted">Persediaan Barang (Saldo Jurnal)</span>
                                <span class="font-semibold text-neutral-dark">Rp {{ number_format($nilaiStok, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-semibold text-neutral-dark pt-2 border-t border-border-soft">
                                <span>Jumlah Aset Lancar</span>
                                <span>Rp {{ number_format($totalAsetLancar, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-muted uppercase tracking-wider mb-3">Aset Tetap</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-muted">Aset Tetap (Nilai Buku per Jurnal)</span>
                                <span class="font-semibold text-neutral-dark">Rp {{ number_format($asetTetap, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-semibold text-neutral-dark pt-2 border-t border-border-soft">
                                <span>Jumlah Aset Tetap</span>
                                <span>Rp {{ number_format($asetTetap, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-teal-50/50 px-6 py-4 border-t border-teal-100/50 flex justify-between items-center">
                <span class="font-bold text-teal-900 text-sm">TOTAL AKTIVA (ASET)</span>
                <span class="font-extrabold text-teal-700 text-lg">Rp {{ number_format($totalAset, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- PASIVA (Ekuitas & Liabilitas) --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm flex flex-col justify-between overflow-hidden">
            <div>
                <div class="bg-body-bg px-6 py-4 border-b border-border-soft">
                    <h3 class="font-bold font-display text-neutral-dark text-lg">PASIVA (Ekuitas & Liabilitas)</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-muted uppercase tracking-wider mb-3">Liabilitas (Kewajiban)</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-muted">Utang Usaha / Dagang</span>
                                <span class="font-semibold text-neutral-dark">Rp {{ number_format($totalLiabilitas, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-muted uppercase tracking-wider mb-3">Ekuitas (Modal)</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-muted">Modal Disetor & Saldo Laba</span>
                                <span class="font-semibold text-neutral-dark">Rp {{ number_format($totalEkuitas, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-teal-50/50 px-6 py-4 border-t border-teal-100/50 flex justify-between items-center">
                <span class="font-bold text-teal-900 text-sm">TOTAL PASIVA (KEWAJIBAN & MODAL)</span>
                <span class="font-extrabold text-teal-700 text-lg">Rp {{ number_format($totalPasiva, 0, ',', '.') }}</span>
            </div>
        </div>

    </div>

    {{-- Note SAK EMKM --}}
    <div class="bg-body-bg border border-border-soft rounded-xl p-4 text-xs text-muted leading-relaxed">
        <strong>Catatan Laporan:</strong> Laporan Neraca Keuangan di atas disajikan secara ringkas berdasarkan standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK EMKM). Saldo setiap akun dihitung dari buku jurnal per tanggal posisi (debit/kredit), sehingga nilai persediaan mengikuti mutasi stok yang dijurnal, bukan nilai stok aktif hari ini. Aset tetap dicatat sebesar nilai buku (harga perolehan dikurangi akumulasi penyusutan) melalui jurnal perolehan terhadap Modal Pemilik. Ekuitas mencakup modal disetor yang dicatat pada pembukaan serta laba kumulatif.
        <br>
        @if ($seimbang)
            Status: <strong class="text-teal-700">Neraca seimbang</strong> (Aset = Liabilitas + Ekuitas, selisih Rp 0).
        @else
            Status: <strong class="text-red-600">Neraca tidak seimbang</strong> — selisih Aset terhadap (Liabilitas + Ekuitas) sebesar Rp {{ number_format(abs($selisihNeraca), 0, ',', '.') }}. Periksa jurnal yang belum tercatat.
        @endif
    </div>

</div>
@endsection
