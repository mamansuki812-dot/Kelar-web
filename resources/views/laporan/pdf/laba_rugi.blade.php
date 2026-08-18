<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; color: #666; margin-top: 0; }
        .meta { font-size: 11px; color: #888; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 11px; }
        th { background: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { display: flex; gap: 20px; margin-bottom: 16px; }
        .summary-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; }
        .summary-box .label { font-size: 10px; color: #888; text-transform: uppercase; }
        .summary-box .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .note { font-size: 10px; color: #888; margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>Laporan Laba Rugi</h1>
    <h2>KELAR POS — SAK EMKM</h2>
    <p class="meta">Periode: {{ $tanggalMulai->format('d M Y') }} — {{ $tanggalAkhir->format('d M Y') }} | Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Pendapatan Penjualan (Neto)</div>
            <div class="value">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Harga Pokok Penjualan</div>
            <div class="value">Rp {{ number_format($hpp, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Laba Kotor ({{ $marginPersen }}%)</div>
            <div class="value">Rp {{ number_format($labaKotor, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Beban Operasional</div>
            <div class="value">Rp {{ number_format($totalBeban, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Laba Bersih (Net Profit)</div>
            <div class="value">Rp {{ number_format($labaBersih, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="text-right">Qty Terjual</th>
                <th class="text-right">Pendapatan</th>
                <th class="text-right">HPP</th>
                <th class="text-right">Laba</th>
                <th class="text-center">Margin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kategoriBreakdown as $row)
            <tr>
                <td>{{ $row->nama_kategori }}</td>
                <td class="text-right">{{ $row->total_qty }} pcs</td>
                <td class="text-right">Rp {{ number_format($row->total_penjualan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($row->total_hpp, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($row->laba, 0, ',', '.') }}</td>
                <td class="text-center">{{ $row->margin }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;">Tidak ada penjualan di periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p class="note">Laporan ini disusun berdasarkan SAK EMKM. Pendapatan bersih setelah diskon Rp {{ number_format($totalDiskon, 0, ',', '.') }}. HPP diestimasi berdasarkan harga beli/modal produk. Laba bersih = laba kotor dikurangi beban operasional Rp {{ number_format($totalBeban, 0, ',', '.') }}.</p>
</body>
</html>
