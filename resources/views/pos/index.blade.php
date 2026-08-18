<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transaksi POS — KELAR</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/barcode-engine.js'])
    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; overflow: hidden; font-family: 'Inter', sans-serif; background: #f8fafc; }
        .font-display { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }

        /* Layout */
        #pos-topbar { height: 52px; flex-shrink: 0; background: #0f172a; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; }
        #pos-body   { height: calc(100vh - 52px); display: flex; overflow: hidden; }

        /* Panel Kiri */
        #panel-kiri  { flex: 1; min-width: 0; display: flex; flex-direction: column; overflow: hidden; }
        #search-area { flex-shrink: 0; padding: 12px; display: flex; gap: 8px; }
        #produk-area { flex: 1; overflow-y: auto; padding: 0 12px 12px; }
        .prod-grid   { display: grid; grid-template-columns: repeat(auto-fill, minmax(145px, 1fr)); gap: 10px; }

        /* Panel Kanan — flex column, footer selalu di bawah */
        #panel-kanan  { width: 340px; flex-shrink: 0; display: flex; flex-direction: column; background: white; border-left: 1px solid #e2e8f0; overflow: hidden; }
        #cart-header  { flex-shrink: 0; padding: 12px 16px 10px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; }
        #cart-scroll  { flex: 1; overflow-y: auto; padding: 8px 10px; }
        #cart-footer  { flex-shrink: 0; padding: 12px 14px; border-top: 1px solid #e2e8f0; }

        /* Produk card */
        .produk-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; cursor: pointer; transition: all .15s; position: relative; }
        .produk-card:hover  { border-color: #0E8388; box-shadow: 0 4px 14px rgba(14,131,136,.12); transform: translateY(-1px); }
        .produk-card:active { transform: scale(.97); }
        .produk-card.habis  { opacity: .45; cursor: not-allowed; pointer-events: none; }

        /* Metode radio */
        .m-radio { display: none; }
        .m-label { flex: 1; text-align: center; padding: 7px 4px; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer; font-size: 12px; font-weight: 600; color: #64748b; transition: all .15s; }
        .m-radio:checked + .m-label { background: #0E8388; color: white; border-color: #0E8388; }

        /* Modal overlay */
        .modal-wrap { display: none; position: fixed; inset: 0; z-index: 999; align-items: center; justify-content: center; padding: 16px; background: rgba(15,23,42,.6); backdrop-filter: blur(4px); }
        .modal-wrap.active { display: flex; }
        .modal-box { background: white; border-radius: 20px; width: 100%; max-width: 400px; box-shadow: 0 25px 60px rgba(0,0,0,.25); animation: popIn .2s ease; }
        @keyframes popIn { from { transform: scale(.94); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        /* ===== FLOATING CART BUTTON (Mobile) ===== */
        #fab-cart {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 50;
            background: #0E8388;
            color: white;
            border: none;
            border-radius: 9999px;
            padding: 14px 20px;
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 8px 24px rgba(14,131,136,.4);
            cursor: pointer;
            transition: transform .15s, box-shadow .15s;
        }
        #fab-cart:active { transform: scale(.95); }
        #fab-cart         .fab-badge {
            background: #be123c;
            color: white;
            font-size: 11px;
            font-weight: 700;
            border-radius: 9999px;
            padding: 2px 7px;
            margin-left: 6px;
        }

        /* ===== RESPONSIVE — MOBILE ===== */
        @media (max-width: 767px) {
            #pos-body { flex-direction: column; }

            #panel-kiri { flex: 1; min-height: 0; }

            /* Search area stacking */
            #search-area { flex-wrap: wrap; }
            #search-area > div { flex: 1 1 100%; }
            #filterKategori { max-width: 100%; flex: 1; }
            #btnScanKamera { flex-shrink: 0; }

            /* Product grid: 2 columns on mobile */
            .prod-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }

            /* Panel kanan: bottom sheet */
            #panel-kanan {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                width: 100%;
                max-height: 80vh;
                border-left: none;
                border-top: 1px solid #e2e8f0;
                border-radius: 20px 20px 0 0;
                transform: translateY(100%);
                transition: transform .3s cubic-bezier(.4,0,.2,1);
                z-index: 60;
                box-shadow: 0 -8px 30px rgba(0,0,0,.15);
            }
            #panel-kanan.open { transform: translateY(0); }

            /* Backdrop overlay for bottom sheet */
            #cart-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15,23,42,.5);
                z-index: 55;
            }
            #cart-backdrop.active { display: block; }

            /* Drag handle indicator */
            #cart-drag-handle {
                display: flex;
                justify-content: center;
                padding: 10px 0 4px;
            }
            #cart-drag-handle::after {
                content: '';
                width: 40px;
                height: 4px;
                border-radius: 2px;
                background: #cbd5e1;
            }

            /* Hide fab when bottom sheet is open */
            #panel-kanan.open ~ #fab-cart { display: none !important; }

            /* Cart footer: tighter padding */
            #cart-footer { padding: 10px 12px; }
            #cart-footer button { padding: 12px; }

            /* Modals: nearly full width on mobile */
            .modal-box { max-width: calc(100vw - 32px); border-radius: 16px; }

            /* Topbar: tighten */
            #pos-topbar { padding: 0 10px; }
        }

        /* Small mobile (< 380px): single column grid */
        @media (max-width: 379px) {
            .prod-grid { grid-template-columns: 1fr; }
        }

        /* ===== PRINT STRUK ===== */
        @media print {
            @page { size: 80mm auto; margin: 2mm; }
            body * { visibility: hidden !important; }
            #struk-cetak, #struk-cetak * { visibility: visible !important; }
            #struk-cetak {
                position: fixed; inset: 0; padding: 0;
                width: 76mm; margin: 0 auto;
                font-family: 'Consolas', 'Courier New', monospace; font-size: 11px; line-height: 1.4; color: #000; background: white;
            }
        }
    </style>
</head>
<body>

{{-- TOPBAR --}}
<div id="pos-topbar">
    <div class="flex items-center gap-3">
        <a href="{{ url('/dashboard') }}" class="text-muted p-1.5 rounded-lg leading-none no-underline hover:text-white transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <span class="text-white font-display font-bold text-lg">KELAR<span class="text-teal-400">.</span> POS</span>
    </div>
    <div class="flex items-center gap-3">
        <span id="jam-pos" class="text-muted font-mono text-[13px]"></span>
        <div class="w-[30px] h-[30px] rounded-full bg-primary flex items-center justify-center font-display font-bold text-xs text-white">
            {{ strtoupper(substr(auth()->user()->name,0,2)) }}
        </div>
    </div>
</div>

{{-- BODY --}}
<div id="pos-body">

    {{-- PANEL KIRI --}}
    <div id="panel-kiri">
        <div id="search-area">
            <div class="relative flex-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted leading-none">
                    <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                </span>
                <input id="searchInput" type="text" autocomplete="off" placeholder="Cari nama / scan barcode..."
                    class="w-full py-2.5 pl-[38px] pr-3 border border-border-soft rounded-xl text-[13px] outline-none bg-surface transition"
                    onfocus="this.style.borderColor='#0E8388';this.style.boxShadow='0 0 0 3px rgba(14,131,136,.15)';"
                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';">
            </div>
            <select id="filterKategori"
                class="py-2.5 px-3 border border-border-soft rounded-xl text-[13px] outline-none bg-surface text-muted max-w-[130px] cursor-pointer"
                onfocus="this.style.borderColor='#0E8388'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">Semua</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                @endforeach
            </select>
            <button id="btnScanKamera" onclick="bukaScanKamera()" type="button"
                class="p-2.5 border border-border-soft rounded-xl bg-surface text-primary cursor-pointer flex items-center justify-center transition hover:border-primary hover:bg-body-bg"
                title="Scan Barcode via Kamera">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </button>
        </div>
        <div id="produk-area">
            <div id="produkGrid" class="prod-grid">
                <div class="col-span-full text-center py-[60px] text-muted">
                    <svg class="h-8 w-8 mx-auto mb-2 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25 stroke-primary" cx="12" cy="12" r="10" stroke-width="4"/>
                        <path class="opacity-75 fill-primary" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <p class="text-[13px]">Memuat produk...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- CART BACKDROP (Mobile bottom sheet overlay) --}}
    <div id="cart-backdrop" onclick="tutupCartSheet()"></div>

    {{-- PANEL KANAN --}}
    <div id="panel-kanan">

        {{-- Drag handle (mobile) --}}
        <div id="cart-drag-handle"></div>

        {{-- Header --}}
        <div id="cart-header">
            <span class="font-display font-bold text-neutral-dark text-[15px]">🛒 Keranjang</span>
            <button id="btnKosongkan" onclick="kosongkanKeranjang()" class="hidden text-xs font-semibold text-rose-700 bg-rose-50 border-none px-2.5 py-1 rounded-lg cursor-pointer hover:bg-rose-100 transition">Kosongkan</button>
        </div>

        {{-- Cart area: emptyState + itemList SELALU di DOM, toggle visibility --}}
        <div id="cart-scroll">
            {{-- Empty State — selalu ada di DOM --}}
            <div id="emptyCart" class="flex flex-col items-center justify-center h-full py-10 text-muted">
                <svg class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-[13px] font-semibold text-muted">Keranjang kosong</p>
                <p class="text-[11px] text-muted mt-1">Pilih produk atau scan barcode</p>
            </div>
            {{-- Item List — selalu ada di DOM, awalnya hidden --}}
            <div id="cartList" class="hidden"></div>
        </div>

        {{-- Footer Pembayaran --}}
        <div id="cart-footer">
            <div class="flex items-center justify-between gap-2 mb-2.5">
                <label class="text-xs font-semibold text-muted whitespace-nowrap">Diskon Total</label>
                <input type="number" id="inputDiskon" value="0" min="0" step="500" readonly
                    class="w-[120px] text-right py-1.5 px-2.5 border border-border-soft rounded-lg text-[13px] outline-none bg-slate-50">
            </div>
            <div class="border-t border-dashed border-border-soft mb-2.5"></div>
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[13px] font-bold text-neutral-dark">Total Bayar</span>
                <span id="displayTotal" class="font-display font-bold text-xl text-primary">Rp 0</span>
            </div>
            <div class="mb-2">
                <label class="block text-[11px] font-bold text-muted uppercase tracking-wider mb-1">Jumlah Diterima (Rp)</label>
                <input type="text" inputmode="numeric" id="inputJumlahBayar" maxlength="12" autocomplete="off" placeholder="Masukkan nominal..."
                    class="w-full py-2.5 px-3.5 text-right border border-border-soft rounded-xl text-lg font-bold outline-none transition"
                    oninput="sanitasiNominal(this);hitungKembalian()" onfocus="this.style.borderColor='#0E8388';this.style.boxShadow='0 0 0 3px rgba(14,131,136,.15)'" onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
            </div>
            <div id="kembalianBox" class="hidden bg-emerald-50 border border-emerald-200 rounded-xl px-3.5 py-2 justify-between items-center mb-2">
                <span class="text-[13px] font-semibold text-emerald-700">Kembalian</span>
                <span id="displayKembalian" class="font-display font-bold text-base text-emerald-700">Rp 0</span>
            </div>
            <div id="donasiBox" class="hidden bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2 items-center gap-2.5 mb-2 cursor-pointer select-none">
                <input type="checkbox" id="relakanKembalian" class="accent-[#0E8388]">
                <label for="relakanKembalian" class="text-[12px] font-semibold text-amber-800 cursor-pointer">Pelanggan relakan kembalian <span id="donasiNominal" class="font-bold">Rp 0</span> sebagai donasi</label>
            </div>
            <div class="flex gap-1.5 mb-2">
                <input type="radio" id="m_tunai"    name="metode" value="tunai"    class="m-radio" checked onchange="hitungKembalian()"> <label for="m_tunai"    class="m-label">💵 Tunai</label>
                <input type="radio" id="m_transfer" name="metode" value="transfer" class="m-radio" onchange="hitungKembalian()">         <label for="m_transfer" class="m-label">🏦 Transfer</label>
                <input type="radio" id="m_qris"     name="metode" value="qris"     class="m-radio" onchange="hitungKembalian()">         <label for="m_qris"     class="m-label">📱 QRIS</label>
            </div>
            <button id="btnBayar" onclick="prosesTransaksi()" disabled
                class="w-full py-3 border-none rounded-2xl font-display font-bold text-base bg-slate-200 text-muted cursor-not-allowed transition">
                Proses Pembayaran
            </button>
            @if($midtransAvailable)
            <button id="btnBayarMidtrans" onclick="prosesMidtrans()"
                class="w-full mt-2 py-3 border-none rounded-2xl font-display font-bold text-base bg-primary text-white cursor-pointer transition hover:bg-primary-dark">
                💳 Bayar Online (Midtrans)
            </button>
            @endif
        </div>
    </div>
</div>

{{-- ===== FLOATING CART BUTTON (Mobile) ===== --}}
<button id="fab-cart" onclick="bukaCartSheet()" type="button">
    🛒 <span id="fab-count">0</span> item
    <span id="fab-total" class="ml-2 text-xs opacity-85">Rp 0</span>
</button>

{{-- ===== MODAL KONFIRMASI ===== --}}
<div id="modalBayar" class="modal-wrap">
    <div class="modal-box">
        <div class="px-6 pt-5 pb-4 border-b border-border-soft">
            <h3 class="font-display font-bold text-[17px] text-neutral-dark m-0">✅ Konfirmasi Pembayaran</h3>
        </div>
        <div class="px-6 py-4 text-[13px]">
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-muted">Total Item</span><span id="conf_item" class="font-semibold"></span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-muted">Subtotal</span><span id="conf_harga" class="font-semibold"></span></div>
            <div class="flex justify-between py-1 border-b border-slate-50"><span class="text-muted">Diskon</span><span id="conf_diskon" class="font-semibold text-rose-700"></span></div>
            <div class="flex justify-between pt-2.5 pb-1 border-t border-dashed border-border-soft mt-1.5">
                <span class="font-bold text-neutral-dark text-sm">Total Bayar</span>
                <span id="conf_total" class="font-display font-bold text-base text-primary"></span>
            </div>
            <div class="flex justify-between py-1"><span class="text-muted">Diterima</span><span id="conf_bayar" class="font-semibold"></span></div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-3.5 py-2.5 flex justify-between mt-2">
                <span class="font-bold text-emerald-700">Kembalian</span>
                <span id="conf_kembalian" class="font-display font-bold text-base text-emerald-700"></span>
            </div>
            <div id="conf_donasi_box" class="hidden bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2 flex justify-between mt-2">
                <span class="font-bold text-amber-800">Donasi (relakan)</span>
                <span id="conf_donasi" class="font-display font-bold text-base text-amber-800"></span>
            </div>
            <p id="conf_metode" class="text-center text-muted text-[11px] mt-2.5"></p>
        </div>
        <div class="px-6 pb-5 flex gap-2.5">
            <button onclick="tutupModalBayar()" class="flex-1 py-2.5 bg-slate-100 border-none rounded-xl font-semibold text-[13px] text-muted cursor-pointer hover:bg-slate-200 transition">Batal</button>
            <button id="btnSimpan" onclick="simpanTransaksi()" class="flex-1 py-2.5 bg-primary hover:bg-primary-dark border-none rounded-xl font-bold text-[13px] text-white cursor-pointer transition">✓ Simpan & Bayar</button>
        </div>
    </div>
</div>

{{-- ===== MODAL STRUK ===== --}}
<div id="modalStruk" class="modal-wrap">
    <div class="modal-box" style="max-width:360px;">
        <div id="strukContent" style="padding:16px;font-family:'Consolas','Courier New',monospace;font-size:11px;line-height:1.4;max-height:60vh;overflow-y:auto;width:76mm;margin:0 auto;background:white;"></div>
        <div class="px-5 pb-5 flex gap-2.5 mt-2">
            <button onclick="cetakStruk()" class="flex-1 py-2.5 bg-primary border-none rounded-xl font-bold text-[13px] text-white cursor-pointer hover:bg-primary-dark transition">🖨 Cetak Struk</button>
            <button onclick="tutupStruk()"  class="flex-1 py-2.5 bg-slate-100 border-none rounded-xl font-semibold text-[13px] text-muted cursor-pointer hover:bg-slate-200 transition">Transaksi Baru</button>
        </div>
    </div>
</div>

{{-- ===== MODAL SCAN KAMERA ===== --}}
<div id="modalScanKamera" class="modal-wrap">
    <div class="modal-box" style="max-width:440px;">
        <div class="px-6 pt-5 pb-4 border-b border-border-soft flex justify-between items-center">
            <h3 class="font-display font-bold text-[17px] text-neutral-dark m-0">📷 Scan Barcode Kamera</h3>
            <button onclick="tutupScanKamera()" class="bg-transparent border-none cursor-pointer text-xl text-muted font-bold leading-none hover:text-muted transition">&times;</button>
        </div>
        <div class="px-6 py-4">
            <div id="reader" class="w-full bg-body-bg rounded-xl overflow-hidden border border-border-soft"></div>
            <p id="scan-status-pos" class="text-center text-muted text-xs mt-3">Arahkan kamera ke barcode produk</p>
            <p id="scan-hint-pos" class="text-center text-muted text-[11px] mt-1.5 hidden leading-relaxed">
                Jaga jarak 10-15cm, pastikan barcode tidak buram/silau.
            </p>
            <div class="flex justify-center mt-2.5">
                <button id="btn-torch-pos" onclick="toggleTorchPos()" class="hidden items-center gap-1.5 px-3.5 py-1.5 bg-slate-100 border-none rounded-lg text-[11px] font-semibold text-muted cursor-pointer">
                    <svg id="torch-icon-pos" class="h-3.5 w-3.5 stroke-slate-600" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span id="torch-text-pos">Lampu</span>
                </button>
            </div>
            <div id="scan-controls-pos" class="mt-3 space-y-2"></div>
        </div>
        <div class="px-6 pb-5">
            <button onclick="tutupScanKamera()" class="w-full py-2.5 bg-slate-100 border-none rounded-xl font-semibold text-[13px] text-muted cursor-pointer hover:bg-slate-200 transition">Tutup</button>
        </div>
    </div>
</div>

{{-- Hidden print area --}}
<div id="struk-cetak" class="hidden"></div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<!-- GLOBAL MODAL -->
@include('components.global-modal')

<!-- SCANNER ENGINE -->
@include('components.scanner-engine')

<script>
// ===== STATE =====
let keranjang = [];
let allProduk  = [];
let scanBuffer = '';
let scanTimer  = null;
let dataTerakhir = null; // untuk print
let snapWidget = null;

// ===== MIDTRANS CONFIG (dari server) =====
const MIDTRANS_AVAILABLE  = @json($midtransAvailable);
const MIDTRANS_SNAP_JS_URL = @json(config('midtrans.snap_js_url'));
const MIDTRANS_CLIENT_KEY  = @json(config('midtrans.client_key'));

// ===== CLOCK =====
(function tick(){
    const el = document.getElementById('jam-pos');
    if(el) el.textContent = new Date().toLocaleTimeString('id-ID');
    setTimeout(tick, 1000);
})();

// ===== FORMAT =====
function rp(n){ return 'Rp ' + Math.round(n||0).toLocaleString('id-ID'); }
function escH(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escJ(s){ return String(s||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }

// ===== LOAD PRODUK =====
async function loadProduk(q='', katId=''){
    try{
        const r = await fetch(`/produk/search?q=${encodeURIComponent(q)}&kategori_id=${katId}`,{headers:{'X-Requested-With':'XMLHttpRequest'}});
        allProduk = await r.json();
        renderGrid(allProduk);
    } catch(e){
        document.getElementById('produkGrid').innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#64748b;font-size:13px;">Gagal memuat produk.</div>';
    }
}

function renderGrid(list){
    const grid = document.getElementById('produkGrid');
    if(!list.length){
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 0;color:#64748b;font-size:13px;font-weight:600;">Produk tidak ditemukan</div>';
        return;
    }
    grid.innerHTML = list.map(p => {
        const tampil = stokTampil(p);
        const sc = tampil<=0 ? 'bg-rose-100 text-rose-700' : (tampil<=p.stok_minimum ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
        // Card tetap diklik tapi menampilkan alert bila stok tampilan habis (kecuali stok DB benar-benar 0).
        const habis = p.stok<=0;
        const img = p.gambar
            ? `<img src="/storage/${p.gambar}" style="width:100%;height:80px;object-fit:cover;" loading="lazy" alt="">`
            : `<div style="width:100%;height:80px;background:#f8fafc;display:flex;align-items:center;justify-content:center;"><svg style="width:28px;height:28px;color:#e2e8f0;" fill="none" viewBox="0 0 24 24" stroke="#e2e8f0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>`;
        return `<div class="produk-card${habis?' habis':''}" onclick="pilihProduk(${p.id})">
            ${img}
            ${p.aturan_diskon_aktif?`<span style="position:absolute;top:6px;right:6px;z-index:2;padding:2px 6px;border-radius:9999px;background:#0E8388;color:#fff;font-size:9px;font-weight:700;">${p.aturan_diskon_aktif.tipe_diskon==='persen'?p.aturan_diskon_aktif.nilai_diskon+'%':(p.aturan_diskon_aktif.tipe_diskon==='free-packaging'?'Kemasan':rp(p.aturan_diskon_aktif.nilai_diskon))}</span>`:''}
            <div style="padding:8px 8px 10px;">
                <p style="font-size:11px;font-weight:600;color:#0f172a;margin:0 0 3px;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">${escH(p.nama_produk)}</p>
                <p style="font-size:13px;font-weight:700;color:#0E8388;margin:0 0 4px;">${rp(p.harga_jual)}</p>
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full ${sc}">Stok: ${tampil}</span>
            </div>
        </div>`;
    }).join('');
}

// ===== CART =====
// Stok TAMPILAN = stok DB − qty yang sudah di keranjang (murni di JS, tidak menyentuh DB).
function qtyKeranjang(id){ const it = keranjang.find(i => Number(i.id) === Number(id)); return it ? it.qty : 0; }
function stokTampil(p){ return Math.max(0, (Number(p.stok)||0) - qtyKeranjang(p.id)); }

function pilihProduk(id){
    const p = allProduk.find(x => Number(x.id) === Number(id));
    if(p) tambahProduk(p);
}

// Satu handler penambahan produk — dipakai klik card, scan hardware, dan kamera.
function tambahProduk(p){
    if(!p) return false;
    const tersisa = stokTampil(p);
    if(tersisa <= 0){
        appAlert('Stok Habis',
            (Number(p.stok)||0) <= 0
                ? `Stok "${p.nama_produk}" sudah habis.`
                : `Stok "${p.nama_produk}" tersisa ${tersisa} — sudah semua di keranjang.`,
            {type:'error'});
        return false;
    }
    addCart(p.id, p.kode_produk, p.nama_produk, p.harga_jual, p.stok, p.aturan_diskon_aktif || null);
    return true;
}

// Cari produk by kode (dulu cek allProduk, fallback fetch /produk/search) lalu tambah.
// Dipakai jalur scan: keyboard-wedge & kamera. Return produk jika berhasil ditambahkan.
async function cariDanTambah(kode){
    let f = allProduk.find(p => p.kode_produk === kode);
    if(!f){
        try{
            const r = await fetch(`/produk/search?q=${encodeURIComponent(kode)}`, {headers:{'X-Requested-With':'XMLHttpRequest'}});
            const list = await r.json();
            f = list.find(p => p.kode_produk === kode) || null;
        }catch(e){}
    }
    if(!f){
        appAlert('Kode Tidak Ditemukan', `Kode "${kode}" tidak terdaftar di sistem.`, {type:'error'});
        return null;
    }
    return tambahProduk(f) ? f : null;
}

function addCart(id,kode,nama,harga,stok,aturan){
    // Dukungan klik card: hanya id dikirim → resolve data dari allProduk
    // (menghindari sisipan JSON/string di atribut onclick yang bisa merusak HTML).
    if(typeof kode === 'undefined' || kode === null){
        const found = allProduk.find(p => Number(p.id) === Number(id));
        if(!found) return;
        kode   = found.kode_produk;
        nama   = found.nama_produk;
        harga  = found.harga_jual;
        stok   = found.stok;
        aturan = found.aturan_diskon_aktif || null;
    }
    if(stok<=0) return;
    const idx = keranjang.findIndex(i=>i.id===id);
    if(idx>=0){
        if(keranjang[idx].qty >= stok){
            appAlert('Stok Habis', `Stok "${nama}" sudah habis di keranjang.`, {type:'error'});
            return;
        }
        keranjang[idx].qty++;
    } else {
        keranjang.push({
            id,kode,nama,harga:parseFloat(harga),qty:1,stok,
            tipe_diskon: aturan ? aturan.tipe_diskon : null,
            nilai_diskon: aturan ? parseFloat(aturan.nilai_diskon) : 0
        });
    }
    renderCart();
}

function changeQty(id,val){
    const idx = keranjang.findIndex(i=>i.id===id);
    if(idx<0) return;
    const item = keranjang[idx];
    // Sisa yang masih boleh untuk baris ini = stok − (qty total keranjang − qty baris ini)
    const maxBaris = Math.max(0, item.stok - (qtyKeranjang(id) - item.qty));
    const q = parseInt(val, 10);
    if(isNaN(q)||q<=0){ delItem(id); return; }
    if(q > maxBaris){
        q = maxBaris;
        appAlert('Stok Habis', `Stok "${item.nama}" hanya tersisa ${maxBaris} untuk ditambahkan.`, {type:'error'});
    }
    keranjang[idx].qty = Math.max(1, q);
    renderCart();
}

function delItem(id){
    keranjang = keranjang.filter(i=>i.id!==id);
    if(!keranjang.length) resetPembayaran();
    renderCart();
}

function resetPembayaran(){
    document.getElementById('inputJumlahBayar').value = '';
    document.getElementById('kembalianBox').style.display = 'none';
    document.getElementById('displayKembalian').textContent = 'Rp 0';
}

function kosongkanKeranjang(){
    if(!keranjang.length) return;
    appConfirm('Kosongkan Keranjang','Kosongkan seluruh keranjang?').then(function(ok){
        if(!ok) return;
        keranjang = [];
        renderCart();
        resetPembayaran();
        document.getElementById('inputDiskon').value = 0;
    });
}

// FIXED: emptyCart & cartList always in DOM, just toggle display
function renderCart(){
    const emptyEl = document.getElementById('emptyCart');
    const listEl  = document.getElementById('cartList');
    const kosBtn  = document.getElementById('btnKosongkan');

    if(!keranjang.length){
        emptyEl.style.display = 'flex';
        listEl.style.display  = 'none';
        listEl.innerHTML = '';
        kosBtn.style.display = 'none';
    } else {
        emptyEl.style.display = 'none';
        listEl.style.display  = 'block';
        kosBtn.style.display  = 'inline-block';
        listEl.innerHTML = keranjang.map(item => {
            const bruto = item.harga*item.qty;
            const diskonEfektif = hitungDiskonEfektif(item);
            const subtotalNet = Math.max(0, bruto - diskonEfektif);
            const tipe = item.tipe_diskon||'';
            const labelDiskon = tipe==='persen'
                ? item.nilai_diskon+'%'
                : (tipe==='free-packaging' ? 'Gratis Kemasan' : rp(item.nilai_diskon||0));
            const maxBaris = Math.max(1, item.stok - (qtyKeranjang(item.id) - item.qty));
            return `
            <div style="display:flex;align-items:flex-start;gap:6px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:9px 8px;margin-bottom:7px;">
                <div style="flex:1;min-width:0;">
                    <p style="font-size:11px;font-weight:600;color:#0f172a;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${escH(item.nama)}</p>
                    <p style="font-size:12px;color:#0E8388;font-weight:600;margin:0;">${rp(item.harga)}</p>
                    ${tipe ? `<span style="display:inline-flex;align-items:center;gap:3px;margin-top:5px;padding:2px 7px;border-radius:9999px;background:#0E8388;color:#fff;font-size:9px;font-weight:700;">DISKON OTOMATIS ${labelDiskon}</span>` : ''}
                </div>
                <div style="display:flex;align-items:center;gap:3px;flex-shrink:0;">
                    <button onclick="changeQty(${item.id},${item.qty-1})" style="width:24px;height:24px;border-radius:6px;background:#e2e8f0;border:none;cursor:pointer;font-size:14px;font-weight:700;color:#475569;line-height:1;">−</button>
                    <input type="number" value="${item.qty}" min="1" max="${maxBaris}" onchange="changeQty(${item.id},this.value)"
                        style="width:36px;height:24px;text-align:center;border:1px solid #e2e8f0;border-radius:6px;font-size:12px;font-weight:700;outline:none;">
                    <button onclick="changeQty(${item.id},${item.qty+1})" style="width:24px;height:24px;border-radius:6px;background:#e2e8f0;border:none;cursor:pointer;font-size:14px;font-weight:700;color:#475569;line-height:1;">+</button>
                    <button onclick="delItem(${item.id})" style="width:24px;height:24px;border-radius:6px;background:#ffe4e6;border:none;cursor:pointer;line-height:0;display:flex;align-items:center;justify-content:center;margin-left:2px;">
                        <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke="#be123c"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div style="width:64px;text-align:right;flex-shrink:0;">
                    ${diskonEfektif>0?`<p style="font-size:10px;color:#be123c;font-weight:600;margin:0;text-decoration:line-through;">${rp(bruto)}</p>`:''}
                    <p style="font-size:12px;font-weight:700;color:#0f172a;margin:0;">${rp(subtotalNet)}</p>
                </div>
            </div>`;
        }).join('');
    }
    hitungTotal();
    updateFab();
    renderGrid(allProduk);
}

// ===== DISKON PER ITEM =====
function hitungDiskonEfektif(item){
    const bruto = item.harga*item.qty;
    const nilai = parseFloat(item.nilai_diskon)||0;
    if(item.tipe_diskon==='nominal')         return Math.max(0, Math.min(bruto,nilai));
    if(item.tipe_diskon==='free-packaging')  return Math.max(0, Math.min(bruto, nilai*item.qty));
    if(item.tipe_diskon==='persen')          return Math.max(0, Math.min(bruto, bruto*nilai/100));
    return 0;
}
function getDiskonAgregat(){ return keranjang.reduce((s,i)=>s+hitungDiskonEfektif(i),0); }

// ===== KALKULASI =====
function getSub(){ return keranjang.reduce((s,i)=>s+i.harga*i.qty, 0); }
function getDiskon(){ return getDiskonAgregat(); }
function getTotal(){
    const sub = getSub();
    return Math.max(0, sub - getDiskonAgregat());
}

function hitungTotal(){
    const total = getTotal();
    // header diskon = agregat diskon per baris (read-only)
    document.getElementById('inputDiskon').value = Math.round(getDiskonAgregat());
    document.getElementById('displayTotal').textContent = rp(total);
    const btn = document.getElementById('btnBayar');
    if(keranjang.length>0 && total>0){
        btn.disabled = false;
        btn.style.background = '#0E8388';
        btn.style.color = 'white';
        btn.style.cursor = 'pointer';
    } else {
        btn.disabled = true;
        btn.style.background = '#e2e8f0';
        btn.style.color = '#64748b';
        btn.style.cursor = 'not-allowed';
    }
    hitungKembalian();
}

function hitungKembalian(){
    const total = getTotal();
    // Nilai dari input sudah dipaksa digit-only (tanpa pemisah ribuan/notasi ilmiah).
    const bayar = Number(document.getElementById('inputJumlahBayar').value)||0;
    const box = document.getElementById('kembalianBox');
    const donasiBox = document.getElementById('donasiBox');
    const metode = document.querySelector('input[name="metode"]:checked').value;
    const sisa = bayar - total;
    const AMBANG_DONASI = 500;
    if(bayar>0 && bayar>=total){
        box.style.display = 'flex';
        document.getElementById('displayKembalian').textContent = rp(sisa);
    } else {
        box.style.display = 'none';
    }
    // FASE 4: selisih kecil (≤ Rp 500) pada metode tunai boleh direlakan sebagai donasi.
    if(metode==='tunai' && sisa>0 && sisa<=AMBANG_DONASI){
        document.getElementById('donasiNominal').textContent = rp(sisa);
        donasiBox.style.display = 'flex';
    } else {
        document.getElementById('relakanKembalian').checked = false;
        donasiBox.style.display = 'none';
    }
}

// Hanya terima digit (0-9), maksimal 12 digit — mencegah notasi ilmiah/paste simbol.
function sanitasiNominal(el){
    el.value = (el.value||'').replace(/[^\d]/g,'').slice(0,12);
}

// ===== PROSES TRANSAKSI =====
function prosesTransaksi(){
    if(!keranjang.length) return;
    const metode = document.querySelector('input[name="metode"]:checked').value;
    const total  = getTotal();
    const sub    = getSub();
    const diskon = getDiskonAgregat();
    const bayar  = parseFloat(document.getElementById('inputJumlahBayar').value)||0;

    if(metode==='tunai' && bayar<total){
        appAlert('Pembayaran Kurang','Jumlah diterima kurang!\nTotal: '+rp(total)+'\nDiterima: '+rp(bayar));
        document.getElementById('inputJumlahBayar').focus();
        return;
    }
    const totalItem = keranjang.reduce((s,i)=>s+i.qty,0);
    const relakan = document.getElementById('relakanKembalian').checked;
    document.getElementById('conf_item').textContent     = totalItem+' item';
    document.getElementById('conf_harga').textContent    = rp(sub);
    document.getElementById('conf_diskon').textContent   = '- '+rp(diskon);
    document.getElementById('conf_total').textContent    = rp(total);
    document.getElementById('conf_bayar').textContent    = rp(bayar);
    document.getElementById('conf_kembalian').textContent= rp(metode==='tunai' ? bayar-total : 0);
    const donasiBox = document.getElementById('conf_donasi_box');
    if(relakan && metode==='tunai' && (bayar-total)>0){
        document.getElementById('conf_donasi').textContent = rp(bayar-total);
        donasiBox.style.display = 'flex';
        document.getElementById('conf_kembalian').textContent = rp(0);
    } else {
        donasiBox.style.display = 'none';
    }
    document.getElementById('conf_metode').textContent   = 'Metode: '+{tunai:'💵 Tunai',transfer:'🏦 Transfer',qris:'📱 QRIS'}[metode];
    document.getElementById('modalBayar').classList.add('active');
}
function tutupModalBayar(){ document.getElementById('modalBayar').classList.remove('active'); }

async function simpanTransaksi(){
    const btn = document.getElementById('btnSimpan');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const metode = document.querySelector('input[name="metode"]:checked').value;
    const bayar  = parseFloat(document.getElementById('inputJumlahBayar').value)||0;
    const relakan = document.getElementById('relakanKembalian').checked;
    try{
        const res = await fetch('/transaksi',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({
                items: keranjang.map(i=>({
                    produk_id:i.id,jumlah:i.qty,harga_satuan:i.harga
                })),
                metode_pembayaran:metode, jumlah_bayar:bayar, relakan_kembalian:relakan
            })
        });
        const data = await res.json();
        if(!res.ok) throw new Error(data.message||'Gagal menyimpan transaksi.');
        dataTerakhir = data;
        tutupModalBayar();
        tutupCartSheet();
        tampilStruk(data);
        resetKeranjang();
        loadProduk(document.getElementById('searchInput').value, document.getElementById('filterKategori').value);
    } catch(e){
        appAlert('Error',e.message,{type:'error'});
    } finally {
        btn.disabled = false; btn.textContent = '✓ Simpan & Bayar';
    }
}

// Bersihkan keranjang & input setelah transaksi selesai
function resetKeranjang(){
    keranjang = [];
    renderCart();
    resetPembayaran();
    document.getElementById('inputDiskon').value = 0;
}

// ===== MIDTRANS (BAYAR ONLINE) =====
function metodeLabel(m){
    return ({tunai:'💵 Tunai',transfer:'🏦 Transfer',qris:'📱 QRIS',midtrans:'💳 Online (Midtrans)'}[m]) || m;
}

function muatSnapJs(){
    return new Promise(function(resolve, reject){
        if(window.snap){ resolve(); return; }
        var s = document.createElement('script');
        s.src = MIDTRANS_SNAP_JS_URL + '?v=1';
        s.setAttribute('data-client-key', MIDTRANS_CLIENT_KEY);
        s.onload  = function(){ resolve(); };
        s.onerror = function(){ reject(new Error('Gagal memuat widget pembayaran Midtrans.')); };
        document.head.appendChild(s);
    });
}

async function prosesMidtrans(){
    if(!keranjang.length) return;
    const btn = document.getElementById('btnBayarMidtrans');
    btn.disabled = true; btn.textContent = 'Menyiapkan pembayaran...';
    try{
        const res = await fetch('/midtrans/create-transaction',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({
                items: keranjang.map(i=>({
                    produk_id:i.id,jumlah:i.qty,harga_satuan:i.harga
                }))
            })
        });
        const data = await res.json();
        if(!res.ok) throw new Error(data.message||'Gagal membuat pembayaran online.');
        await muatSnapJs();
        snapWidget = window.snap;
        // onSuccess HANYA memicu verifikasi ke server — struk tidak muncul dari callback ini.
        snapWidget.pay(data.snap_token, {
            onSuccess: function(){ verifikasiMidtrans(data.kode_transaksi); },
            onPending: function(){ tutupCartSheet(); appAlert('Menunggu Pembayaran','Pembayaran Anda sedang diproses. Status akan dikonfirmasi otomatis.',{type:'alert'}); },
            onError:   function(result){ appAlert('Pembayaran Gagal', (result && result.status_message) || 'Terjadi kesalahan pada pembayaran.',{type:'error'}); },
            onClose:   function(){ /* pengguna menutup popup — transaksi pending tetap diproses via webhook */ }
        });
    }catch(e){
        appAlert('Error', e.message, {type:'error'});
    }finally{
        btn.disabled = false; btn.textContent = '💳 Bayar Online (Midtrans)';
    }
}

// Verifikasi status ke server (re-check API Midtrans) — struk hanya muncul setelah settlement.
async function verifikasiMidtrans(orderId){
    let tersisa = 10;
    while(tersisa-- > 0){
        try{
            const res = await fetch('/midtrans/status?order_id='+encodeURIComponent(orderId),{headers:{'X-Requested-With':'XMLHttpRequest'}});
            const data = await res.json();
            if(data.kode_transaksi){
                dataTerakhir = data;
                tutupCartSheet();
                tampilStruk(data);
                resetKeranjang();
                loadProduk(document.getElementById('searchInput').value, document.getElementById('filterKategori').value);
                return;
            }
            if(data.status === 'dibatalkan'){
                appAlert('Pembayaran Dibatalkan','Transaksi ini telah dibatalkan.',{type:'error'});
                return;
            }
        }catch(e){
            break;
        }
        await new Promise(r=>setTimeout(r,3000));
    }
    appAlert('Menunggu Konfirmasi','Pembayaran diproses. Jika sudah dibayar, status dikonfirmasi otomatis via webhook. Cek di Riwayat Transaksi.',{type:'alert'});
}

// ===== STRUK =====
function tampilStruk(data){
    const tgl = new Date(data.tanggal_transaksi).toLocaleString('id-ID');
    const rows = data.details.map(d=>
        `<div style="display:flex;justify-content:space-between;gap:6px;margin-bottom:2px;">
            <span style="flex:1;font-size:11px;">${escH(d.nama_produk)} x${d.jumlah}</span>
            <span style="flex-shrink:0;font-size:11px;">${rp(d.subtotal)}</span>
         </div>`).join('');

    const html = `
        <div style="text-align:center;margin-bottom:8px;border-bottom:1px dashed #000;padding-bottom:8px;">
            <div style="font-family:'Consolas','Courier New',monospace;font-weight:700;font-size:15px;letter-spacing:2px;">KELAR POS</div>
            <div style="font-size:10px;color:#555;margin-top:4px;">${escH(data.kode_transaksi)}</div>
            <div style="font-size:10px;color:#555;">${tgl}</div>
            <div style="font-size:10px;color:#555;">Kasir: ${escH(data.nama_kasir)}</div>
        </div>
        <div style="border-top:1px dashed #000;border-bottom:1px dashed #000;padding:6px 0;margin:4px 0;">${rows}</div>
        <div style="display:flex;justify-content:space-between;font-size:11px;padding:1px 0;"><span>Metode</span><span>${metodeLabel(data.metode_pembayaran)}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:11px;padding:1px 0;"><span>Subtotal</span><span>${rp(data.total_harga)}</span></div>
        ${data.diskon>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#be123c;padding:1px 0;"><span>Diskon</span><span>- ${rp(data.diskon)}</span></div>`:''}
        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:12px;border-top:1px dashed #000;padding-top:4px;margin-top:4px;"><span>Total</span><span>${rp(data.total_bayar)}</span></div>
        <div style="display:flex;justify-content:space-between;font-size:11px;padding:1px 0;"><span>Diterima</span><span>${rp(data.jumlah_bayar)}</span></div>
        ${data.donasi>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#92400e;padding:1px 0;"><span>Donasi</span><span>${rp(data.donasi)}</span></div>`:''}
        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:11px;color:#047857;padding:1px 0;"><span>Kembali</span><span>${rp(data.kembalian)}</span></div>
        <div style="text-align:center;margin-top:10px;border-top:1px dashed #000;padding-top:6px;font-size:10px;color:#555;">Terima kasih atas kunjungan Anda!</div>`;

    document.getElementById('strukContent').innerHTML = html;
    document.getElementById('struk-cetak').innerHTML = html;
    document.getElementById('modalStruk').classList.add('active');
}

function tutupStruk(){
    document.getElementById('modalStruk').classList.remove('active');
    document.getElementById('searchInput').focus();
}

// Cetak hanya struk via @media print
function cetakStruk(){
    document.getElementById('struk-cetak').style.display = 'block';
    window.print();
    // Setelah print dialog ditutup, sembunyikan lagi
    setTimeout(()=>{ document.getElementById('struk-cetak').style.display='none'; }, 1000);
}

// ===== BARCODE SCANNER =====
document.addEventListener('keydown', function(e){
    const active = document.activeElement;
    if(active && (active.tagName==='INPUT'||active.tagName==='TEXTAREA') && active.id!=='searchInput') return;
    if(e.key==='Enter' && scanBuffer.trim().length>=3){
        const kode = scanBuffer.trim();
        scanBuffer = '';
        clearTimeout(scanTimer);
        document.getElementById('searchInput').value = '';
        cariDanTambah(kode);
        return;
    }
    if(e.key.length===1&&!e.ctrlKey&&!e.altKey&&!e.metaKey){
        scanBuffer+=e.key;
        clearTimeout(scanTimer);
        scanTimer=setTimeout(()=>{scanBuffer='';},250);
    }
});

// ===== LIVE SEARCH =====
let debounce=null;
document.getElementById('searchInput').addEventListener('input',function(){
    clearTimeout(debounce);
    debounce=setTimeout(()=>loadProduk(this.value,document.getElementById('filterKategori').value),300);
});
document.getElementById('filterKategori').addEventListener('change',function(){
    loadProduk(document.getElementById('searchInput').value,this.value);
});

// ===== SCAN KAMERA (barcode-detector / zxing-wasm) =====
function bukaScanKamera() {
    document.getElementById('modalScanKamera').classList.add('active');
    KELARScanner.open({
        containerId: 'reader',
        statusId: 'scan-status-pos',
        tipsId: 'scan-hint-pos',
        torchBtnId: 'btn-torch-pos',
        torchIconId: 'torch-icon-pos',
        torchTextId: 'torch-text-pos',
        controlsId: 'scan-controls-pos',
        onSuccess: handlePosScan
    });
}

function handlePosScan(kode) {
    // Jalur kamera memakai handler bersama: gagal/not-found/stok habis ditangani appAlert di cariDanTambah.
    cariDanTambah(kode).then(function(p){
        if(p) showScanNotification(`✓ ${p.nama_produk} dimasukkan`);
    });
}

function toggleTorchPos() {
    KELARScanner.toggleTorch();
}

function tutupScanKamera() {
    document.getElementById('modalScanKamera').classList.remove('active');
    KELARScanner.close();
}

function showScanNotification(msg) {
    const box = document.createElement('div');
    box.style.position = 'fixed';
    box.style.bottom = '80px';
    box.style.left = '50%';
    box.style.transform = 'translateX(-50%)';
    box.style.background = '#1e293b';
    box.style.color = '#f8fafc';
    box.style.padding = '10px 18px';
    box.style.borderRadius = '30px';
    box.style.fontSize = '13px';
    box.style.fontWeight = '600';
    box.style.zIndex = '1000';
    box.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
    box.style.textAlign = 'center';
    box.textContent = msg;
    document.body.appendChild(box);
    setTimeout(() => {
        box.style.opacity = '0';
        box.style.transition = 'opacity 0.3s ease';
        setTimeout(() => box.remove(), 300);
    }, 1500);
}

// ===== INIT =====
window.addEventListener('DOMContentLoaded',function(){
    loadProduk();
    document.getElementById('searchInput').focus();
});

// ===== BOTTOM SHEET (Mobile) =====
function bukaCartSheet(){
    document.getElementById('panel-kanan').classList.add('open');
    document.getElementById('cart-backdrop').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function tutupCartSheet(){
    document.getElementById('panel-kanan').classList.remove('open');
    document.getElementById('cart-backdrop').classList.remove('active');
    document.body.style.overflow = '';
}

// ===== FAB UPDATE =====
function updateFab(){
    const fab = document.getElementById('fab-cart');
    const countEl = document.getElementById('fab-count');
    const totalEl = document.getElementById('fab-total');
    const totalItems = keranjang.reduce((s,i)=>s+i.qty, 0);
    const totalPrice = keranjang.reduce((s,i)=>s+i.harga*i.qty, 0);
    countEl.textContent = totalItems;
    totalEl.textContent = rp(totalPrice);
    // Show/hide FAB based on screen width + sheet state
    if(window.innerWidth < 768 && !document.getElementById('panel-kanan').classList.contains('open')){
        fab.style.display = totalItems > 0 ? 'flex' : 'none';
    } else {
        fab.style.display = 'none';
    }
}
// Re-check FAB on resize
window.addEventListener('resize', updateFab);
</script>
</body>
</html>
