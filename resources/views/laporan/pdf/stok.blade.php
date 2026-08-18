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
        .badge-green { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
        .badge-yellow { background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
        .badge-red { background: #ffe4e6; color: #9f1239; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Laporan Stok Barang</h1>
    <h2>KELAR POS</h2>
    <p class="meta">Dicetak: {{ now()->format('d M Y H:i') }} | Periode Mutasi: {{ $tanggalMulai->format('d M Y') }} — {{ $tanggalAkhir->format('d M Y') }}</p>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Nilai Aset (Harga Jual)</div>
            <div class="value">Rp {{ number_format($totalNilaiStok, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Barang Masuk (Periode)</div>
            <div class="value">{{ number_format($totalMasuk, 0, ',', '.') }} unit</div>
        </div>
        <div class="summary-box">
            <div class="label">Barang Keluar (Periode)</div>
            <div class="value">{{ number_format($totalKeluar, 0, ',', '.') }} unit</div>
        </div>
    </div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Stok Habis</div>
            <div class="value">{{ $totalStokHabis }} Produk</div>
        </div>
        <div class="summary-box">
            <div class="label">Stok Menipis</div>
            <div class="value">{{ $totalMenipis }} Produk</div>
        </div>
        <div class="summary-box">
            <div class="label">Total Mutasi (Periode)</div>
            <div class="value">{{ $jumlahRecord }} catatan</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th class="text-right">Harga Beli</th>
                <th class="text-right">Harga Jual</th>
                <th class="text-right">Stok</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produks as $p)
            <tr>
                <td>{{ $p->kode_produk }}</td>
                <td>{{ $p->nama_produk }}</td>
                <td>{{ $p->kategori->nama_kategori ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                <td class="text-right">{{ $p->stok }} {{ $p->satuan }}</td>
                <td class="text-center">
                    @if($p->stok <= 0)
                        <span class="badge-red">Habis</span>
                    @elseif($p->stok <= $p->stok_minimum)
                        <span class="badge-yellow">Menipis</span>
                    @else
                        <span class="badge-green">Aman</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;">Tidak ada produk aktif.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
