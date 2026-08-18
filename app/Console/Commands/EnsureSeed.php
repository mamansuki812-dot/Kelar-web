<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnsureSeed extends Command
{
    protected $signature = 'app:ensure-seed';

    protected $description = 'Menjalankan seeder master data hanya jika database masih kosong (aman dipanggil tiap deploy)';

    public function handle(): int
    {
        if (DB::table('users')->where('username', 'admin')->exists()) {
            $this->info('Data master sudah ada, seeder dilewati.');
            return self::SUCCESS;
        }

        $this->call('db:seed', ['--force' => true]);
        $this->info('Seeder master data selesai dijalankan.');
        return self::SUCCESS;
    }
}
