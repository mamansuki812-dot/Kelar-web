<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; color: #666; margin-top: 0; }
        .meta { font-size: 11px; color: #888; margin-bottom: 20px; }
        .cols { display: flex; gap: 20px; }
        .col { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; overflow: hidden; }
        .col-header { background: #f3f4f6; padding: 10px 14px; font-weight: bold; font-size: 13px; border-bottom: 1px solid #e5e7eb; }
        .col-body { padding: 14px; }
        .row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px; }
        .row-label { color: #555; }
        .row-value { font-weight: 600; }
        .sub-header { font-size: 10px; color: #888; text-transform: uppercase; margin: 12px 0 6px; letter-spacing: 0.5px; }
        .total-bar { background: #ccfbf1; padding: 10px 14px; border-top: 2px solid #5eead4; display: flex; justify-content: space-between; font-weight: bold; font-size: 13px; }
        .note { font-size: 10px; color: #888; margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>Laporan Neraca Keuangan</h1>
    <h2>KELAR POS — SAK EMKM</h2>
    <p class="meta">Posisi per: {{ $tanggal->format('d M Y') }} | Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <div class="cols">
        <!-- AKTIVA -->
        <div class="col">
            <div class="col-header">AKTIVA (Aset)</div>
            <div class="col-body">
                <div class="sub-header">Aset Lancar</div>
                <div class="row">
                    <span class="row-label">Kas (Saldo Jurnal)</span>
                    <span class="row-value">Rp {{ number_format($kasEstimasi, 0, ',', '.') }}</span>
                </div>
                <div class="row">
                    <span class="row-label">Bank (Saldo Jurnal)</span>
                    <span class="row-value">Rp {{ number_format($bank, 0, ',', '.') }}</span>
                </div>
                <div class="row">
                    <span class="row-label">Persediaan Barang (Saldo Jurnal)</span>
                    <span class="row-value">Rp {{ number_format($nilaiStok, 0, ',', '.') }}</span>
                </div>
                <div class="sub-header">Aset Tetap</div>
                <div class="row">
                    <span class="row-label">Aset Tetap (Nilai Buku per Jurnal)</span>
                    <span class="row-value">Rp {{ number_format($asetTetap, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="total-bar">
                <span>TOTAL AKTIVA</span>
                <span>Rp {{ number_format($totalAset, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- PASIVA -->
        <div class="col">
            <div class="col-header">PASIVA (Ekuitas & Liabilitas)</div>
            <div class="col-body">
                <div class="sub-header">Liabilitas (Kewajiban)</div>
                <div class="row">
                    <span class="row-label">Utang Usaha / Dagang</span>
                    <span class="row-value">Rp {{ number_format($totalLiabilitas, 0, ',', '.') }}</span>
                </div>
                <div class="sub-header">Ekuitas (Modal)</div>
                <div class="row">
                    <span class="row-label">Modal Disetor & Saldo Laba</span>
                    <span class="row-value">Rp {{ number_format($totalEkuitas, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="total-bar">
                <span>TOTAL PASIVA</span>
                <span>Rp {{ number_format($totalPasiva, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <p class="note">Neraca disusun berdasarkan SAK EMKM. Saldo akun dihitung dari buku jurnal per tanggal posisi (debit/kredit). Aset tetap dicatat sebesar nilai buku (harga perolehan dikurangi akumulasi penyusutan) melalui jurnal perolehan terhadap Modal Pemilik. Ekuitas mencakup modal disetor serta laba kumulatif.
        @if ($seimbang)
            Status: Neraca seimbang (Aset = Liabilitas + Ekuitas).
        @else
            Status: Neraca TIDAK seimbang — selisih Rp {{ number_format(abs($selisihNeraca), 0, ',', '.') }}.
        @endif
    </p>
</body>
</html>
