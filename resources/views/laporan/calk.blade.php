@extends('layouts.app')

@section('title', 'Catatan atas Laporan Keuangan')
@section('page_title', 'Catatan atas Laporan Keuangan (CaLK)')

@section('content')
<div class="space-y-6">

    {{-- Filter Tanggal --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
        <form method="GET" action="{{ route('laporan.calk') }}" class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Posisi Keuangan Per Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-sm transition">
                    Tampilkan Catatan
                </button>
                <a href="{{ route('laporan.calk') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition text-center">
                    Hari Ini
                </a>
                <x-button variant="danger" href="{{ route('laporan.export', 'calk') }}?tanggal={{ $tanggal->format('Y-m-d') }}">
                    Export PDF
                </x-button>
            </div>
        </form>
    </div>

    {{-- a. Pernyataan Kepatuhan --}}
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-6 flex items-start space-x-4">
        <svg class="h-7 w-7 text-teal-700 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="font-bold font-display text-teal-900">Pernyataan Kepatuhan</p>
            <p class="text-sm text-teal-900/80 mt-1 leading-relaxed">
                Laporan keuangan ini disusun sesuai dengan Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK EMKM).
            </p>
        </div>
    </div>

    {{-- b. Ikhtisar Kebijakan Akuntansi --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm overflow-hidden">
        <div class="bg-body-bg px-6 py-4 border-b border-border-soft">
            <h3 class="font-bold font-display text-neutral-dark text-lg">Ikhtisar Kebijakan Akuntansi</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-bold text-muted uppercase tracking-wider mb-2">Dasar Pengukuran</p>
                <p class="text-sm text-neutral-dark">Biaya historis (historical cost), bukan nilai wajar.</p>
            </div>
            <div>
                <p class="text-xs font-bold text-muted uppercase tracking-wider mb-2">Dasar Pengakuan</p>
                <p class="text-sm text-neutral-dark leading-relaxed">
                    Basis akrual: pendapatan dan beban diakui saat transaksi terjadi — HPP dipadankan (matched) dengan pendapatan pada saat penjualan, dan utang usaha diakui pada saat barang diterima (bukan saat pembayaran).
                </p>
                <p class="text-xs text-muted mt-1">Catatan: beban operasional masih diakui pada saat pembayaran; akun akrual/prepaid belum diterapkan.</p>
            </div>
            <div>
                <p class="text-xs font-bold text-muted uppercase tracking-wider mb-2">Penilaian Persediaan</p>
                <p class="text-sm text-neutral-dark leading-relaxed">
                    Biaya perolehan berdasarkan harga beli per produk (harga beli terakhir yang tercatat pada master produk, dikali stok aktif). Metode ini bukan FIFO/MPKP atau biaya rata-rata  pelacakan lot (lot costing) belum diterapkan.
                </p>
            </div>
            <div>
                <p class="text-xs font-bold text-muted uppercase tracking-wider mb-2">Penyajian</p>
                <p class="text-sm text-neutral-dark">Mata uang fungsional dan penyajian adalah Rupiah (Rp). Laporan disajikan secara ringkas sesuai ketentuan SAK EMKM.</p>
            </div>
        </div>
    </div>

    {{-- b.1 Dasar Penyusunan / Referensi --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm overflow-hidden">
        <div class="bg-body-bg px-6 py-4 border-b border-border-soft">
            <h3 class="font-bold font-display text-neutral-dark text-lg">Dasar Penyusunan &amp; Referensi Standar</h3>
        </div>
        <div class="p-6 text-sm text-neutral-dark leading-relaxed space-y-3">
            <p>
                Laporan keuangan ini disusun mengacu pada <strong>Standar Akuntansi Keuangan Entitas Mikro, Kecil,
                dan Menengah (SAK EMKM)</strong> yang diterbitkan oleh Dewan Standar Akuntansi Keuangan
                Ikatan Akuntan Indonesia (DSAK IAI), berlaku efektif per 1 Januari 2018. Sesuai SAK EMKM,
                laporan keuangan entitas terdiri atas Laporan Posisi Keuangan, Laporan Laba Rugi, dan
                Catatan atas Laporan Keuangan (CaLK) ini.
            </p>
            <p class="text-xs text-muted">
                Referensi: DSAK IAI — SAK EMKM. Situs resmi IAI:
                <a href="https://web.iaiglobal.or.id/SAK-IAI/Tentang%20SAK%20EMKM" target="_blank" rel="noopener"
                   class="text-primary underline hover:text-primary-dark">
                    web.iaiglobal.or.id/SAK-IAI/Tentang%20SAK%20EMKM
                </a>
            </p>
        </div>
    </div>

    {{-- Catatan Cutover & Saldo Pembukaan 
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm overflow-hidden">
        <div class="bg-body-bg px-6 py-4 border-b border-border-soft">
            <h3 class="font-bold font-display text-neutral-dark text-lg">Catatan Kebijakan Cutover & Saldo Pembukaan</h3>
        </div>
        <div class="p-6 text-sm text-neutral-dark space-y-3 leading-relaxed">
            <p>1. Pembukuan formal double-entry dimulai pada <strong>tanggal 05-08-2026</strong> (tanggal cutover). Seluruh data uji/testing yang timbul sebelum tanggal tersebut telah dibersihkan dan bukan merupakan bagian dari saldo pembukaan riil.</p>
            <p>2. Modal awal dihitung dari saldo persediaan hasil valuasi stok fisik: <strong>Rp {{ number_format($totalEkuitas > 0 && $kasEstimasi == 0 ? $totalEkuitas : 150000, 0, ',', '.') }}</strong> (5 produk aktif × 20 pcs × harga beli per produk). Kas awal <strong>Rp 0 — ESTIMASI SEMENTARA</strong> (belum dihitung fisik; akan dikoreksi dengan entry terpisah Debit Kas / Kredit Modal Pemilik pada tanggal koreksi).</p>
            <p>3. Dua transaksi lama (TRX-20260715-00001 dan TRX-20260715-00002, Juli 2026) yang teridentifikasi sejak sebelum fase testing dimulai tidak memiliki jurnal individual. Sesuai kebijakan cutover, transaksi tersebut <strong>tidak di-backfill</strong>; dampaknya dianggap sudah tercermin dalam saldo pembukaan.</p>
        </div>
    </div>--}}

    {{-- c. Rincian Posisi Periode Berjalan (reuse data neraca & laba rugi) --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm overflow-hidden">
        <div class="bg-body-bg px-6 py-4 border-b border-border-soft">
            <h3 class="font-bold font-display text-neutral-dark text-lg">Rincian Posisi Periode Berjalan</h3>
        </div>
        <div class="p-6 space-y-8">
            {{-- Posisi Keuangan (Neraca) --}}
            <div>
                <p class="text-xs font-bold text-muted uppercase tracking-wider mb-3">Posisi Keuangan per {{ $tanggal->format('d M Y') }}</p>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div class="bg-body-bg border border-border-soft rounded-xl p-4">
                        <p class="text-xs text-muted">Kas</p>
                        <p class="text-xl font-bold font-display text-neutral-dark mt-1">Rp {{ number_format($kasEstimasi, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-body-bg border border-border-soft rounded-xl p-4">
                        <p class="text-xs text-muted">Persediaan Barang</p>
                        <p class="text-xl font-bold font-display text-neutral-dark mt-1">Rp {{ number_format($nilaiStok, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-body-bg border border-border-soft rounded-xl p-4">
                        <p class="text-xs text-muted">Total Aset</p>
                        <p class="text-xl font-bold font-display text-teal-700 mt-1">Rp {{ number_format($totalAset, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-body-bg border border-border-soft rounded-xl p-4">
                        <p class="text-xs text-muted">Liabilitas</p>
                        <p class="text-xl font-bold font-display text-neutral-dark mt-1">Rp {{ number_format($totalLiabilitas, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-body-bg border border-border-soft rounded-xl p-4">
                        <p class="text-xs text-muted">Ekuitas</p>
                        <p class="text-xl font-bold font-display text-neutral-dark mt-1">Rp {{ number_format($totalEkuitas, 0, ',', '.') }}</p>
                    </div>
                </div>
                @if ($seimbang)
                    <p class="text-xs text-teal-700 mt-2">Status: Neraca seimbang (Aset = Liabilitas + Ekuitas).</p>
                @else
                    <p class="text-xs text-red-600 mt-2">Status: Neraca TIDAK seimbang — selisih Rp {{ number_format(abs($selisihNeraca), 0, ',', '.') }}.</p>
                @endif
            </div>

            {{-- Laba Rugi (LabaRugi) --}}
            <div>
                <p class="text-xs font-bold text-muted uppercase tracking-wider mb-3">Laba Rugi {{ $awalBulan->format('M Y') }} ({{ $awalBulan->format('d M Y') }} – {{ $akhirBulan->format('d M Y') }})</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-body-bg border border-border-soft rounded-xl p-4">
                        <p class="text-xs text-muted">Pendapatan Penjualan (Neto)</p>
                        <p class="text-xl font-bold font-display text-emerald-700 mt-1">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</p>
                        <p class="text-xs text-muted mt-1">Diskon Rp {{ number_format($totalDiskon, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-body-bg border border-border-soft rounded-xl p-4">
                        <p class="text-xs text-muted">HPP</p>
                        <p class="text-xl font-bold font-display text-rose-700 mt-1">Rp {{ number_format($hpp, 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-body-bg border border-border-soft rounded-xl p-4">
                        <p class="text-xs text-muted">Laba Kotor</p>
                        <p class="text-xl font-bold font-display text-neutral-dark mt-1">Rp {{ number_format($labaKotor, 0, ',', '.') }}</p>
                        <p class="text-xs text-muted mt-1">Margin {{ $marginPersen }}%</p>
                    </div>
                    <div class="bg-body-bg border border-border-soft rounded-xl p-4">
                        <p class="text-xs text-muted">Beban Operasional</p>
                        <p class="text-xl font-bold font-display text-neutral-dark mt-1">Rp {{ number_format($totalBeban, 0, ',', '.') }}</p>
                        <p class="text-xs text-muted mt-1">Laba Bersih: <strong class="text-{{ $labaBersih >= 0 ? 'emerald' : 'rose' }}-700">Rp {{ number_format($labaBersih, 0, ',', '.') }}</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown Kategori (dari Laba Rugi) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="font-bold font-display text-neutral-dark text-lg">Laba Rugi per Kategori ({{ $awalBulan->format('M Y') }})</h3>
    </div>

    <x-responsive-table :headers="[
        ['label' => 'Kategori', 'class' => 'text-left'],
        ['label' => 'Terjual', 'class' => 'text-right'],
        ['label' => 'Pendapatan', 'class' => 'text-right'],
        ['label' => 'HPP', 'class' => 'text-right'],
        ['label' => 'Laba', 'class' => 'text-right'],
        ['label' => 'Margin', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($kategoriBreakdown as $row)
            <tr class="hover:bg-body-bg/50 transition-colors">
                <td class="px-3 sm:px-6 py-3 font-semibold text-neutral-dark text-sm">{{ $row->nama_kategori }}</td>
                <td class="px-3 sm:px-6 py-3 text-right text-muted text-sm">{{ $row->total_qty }} pcs</td>
                <td class="px-3 sm:px-6 py-3 text-right font-medium text-neutral-dark text-sm">Rp {{ number_format($row->total_penjualan, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-right text-rose-700 text-sm">Rp {{ number_format($row->total_hpp, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-right font-bold text-emerald-700 text-sm">Rp {{ number_format($row->laba, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-3 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">{{ $row->margin }}%</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-3 sm:px-6 py-12 text-center text-muted">Tidak ada penjualan pada kategori mana pun di bulan ini.</td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($kategoriBreakdown as $row)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <p class="font-semibold text-neutral-dark text-sm">{{ $row->nama_kategori }}</p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Terjual</span>
                    <span class="text-muted">{{ $row->total_qty }} pcs</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Pendapatan</span>
                    <span class="font-medium text-neutral-dark">Rp {{ number_format($row->total_penjualan, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">HPP</span>
                    <span class="text-rose-700">Rp {{ number_format($row->total_hpp, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm pt-2 border-t border-slate-50">
                    <span class="text-muted font-medium">Laba</span>
                    <span class="font-bold text-emerald-700">Rp {{ number_format($row->laba, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Margin</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">{{ $row->margin }}%</span>
                </div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <svg class="h-10 w-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="font-medium text-muted">Tidak ada penjualan pada kategori mana pun di bulan ini.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>
</div>
@endsection
