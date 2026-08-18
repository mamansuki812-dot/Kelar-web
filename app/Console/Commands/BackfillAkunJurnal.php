<?php

namespace App\Console\Commands;

use App\Models\Akun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillAkunJurnal extends Command
{
    /**
     * Nama & deskripsi perintah.
     */
    protected $signature = 'jurnal:backfill-akun';

    protected $description = 'Isi akun_debit_id/akun_kredit_id pada baris jurnal lama berdasarkan pencocokan string akun_debit/akun_kredit ke tabel akun. Hanya mengisi kolom yang masih NULL; string yang tidak cocok tidak dipaksa, dilaporkan.';

    /**
     * Jalankan perintah.
     */
    public function handle(): int
    {
        $map = Akun::all()->mapWithKeys(fn ($a) => [mb_strtolower(trim($a->nama_akun)) => $a->id])->toArray();

        $rows = DB::table('jurnal')
            ->whereNull('akun_debit_id')
            ->orWhereNull('akun_kredit_id')
            ->get();

        $unmatched = [];
        $updated = 0;

        foreach ($rows as $row) {
            $updates = [];

            if ($row->akun_debit_id === null) {
                $id = $map[mb_strtolower(trim($row->akun_debit))] ?? null;
                if ($id === null) {
                    $unmatched[] = ['kolom' => 'akun_debit', 'nilai' => $row->akun_debit];
                } else {
                    $updates['akun_debit_id'] = $id;
                }
            }

            if ($row->akun_kredit_id === null) {
                $id = $map[mb_strtolower(trim($row->akun_kredit))] ?? null;
                if ($id === null) {
                    $unmatched[] = ['kolom' => 'akun_kredit', 'nilai' => $row->akun_kredit];
                } else {
                    $updates['akun_kredit_id'] = $id;
                }
            }

            if ($updates) {
                DB::table('jurnal')->where('id', $row->id)->update($updates);
                $updated++;
            }
        }

        $this->info("Baris jurnal yang diproses: {$rows->count()}");
        $this->info("Baris yang diperbarui: {$updated}");

        if ($unmatched) {
            $this->error('String akun yang TIDAK cocok ke tabel akun (tidak dipaksa, butuh keputusan mapping):');
            foreach ($unmatched as $u) {
                $this->line("  - [{$u['kolom']}] \"{$u['nilai']}\"");
            }
            return self::FAILURE;
        }

        $this->info('Semua string akun cocok ke tabel akun.');
        return self::SUCCESS;
    }
}
