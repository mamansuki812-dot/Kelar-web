<?php

namespace App\Console\Commands;

use App\Services\PembukaanService;
use Illuminate\Console\Command;

/**
 * Fase 4.5 — jurnal pembukaan modal (cutover pembukuan formal).
 *
 * Menulis entry pembukaan pertama (Debit Persediaan / Kredit Modal Pemilik)
 * sebesar selisih penyeimbang antara nilai persediaan fisik vs saldo ledger,
 * plus (opsional) Debit Kas / Kredit Modal Pemilik dari opsi --kas.
 *
 * Seluruh logika pengaman dipindah ke PembukaanService sehingga command CLI dan
 * halaman web "Setup Awal" berbagi satu sumber aturan yang sama.
 */
class JurnalPembukaanModal extends Command
{
    protected $signature = 'akuntansi:jurnal-pembukaan {--kas=0 : Nilai kas fisik (0 = BELUM dicatat / estimasi)} {--tanggal= : Tanggal cutover Y-m-d (default hari ini)}';

    protected $description = 'Buat jurnal pembukaan modal (Persediaan fisik + Kas) pada tanggal cutover. Menolak jalan jika jurnal belum bersih atau entry pembukaan sudah ada.';

    public function handle(PembukaanService $pembukaan): int
    {
        try {
            $hasil = $pembukaan->buatPembukaan(
                kas: (float) $this->option('kas'),
                tanggal: $this->option('tanggal') ?: null,
            );
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info("Tanggal cutover        : {$hasil['tanggal']}");
        $this->info('Nilai persediaan fisik : Rp ' . number_format($hasil['nilai_fisik'], 0, ',', '.') . ' (stok aktif x harga_beli)');
        $this->info('Saldo ledger Persediaan: Rp ' . number_format($hasil['saldo_ledger'], 0, ',', '.') . ' per ' . $hasil['tanggal']);
        $this->info('Selisih (nominal jurnal): Rp ' . number_format($hasil['nominal'], 0, ',', '.'));

        if ($hasil['entry_persediaan']) {
            $this->info('Entry pembukaan dibuat: Debit Persediaan Barang / Kredit Modal Pemilik Rp ' . number_format($hasil['nominal'], 0, ',', '.'));
        } else {
            $this->line('Persediaan sudah seimbang, tidak ada entry pembukaan yang dibuat.');
        }

        if ($hasil['entry_kas']) {
            $this->info('Entry kas awal dibuat: Debit Kas / Kredit Modal Pemilik Rp ' . number_format($hasil['kas'], 0, ',', '.'));
        } else {
            $this->warn('Kas awal BELUM dicatat (asumsi Rp0 sementara).');
            $this->line('Setelah pemilik cek fisik kas sesungguhnya, JANGAN jalankan ulang command ini — buat entry KOREKSI terpisah (Debit Kas / Kredit Modal Pemilik) dengan tanggal saat koreksi dilakukan, agar jejak audit tetap utuh (tidak menimpa entry pembukaan yang sudah ada).');
        }

        $this->info('Semua proses selesai dalam satu transaksi DB.');

        return self::SUCCESS;
    }
}