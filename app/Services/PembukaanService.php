<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\Produk;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Jurnal pembukaan modal / setup awal (cutover pembukuan formal).
 *
 * Sumber tunggal (single source of truth) yang dipakai bersama oleh command
 * `akuntansi:jurnal-pembukaan` dan form web "Setup Awal" di Pengaturan.
 *
 * Aturan pengaman bertingkat (idempoten):
 *  1. menolak jika sudah ada kode_jurnal LIKE 'JR-OPENING-%';
 *  2. menolak jika tabel jurnal masih berisi baris;
 *  3. menolak jika akun "Modal Pemilik" belum ada;
 *  4. menolak jika selisih persediaan fisik vs ledger bernilai negatif
 *     (tidak pernah dipaksa menjadi kredit terbalik).
 *
 * Entry yang dibuat:
 *  - Debit Persediaan Barang / Kredit Modal Pemilik (jika nominal > 0);
 *  - Debit Kas / Kredit Modal Pemilik (jika kas > 0).
 */
class PembukaanService
{
    public function __construct(private AkuntansiService $akuntansi) {}

    /**
     * Buat jurnal pembukaan dalam satu transaksi DB.
     *
     * @param float      $kas     Nilai kas fisik (0 = belum dicatat / estimasi).
     * @param string|null $tanggal Tanggal cutover Y-m-d; null = hari ini.
     *
     * @return array{ tanggal: string, nilai_fisik: float, saldo_ledger: float,
     *                nominal: float, kas: float, entry_persediaan: bool, entry_kas: bool }
     *
     * @throws \RuntimeException Bila salah satu guard bertingkat gagal.
     */
    public function buatPembukaan(float $kas, ?string $tanggal = null): array
    {
        if (Jurnal::where('kode_jurnal', 'like', 'JR-OPENING-%')->exists()) {
            throw new \RuntimeException('Entry pembukaan (JR-OPENING-*) sudah ada. Jangan menjalankan ulang; buat entry koreksi terpisah.');
        }

        $jumlahJurnal = Jurnal::count();
        if ($jumlahJurnal > 0) {
            throw new \RuntimeException("Tabel jurnal masih berisi {$jumlahJurnal} baris. Data uji wajib dibersihkan dulu (kebijakan cutover) sebelum jurnal pembukaan dibuat.");
        }

        $modalPemilikId = Akun::resolveIdByName('Modal Pemilik');
        if ($modalPemilikId === null) {
            throw new \RuntimeException('Akun "Modal Pemilik" (3-3000) belum ada. Jalankan migration terlebih dahulu.');
        }

        $tanggalCutover = $tanggal ? Carbon::parse($tanggal) : Carbon::now();
        $tanggalPosisi  = $tanggalCutover->toDateString();
        $kas            = max(0.0, (float) $kas);

        return DB::transaction(function () use ($modalPemilikId, $tanggalCutover, $tanggalPosisi, $kas) {
            $nilaiFisik = (float) Produk::where('is_active', true)
                ->get()
                ->sum(fn (Produk $p) => (float) $p->stok * (float) $p->harga_beli);

            $saldoLedger = $this->akuntansi->saldoAkun('Persediaan Barang', $tanggalPosisi);

            $nominal = $nilaiFisik - $saldoLedger;

            if ($nominal < 0) {
                throw new \RuntimeException('Selisih persediaan NEGATIF (fisik lebih kecil dari ledger) — tidak dieksekusi dan tidak dipaksa menjadi kredit terbalik. Laporkan selisih ini ke pemilik untuk arahan.');
            }

            $entryPersediaan = false;
            if ($nominal > 0) {
                Jurnal::create([
                    'tanggal'        => $tanggalPosisi,
                    'kode_jurnal'    => 'JR-OPENING-' . $tanggalCutover->format('Ymd'),
                    'transaksi_id'   => null,
                    'akun_debit'     => 'Persediaan Barang',
                    'akun_kredit'    => 'Modal Pemilik',
                    'akun_debit_id'  => Akun::resolveIdByName('Persediaan Barang'),
                    'akun_kredit_id' => $modalPemilikId,
                    'nominal'        => $nominal,
                    'keterangan'     => 'Jurnal pembukaan modal - persediaan fisik (cutover ' . $tanggalPosisi . ')',
                ]);
                $entryPersediaan = true;
            }

            $entryKas = false;
            if ($kas > 0) {
                Jurnal::create([
                    'tanggal'        => $tanggalPosisi,
                    'kode_jurnal'    => 'JR-OPENING-' . $tanggalCutover->format('Ymd'),
                    'transaksi_id'   => null,
                    'akun_debit'     => 'Kas',
                    'akun_kredit'    => 'Modal Pemilik',
                    'akun_debit_id'  => Akun::resolveIdByName('Kas'),
                    'akun_kredit_id' => $modalPemilikId,
                    'nominal'        => $kas,
                    'keterangan'     => 'Jurnal pembukaan awal - kas (cutover ' . $tanggalPosisi . ')',
                ]);
                $entryKas = true;
            }

            return [
                'tanggal'          => $tanggalPosisi,
                'nilai_fisik'      => $nilaiFisik,
                'saldo_ledger'     => $saldoLedger,
                'nominal'          => $nominal,
                'kas'              => $kas,
                'entry_persediaan' => $entryPersediaan,
                'entry_kas'        => $entryKas,
            ];
        });
    }
}