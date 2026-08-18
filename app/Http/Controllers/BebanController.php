<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Beban;
use App\Services\JurnalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BebanController extends Controller
{
    public function __construct(private JurnalService $jurnalService) {}

    public function index()
    {
        $beBans = Beban::with('akun', 'user')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15);

        $akuns = Akun::where('tipe', 'beban')
            ->where('is_manual_entry', true)
            ->orderBy('kode_akun')
            ->get();

        return view('beban.index', compact('beBans', 'akuns'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'akun_id'    => 'required|exists:akun,id',
            'nominal'    => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'tanggal.required'    => 'Tanggal beban wajib diisi.',
            'tanggal.date'        => 'Format tanggal tidak valid.',
            'akun_id.required'    => 'Akun beban wajib dipilih.',
            'akun_id.exists'      => 'Akun beban tidak ditemukan.',
            'nominal.required'    => 'Nominal beban wajib diisi.',
            'nominal.numeric'     => 'Nominal harus berupa angka.',
            'nominal.min'         => 'Nominal tidak boleh negatif.',
            'keterangan.max'      => 'Keterangan maksimal 255 karakter.',
        ]);

        $beban = DB::transaction(function () use ($validated) {
            $beban = Beban::create([
                'tanggal'    => $validated['tanggal'],
                'akun_id'    => $validated['akun_id'],
                'nominal'    => $validated['nominal'],
                'keterangan' => $validated['keterangan'] ?? null,
                'user_id'    => auth()->id(),
            ]);

            $this->jurnalService->catatBeban($beban);

            return $beban;
        });

        return redirect()->route('beban.index')
            ->with('success', 'Beban Rp ' . number_format($beban->nominal, 0, ',', '.') . ' berhasil dicatat.');
    }
}
