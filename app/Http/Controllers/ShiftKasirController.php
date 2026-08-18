<?php

namespace App\Http\Controllers;

use App\Models\ShiftKasir;
use App\Services\ShiftKasirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * FASE 1 (revisi dosen) — Controller Shift Kasir (Buka/Tutup/Riwayat).
 * RBAC: route dilindungi role:kasir,admin (lihat routes/web.php).
 */
class ShiftKasirController extends Controller
{
    public function __construct(protected ShiftKasirService $shiftService)
    {
    }

    /**
     * Form buka shift — kasir memilih saldo awal kas.
     */
    public function bukaForm()
    {
        if ($this->shiftService->shiftAktif(Auth::id()) !== null) {
            return redirect()->route('shift.tutup')
                ->with('error', 'Anda sudah memiliki shift yang sedang berjalan. Tutup shift terlebih dahulu sebelum membuka shift baru.');
        }

        return view('shift.buka');
    }

    /**
     * Proses buka shift (POST).
     */
    public function buka(Request $request)
    {
        $request->validate([
            'saldo_awal' => 'required|numeric|min:0',
        ], [
            'saldo_awal.required' => 'Saldo awal kas wajib diisi.',
            'saldo_awal.numeric'  => 'Saldo awal harus berupa angka.',
            'saldo_awal.min'      => 'Saldo awal tidak boleh negatif.',
        ]);

        try {
            $shift = $this->shiftService->bukaShift(Auth::user(), (float) $request->saldo_awal);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('pos.index')
            ->with('success', 'Shift kasir dibuka (saldo awal Rp ' . number_format($shift->saldo_awal, 0, ',', '.') . '). Selamat bertugas!');
    }

    /**
     * Form tutup shift — menampilkan ringkasan transaksi tunai selama shift.
     */
    public function tutupForm()
    {
        $shift = $this->shiftService->shiftAktif(Auth::id());

        if ($shift === null) {
            $shiftTerakhir = ShiftKasir::where('user_id', Auth::id())
                ->orderByDesc('id')
                ->first();

            if ($shiftTerakhir !== null && $shiftTerakhir->status === 'tutup') {
                return view('shift.tutup', ['shift' => $shiftTerakhir, 'ringkasan' => $this->shiftService->ringkasanShift($shiftTerakhir), 'sudahTutup' => true]);
            }

            return redirect()->route('shift.buka')
                ->with('info', 'Belum ada shift yang berjalan. Silakan buka shift terlebih dahulu.');
        }

        $ringkasan = $this->shiftService->ringkasanShift($shift);

        return view('shift.tutup', compact('shift', 'ringkasan'));
    }

    /**
     * Proses tutup shift (POST) — input saldo akhir fisik + selisih dihitung sistem.
     */
    public function tutup(Request $request)
    {
        $request->validate([
            'saldo_akhir_fisik' => 'required|numeric|min:0',
            'catatan'           => 'nullable|string|max:500',
        ], [
            'saldo_akhir_fisik.required' => 'Saldo akhir fisik wajib diisi.',
            'saldo_akhir_fisik.numeric'  => 'Saldo akhir fisik harus berupa angka.',
            'saldo_akhir_fisik.min'      => 'Saldo akhir fisik tidak boleh negatif.',
            'catatan.max'                => 'Catatan maksimal 500 karakter.',
        ]);

        $shift = $this->shiftService->shiftAktif(Auth::id());
        if ($shift === null) {
            return back()->with('error', 'Tidak ada shift aktif untuk ditutup.');
        }

        try {
            $tutup = $this->shiftService->tutupShift(
                shift: $shift,
                saldoAkhirFisik: (float) $request->saldo_akhir_fisik,
                catatan: $request->catatan
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $selisihText = ($tutup->selisih >= 0 ? '+' : '') . number_format($tutup->selisih, 0, ',', '.');
        return redirect()->route('shift.riwayat')
            ->with('success', 'Shift ditutup. Saldo sistem Rp ' . number_format($tutup->saldo_akhir_sistem, 0, ',', '.') . ', fisik Rp ' . number_format($tutup->saldo_akhir_fisik, 0, ',', '.') . ', selisih Rp ' . $selisihText . '.');
    }

    /**
     * Riwayat shift — admin lihat semua, kasir hanya shift miliknya.
     */
    public function riwayat(Request $request)
    {
        $query = ShiftKasir::with('user')->orderByDesc('jam_buka');

        if (Auth::user()->role === 'kasir') {
            $query->where('user_id', Auth::id());
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_akhir);
        }

        $shifts = $query->paginate(20)->withQueryString();
        $users  = \App\Models\User::whereIn('role', ['kasir', 'admin'])->orderBy('name')->get();

        return view('shift.riwayat', compact('shifts', 'users'));
    }
}