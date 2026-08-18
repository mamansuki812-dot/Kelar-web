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
        .summary { display: flex; gap: 20px; margin-bottom: 16px; }
        .summary-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px; }
        .summary-box .label { font-size: 10px; color: #888; text-transform: uppercase; }
        .summary-box .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan</h1>
    <h2>KELAR POS</h2>
    <p class="meta">Periode: {{ $tanggalMulai->format('d M Y') }} — {{ $tanggalAkhir->format('d M Y') }} |Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Omzet</div>
            <div class="value">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Jumlah Transaksi</div>
            <div class="value">{{ $totalTransaksi }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Rata-rata Nota</div>
            <div class="value">Rp {{ number_format($rataPerTransaksi, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Total Diskon</div>
            <div class="value">Rp {{ number_format($totalDiskon, 0, ',', '.') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode Nota</th>
                <th>Kasir</th>
                <th class="text-right">Diskon</th>
                <th class="text-right">Total Bayar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksis as $trx)
            <tr>
                <td>{{ $trx->tanggal_transaksi->format('d M Y H:i') }}</td>
                <td>{{ $trx->kode_transaksi }}</td>
                <td>{{ $trx->user->name ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($trx->diskon, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;">Tidak ada transaksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
