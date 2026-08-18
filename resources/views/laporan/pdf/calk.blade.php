<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; color: #666; margin-top: 0; }
        .meta { font-size: 11px; color: #888; margin-bottom: 20px; }
        h3 { font-size: 13px; margin: 22px 0 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; color: #1f2937; }
        p { margin: 6px 0; font-size: 12px; line-height: 1.5; }
        .note { font-size: 10px; color: #888; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0 20px; }
        .table th { background: #f3f4f6; text-align: left; padding: 8px 10px; font-size: 11px; border-bottom: 1px solid #e5e7eb; }
        .table td { padding: 7px 10px; font-size: 12px; border-bottom: 1px solid #f3f4f6; }
        .table td.right, .table th.right { text-align: right; }
        .table td.center, .table th.center { text-align: center; }
        .compliant { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 10px 14px; font-size: 12px; color: #14532d; }
    </style>
</head>
<body>
    <h1>Catatan atas Laporan Keuangan (CaLK)</h1>
    <h2>KELAR POS — SAK EMKM</h2>
    <p class="meta">Posisi keuangan per: {{ $tanggal->format('d M Y') }} | Laba Rugi {{ $awalBulan->format('d M Y') }} – {{ $akhirBulan->format('d M Y') }} | Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <div class="compliant">
        <strong>Pernyataan Kepatuhan.</strong> Laporan keuangan ini disusun sesuai dengan Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK EMKM).
    </div>

    <h3>Dasar Penyusunan &amp; Referensi Standar</h3>
    <p>Laporan keuangan ini disusun mengacu pada <strong>Standar Akuntansi Keuangan Entitas Mikro, Kecil, dan Menengah (SAK EMKM)</strong> yang diterbitkan oleh Dewan Standar Akuntansi Keuangan Ikatan Akuntan Indonesia (DSAK IAI), berlaku efektif per 1 Januari 2018. Sesuai SAK EMKM, laporan keuangan entitas terdiri atas Laporan Posisi Keuangan, Laporan Laba Rugi, dan Catatan atas Laporan Keuangan (CaLK) ini.</p>
    <p class="note">Referensi: DSAK IAI — SAK EMKM. Situs resmi IAI: web.iaiglobal.or.id/SAK-IAI/Tentang%20SAK%20EMKM</p>

    <h3>Ikhtisar Kebijakan Akuntansi</h3>
    <p><strong>Dasar Pengukuran.</strong> Biaya historis (historical cost), bukan nilai wajar.</p>
    <p><strong>Dasar Pengakuan.</strong> Basis akrual: pendapatan dan beban diakui saat transaksi terjadi — HPP dipadankan dengan pendapatan pada saat penjualan, dan utang usaha diakui saat barang diterima (bukan saat pembayaran). <span class="note">Catatan: beban operasional masih diakui saat pembayaran; akun akrual/prepaid belum diterapkan.</span></p>
    <p><strong>Penilaian Persediaan.</strong> Biaya perolehan berdasarkan harga beli per produk (harga beli terakhir yang tercatat pada master produk, dikali stok aktif). Bukan FIFO/MPKP atau biaya rata-rata — pelacakan lot (lot costing) belum diterapkan.</p>
    <p><strong>Penyajian.</strong> Mata uang fungsional dan penyajian adalah Rupiah (Rp).</p>

    <h3>Kebijakan Cutover &amp; Saldo Pembukaan</h3>
    <p>1. Pembukuan formal double-entry dimulai pada <strong>05-08-2026</strong> (tanggal cutover). Data uji/testing sebelum tanggal tersebut telah dibersihkan dan bukan bagian dari saldo pembukaan riil.</p>
    <p>2. Modal awal dihitung dari valuasi stok fisik (5 produk aktif × 20 pcs × harga beli per produk): <strong>Rp {{ number_format(150000, 0, ',', '.') }}</strong>. Kas awal <strong>Rp 0 — ESTIMASI SEMENTARA</strong> (belum dihitung fisik; akan dikoreksi dengan entry terpisah Debit Kas / Kredit Modal Pemilik).</p>
    <p>3. Dua transaksi lama (TRX-20260715-00001 dan TRX-20260715-00002, Juli 2026) tidak memiliki jurnal individual dan tidak di-backfill; dampaknya dianggap tercermin dalam saldo pembukaan.</p>

    <h3>Rincian Posisi Keuangan per {{ $tanggal->format('d M Y') }}</h3>
    <table class="table">
        <tr>
            <th>Pos</th>
            <th class="right">Nilai (Rp)</th>
        </tr>
        <tr>
            <td>Kas</td>
            <td class="right">{{ number_format($kasEstimasi, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Persediaan Barang</td>
            <td class="right">{{ number_format($nilaiStok, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Aset</strong></td>
            <td class="right"><strong>{{ number_format($totalAset, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Liabilitas</td>
            <td class="right">{{ number_format($totalLiabilitas, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Ekuitas</td>
            <td class="right">{{ number_format($totalEkuitas, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>Total Liabilitas + Ekuitas</strong></td>
            <td class="right"><strong>{{ number_format($totalPasiva, 0, ',', '.') }}</strong></td>
        </tr>
    </table>
    <p class="note">
        @if ($seimbang)
            Status: Neraca seimbang (Aset = Liabilitas + Ekuitas).
        @else
            Status: Neraca TIDAK seimbang — selisih Rp {{ number_format(abs($selisihNeraca), 0, ',', '.') }}.
        @endif
    </p>

    <h3>Laba Rugi {{ $awalBulan->format('M Y') }}</h3>
    <table class="table">
        <tr>
            <th>Pos</th>
            <th class="right">Nilai (Rp)</th>
        </tr>
        <tr>
            <td>Pendapatan Penjualan (Neto)</td>
            <td class="right">{{ number_format($totalPenjualan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="note">Diskon</td>
            <td class="right note">({{ number_format($totalDiskon, 0, ',', '.') }})</td>
        </tr>
        <tr>
            <td>HPP</td>
            <td class="right">({{ number_format($hpp, 0, ',', '.') }})</td>
        </tr>
        <tr>
            <td><strong>Laba Kotor (Margin {{ $marginPersen }}%)</strong></td>
            <td class="right"><strong>{{ number_format($labaKotor, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Beban Operasional</td>
            <td class="right">({{ number_format($totalBeban, 0, ',', '.') }})</td>
        </tr>
        <tr>
            <td><strong>Laba Bersih</strong></td>
            <td class="right"><strong>{{ number_format($labaBersih, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <h3>Laba Rugi per Kategori ({{ $awalBulan->format('M Y') }})</h3>
    <table class="table">
        <tr>
            <th>Kategori</th>
            <th class="right">Terjual</th>
            <th class="right">Pendapatan (Rp)</th>
            <th class="right">HPP (Rp)</th>
            <th class="right">Laba (Rp)</th>
            <th class="center">Margin</th>
        </tr>
        @forelse($kategoriBreakdown as $row)
        <tr>
            <td>{{ $row->nama_kategori }}</td>
            <td class="right">{{ $row->total_qty }} pcs</td>
            <td class="right">{{ number_format($row->total_penjualan, 0, ',', '.') }}</td>
            <td class="right">{{ number_format($row->total_hpp, 0, ',', '.') }}</td>
            <td class="right">{{ number_format($row->laba, 0, ',', '.') }}</td>
            <td class="center">{{ $row->margin }}%</td>
        </tr>
        @empty
        <tr><td colspan="6" class="note">Tidak ada penjualan pada kategori mana pun di bulan ini.</td></tr>
        @endforelse
    </table>

    <p class="note">Catatan atas Laporan Keuangan disusun sesuai SAK EMKM. Data rincian posisi diambil dari laporan Neraca dan Laba Rugi periode berjalan.</p>
</body>
</html>
