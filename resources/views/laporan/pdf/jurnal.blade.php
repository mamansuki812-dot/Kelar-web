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
        .row-debit { background: #fafafa; }
        .row-kredit { background: #f9fafb; }
        .indent { padding-left: 24px; }
    </style>
</head>
<body>
    <h1>Buku Jurnal Keuangan</h1>
    <h2>KELAR POS</h2>
    <p class="meta">Dicetak: {{ now()->format('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width:80px;">Tanggal</th>
                <th style="width:100px;">Kode Jurnal</th>
                <th>Akun & Keterangan</th>
                <th style="width:120px;" class="text-right">Debet</th>
                <th style="width:120px;" class="text-right">Kredit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jurnals as $j)
            <tr class="row-debit">
                <td rowspan="2" style="vertical-align:top;">{{ $j->tanggal->format('d M Y') }}</td>
                <td rowspan="2" style="vertical-align:top;">{{ $j->kode_jurnal }}</td>
                <td>{{ $j->akun_debit }}</td>
                <td class="text-right">Rp {{ number_format($j->nominal, 0, ',', '.') }}</td>
                <td class="text-right">—</td>
            </tr>
            <tr class="row-kredit">
                <td class="indent">{{ $j->akun_kredit }}@if($j->keterangan) <br><small style="color:#888;">({{ $j->keterangan }})</small>@endif</td>
                <td class="text-right">—</td>
                <td class="text-right">Rp {{ number_format($j->nominal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;">Tidak ada entri jurnal.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
