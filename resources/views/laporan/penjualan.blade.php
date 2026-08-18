@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page_title', 'Laporan Penjualan')

@section('content')
<div class="space-y-6">

    {{-- Filter Penjualan --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
        <form method="GET" action="{{ route('laporan.penjualan') }}" class="flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai->format('Y-m-d') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" value="{{ $tanggalAkhir->format('Y-m-d') }}"
                    class="w-full px-4 py-2.5 border border-border-soft rounded-xl text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2.5 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl text-sm transition">
                    Filter Laporan
                </button>
                <a href="{{ route('laporan.penjualan') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-muted font-semibold rounded-xl text-sm transition text-center">
                    Bulan Ini
                </a>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Total Omzet</p>
            <p class="text-2xl font-bold font-display text-neutral-dark mt-2">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</p>
            <p class="text-xs text-muted mt-1">Penjualan kotor periode ini</p>
        </div>

        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Jumlah Transaksi</p>
            <p class="text-2xl font-bold font-display text-neutral-dark mt-2">{{ $totalTransaksi }} Transaksi</p>
            <p class="text-xs text-muted mt-1">Total nota terbit</p>
        </div>

        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Rata-rata Nota</p>
            <p class="text-2xl font-bold font-display text-neutral-dark mt-2">Rp {{ number_format($rataPerTransaksi, 0, ',', '.') }}</p>
            <p class="text-xs text-muted mt-1">Nilai belanja rata-rata</p>
        </div>

        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5">
            <p class="text-xs font-semibold text-muted uppercase tracking-wider">Potongan Diskon</p>
            <p class="text-2xl font-bold font-display text-rose-700 mt-2">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</p>
            <p class="text-xs text-muted mt-1">Total diskon yang diberikan</p>
        </div>
    </div>

    {{-- Charts and Top Selling --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Grafik Penjualan Harian --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5 lg:col-span-2 flex flex-col justify-between">
            <div>
                <h3 class="font-bold font-display text-neutral-dark text-lg">Tren Omzet Harian</h3>
                <p class="text-xs text-muted mt-0.5">Grafik fluktuasi penjualan harian.</p>
            </div>
            <div class="mt-4 h-64 relative">
                <canvas id="trenOmzetChart"></canvas>
            </div>
        </div>

        {{-- 10 Produk Terlaris --}}
        <div class="bg-surface rounded-2xl border border-border-soft shadow-sm p-5 flex flex-col">
            <div>
                <h3 class="font-bold font-display text-neutral-dark text-lg">10 Produk Terlaris</h3>
                <p class="text-xs text-muted mt-0.5">Paling banyak terjual di periode ini.</p>
            </div>
            <div class="mt-4 flex-1 overflow-y-auto max-h-[17.5rem] divide-y divide-slate-50 pr-1">
                @forelse($produkTerlaris as $i => $pt)
                <div class="flex items-center justify-between py-2.5">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-neutral-dark truncate">{{ $pt->nama_produk }}</p>
                        <p class="text-xs font-mono text-muted">{{ $pt->kode_produk }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-teal-50 text-teal-700 text-xs font-semibold">
                            {{ $pt->total_qty }} pcs
                        </span>
                        <p class="text-xs font-semibold text-muted mt-0.5">Rp {{ number_format($pt->total_subtotal, 0, ',', '.') }}</p>
                    </div>
                </div>
                @empty
                <div class="py-12 text-center text-muted">
                    <p class="text-sm">Belum ada data produk terjual.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Detail Tabel Transaksi --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <h3 class="font-bold font-display text-neutral-dark text-lg">Detail Transaksi Periode Ini</h3>
        <div class="flex gap-2">
            <x-button variant="danger" href="{{ route('laporan.export', 'penjualan') }}?tanggal_mulai={{ $tanggalMulai->format('Y-m-d') }}&tanggal_akhir={{ $tanggalAkhir->format('Y-m-d') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </x-button>
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-border-soft hover:bg-body-bg rounded-lg text-xs font-semibold text-muted transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-5a2 2 0 00-2-2H5a2 2 0 00-2 2v5a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
        </div>
    </div>

    <x-responsive-table :headers="[
        ['label' => 'Waktu Transaksi', 'class' => 'text-left'],
        ['label' => 'Kode Nota', 'class' => 'text-left'],
        ['label' => 'Kasir', 'class' => 'text-left'],
        ['label' => 'Diskon', 'class' => 'text-right'],
        ['label' => 'Total', 'class' => 'text-right'],
        ['label' => 'Aksi', 'class' => 'text-center'],
    ]">
        <x-slot:desktop>
            @forelse($transaksis as $trx)
            <tr class="hover:bg-body-bg/50 transition-colors">
                <td class="px-3 sm:px-6 py-4 text-muted text-xs sm:text-sm whitespace-nowrap">{{ $trx->tanggal_transaksi->format('d M Y H:i') }}</td>
                <td class="px-3 sm:px-6 py-4 font-semibold text-neutral-dark text-sm">{{ $trx->kode_transaksi }}</td>
                <td class="px-3 sm:px-6 py-4 text-muted">{{ $trx->user->name ?? '-' }}</td>
                <td class="px-3 sm:px-6 py-4 text-right text-rose-700 font-medium text-sm">Rp {{ number_format($trx->diskon, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-4 text-right font-bold text-neutral-dark text-sm">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                <td class="px-3 sm:px-6 py-4 text-center">
                    <a href="{{ route('transaksi.show', $trx->id) }}" class="text-xs font-semibold text-primary hover:text-primary-dark bg-teal-50 hover:bg-teal-100 px-2 sm:px-2.5 py-1.5 rounded-lg transition">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-3 sm:px-6 py-12 text-center text-muted">Tidak ada transaksi terdaftar untuk periode ini.</td>
            </tr>
            @endforelse
        </x-slot:desktop>

        <x-slot:mobile>
            @forelse($transaksis as $trx)
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-neutral-dark text-sm">{{ $trx->kode_transaksi }}</p>
                        <p class="text-xs text-muted mt-0.5">{{ $trx->tanggal_transaksi->format('d M Y H:i') }}</p>
                    </div>
                    <a href="{{ route('transaksi.show', $trx->id) }}"
                       class="text-xs font-semibold text-primary bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-lg transition">Detail</a>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Kasir</span>
                    <span class="font-medium text-neutral-dark">{{ $trx->user->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted">Diskon</span>
                    <span class="text-rose-700 font-medium">Rp {{ number_format($trx->diskon, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-slate-50">
                    <span class="text-muted font-medium">Total</span>
                    <span class="font-bold text-neutral-dark">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</span>
                </div>
            </div>
            @empty
            <div class="bg-surface rounded-xl border border-border-soft shadow-sm p-8 text-center">
                <p class="font-medium text-muted">Tidak ada transaksi terdaftar untuk periode ini.</p>
            </div>
            @endforelse
        </x-slot:mobile>
    </x-responsive-table>

    @if($transaksis->hasPages())
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm px-3 sm:px-6 py-4">{{ $transaksis->links() }}</div>
    @endif

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('trenOmzetChart').getContext('2d');
        
        const labels = {!! json_encode($grafikHarian->pluck('tanggal')) !!};
        const data = {!! json_encode($grafikHarian->pluck('total')) !!};
        
        // Format labels agar lebih pendek/cantik (mis. '15 Jul')
        const formattedLabels = labels.map(label => {
            const d = new Date(label);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: formattedLabels,
                datasets: [{
                    label: 'Omzet Penjualan (Rp)',
                    data: data,
                    borderColor: '#D97207',
                    backgroundColor: (function() {
                        const g = ctx.createLinearGradient(0, 0, 0, 400);
                        g.addColorStop(0, 'rgba(250, 143, 32, 0.25)');
                        g.addColorStop(1, 'rgba(250, 234, 140, 0.05)');
                        return g;
                    })(),
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#D97207',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Omzet: Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000) + 'jt';
                                } else if (value >= 1000) {
                                    return (value / 1000) + 'rb';
                                }
                                return value;
                            },
                            font: { size: 10 }
                        },
                        grid: {
                            color: '#e2e8f0'
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 10 }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
