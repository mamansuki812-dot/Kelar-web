@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $transaksi->kode_transaksi)
@section('page_title', 'Detail Transaksi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-muted">
        <a href="{{ route('transaksi.index') }}" class="hover:text-primary transition">Riwayat Transaksi</a>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-neutral-dark font-semibold">{{ $transaksi->kode_transaksi }}</span>
    </div>

    {{-- Card Struk --}}
    <div class="bg-surface rounded-2xl border border-border-soft shadow-sm overflow-hidden" id="struk-card">

        {{-- Header Struk --}}
        <div class="bg-slate-900 text-white px-4 sm:px-8 py-5 sm:py-6 text-center">
            <p class="font-bold font-display text-2xl tracking-widest">KELAR<span class="text-teal-400">.</span></p>
            <p class="text-muted text-sm mt-1">Point of Sale System</p>
            <div class="mt-4 border-t border-slate-700 pt-4 grid grid-cols-2 gap-2 text-sm text-left">
                <div>
                    <p class="text-muted text-xs">No. Transaksi</p>
                    <p class="font-mono font-semibold">{{ $transaksi->kode_transaksi }}</p>
                </div>
                <div>
                    <p class="text-muted text-xs">Tanggal</p>
                    <p class="font-semibold">{{ $transaksi->tanggal_transaksi->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-muted text-xs">Kasir</p>
                    <p class="font-semibold">{{ $transaksi->user->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-muted text-xs">Metode</p>
                    @php $metodeLabel = ['tunai'=>'💵 Tunai','transfer'=>'🏦 Transfer','qris'=>'📱 QRIS','midtrans'=>'💳 Midtrans']; @endphp
                    <p class="font-semibold">{{ $metodeLabel[$transaksi->metode_pembayaran] ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Daftar Item --}}
        <div class="px-4 sm:px-6 py-5">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-dashed border-border-soft">
                        <th class="pb-3 text-left font-semibold text-muted text-xs uppercase">Produk</th>
                        <th class="pb-3 text-center font-semibold text-muted text-xs uppercase">Qty</th>
                        <th class="pb-3 text-right font-semibold text-muted text-xs uppercase">Harga</th>
                        <th class="pb-3 text-right font-semibold text-muted text-xs uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($transaksi->details as $d)
                    <tr>
                        <td class="py-3 font-medium text-neutral-dark">{{ $d->produk->nama_produk ?? '[Produk dihapus]' }}</td>
                        <td class="py-3 text-center text-muted">{{ $d->jumlah }}</td>
                        <td class="py-3 text-right text-muted">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                        <td class="py-3 text-right font-semibold text-neutral-dark">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        {{-- Ringkasan Pembayaran --}}
        <div class="border-t border-dashed border-border-soft mx-4 sm:mx-6 mb-5 pt-4 space-y-2 text-sm">
            <div class="flex justify-between text-muted">
                <span>Subtotal</span>
                <span>Rp {{ number_format($transaksi->total_harga, 0, ',', '.') }}</span>
            </div>
            @if($transaksi->diskon > 0)
            <div class="flex justify-between text-rose-700">
                <span>Diskon</span>
                <span>- Rp {{ number_format($transaksi->diskon, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between font-bold text-neutral-dark text-base pt-1 border-t border-border-soft">
                <span>Total Bayar</span>
                <span>Rp {{ number_format($transaksi->total_bayar, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-muted">
                <span>Jumlah Diterima</span>
                <span>Rp {{ number_format($transaksi->jumlah_bayar, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between font-bold text-emerald-700">
                <span>Kembalian</span>
                <span>Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Status Badge --}}
        <div class="px-4 sm:px-6 pb-6 text-center">
            <span class="inline-block px-4 py-1.5 text-sm font-bold rounded-full
                {{ $transaksi->status === 'selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                {{ strtoupper($transaksi->status) }}
            </span>
            <p class="text-xs text-muted mt-3">Terima kasih atas kunjungan Anda! 🙏</p>
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="flex gap-3">
        <a href="{{ route('transaksi.index') }}"
            class="flex-1 py-3 text-center bg-slate-100 hover:bg-slate-200 text-neutral-dark font-semibold rounded-xl transition text-sm">
            ← Kembali
        </a>
        <button onclick="cetakStruk()"
            class="flex-1 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-xl transition text-sm">
            🖨 Cetak Struk
        </button>
    </div>

</div>
@endsection

@section('scripts')
<script>
function cetakStruk() {
    const el = document.getElementById('struk-card');
    const w = window.open('', '_blank', 'width=400,height=700');
    w.document.write(`<html><head><title>Struk</title>
<style>
@page { size: 80mm auto; margin: 2mm; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Consolas', 'Courier New', monospace; font-size: 11px; line-height: 1.4; color: #000; width: 76mm; padding: 0; }
.header { text-align: center; margin-bottom: 8px; border-bottom: 1px dashed #000; padding-bottom: 8px; }
.header strong { font-size: 15px; letter-spacing: 2px; }
.header small { font-size: 10px; color: #555; display: block; margin-top: 2px; }
.info { font-size: 10px; margin-top: 6px; }
.info div { display: flex; justify-content: space-between; }
.items { margin: 8px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 6px 0; }
.item { display: flex; justify-content: space-between; margin-bottom: 2px; font-size: 11px; }
.item .name { flex: 1; }
.item .price { text-align: right; white-space: nowrap; }
.summary { margin-top: 6px; }
.summary div { display: flex; justify-content: space-between; padding: 1px 0; }
.summary .total { font-weight: bold; font-size: 12px; border-top: 1px dashed #000; padding-top: 4px; margin-top: 4px; }
.summary .green { color: #047857; font-weight: bold; }
.footer { text-align: center; margin-top: 10px; font-size: 10px; color: #555; border-top: 1px dashed #000; padding-top: 6px; }
</style></head><body>`);
    w.document.write('<div class="header"><strong>KELAR POS</strong>');
    w.document.write('<small>{{ addslashes($transaksi->kode_transaksi) }}</small>');
    w.document.write('<small>{{ $transaksi->tanggal_transaksi->format("d/m/Y H:i") }}</small>');
    w.document.write('<small>Kasir: {{ addslashes($transaksi->user->name ?? "-") }}</small>');
    w.document.write('<div class="info"><div><span>Metode</span><span>{{ addslashes($transaksi->metode_pembayaran) }}</span></div></div>');
    w.document.write('</div>');
    w.document.write('<div class="items">');
    @foreach($transaksi->details as $d)
    w.document.write('<div class="item"><span class="name">{{ addslashes($d->produk->nama_produk ?? "?") }} x{{ $d->jumlah }}</span><span class="price">Rp {{ number_format($d->subtotal, 0, ",", ".") }}</span></div>');
    @endforeach
    w.document.write('</div>');
    w.document.write('<div class="summary">');
    w.document.write('<div><span>Subtotal</span><span>Rp {{ number_format($transaksi->total_harga, 0, ",", ".") }}</span></div>');
    @if($transaksi->diskon > 0)
    w.document.write('<div style="color:#be123c;"><span>Diskon</span><span>- Rp {{ number_format($transaksi->diskon, 0, ",", ".") }}</span></div>');
    @endif
    w.document.write('<div class="total"><span>Total</span><span>Rp {{ number_format($transaksi->total_bayar, 0, ",", ".") }}</span></div>');
    w.document.write('<div><span>Diterima</span><span>Rp {{ number_format($transaksi->jumlah_bayar, 0, ",", ".") }}</span></div>');
    w.document.write('<div class="green"><span>Kembali</span><span>Rp {{ number_format($transaksi->kembalian, 0, ",", ".") }}</span></div>');
    w.document.write('</div>');
    w.document.write('<div class="footer">Terima kasih atas kunjungan Anda!</div>');
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    setTimeout(() => { w.print(); }, 300);
}
</script>
@endsection
