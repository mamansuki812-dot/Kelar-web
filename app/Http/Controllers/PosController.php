<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Services\ShiftKasirService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    /**
     * Tampilkan halaman Point of Sale (POS).
     *
     * Guard FASE 1: kasir WAJIB memiliki shift 'buka' aktif sebelum membuka POS.
     * Admin dikecualikan — alasan: kebutuhan operasional darurat (admin bisa
     * melayani transaksi saat kasir berhalangan) & admin tidak melakukan closing
     * harian kas milik kasir. Guard di sini + di TransaksiController@store.
     */
    public function index()
    {
        $user = auth()->user();
        if ($user->role === 'kasir') {
            $shift = app(ShiftKasirService::class)->shiftAktif($user->id);
            if ($shift === null) {
                return redirect()->route('shift.buka')
                    ->with('info', 'Anda belum membuka shift kasir. Buka shift terlebih dahulu untuk mulai bertransaksi.');
            }
        }

        // Ambil kategori untuk filter di halaman POS
        $kategoris = Kategori::orderBy('nama_kategori')->get();

        // Konfigurasi Midtrans untuk tombol "Bayar Online"
        $midtransAvailable = app(\App\Services\MidtransService::class)->isConfigured();

        return view('pos.index', compact('kategoris', 'midtransAvailable'));
    }
}
