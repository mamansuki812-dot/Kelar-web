<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kategori')->insert([
            ['nama_kategori' => 'Makanan Ringan', 'deskripsi' => 'Snack, biskuit, dan sejenisnya', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Minuman', 'deskripsi' => 'Air mineral, jus, kopi kemasan', 'created_at' => now(), 'updated_at' => now()],
            ['nama_kategori' => 'Kebutuhan Harian', 'deskripsi' => 'Sabun, sampo, pasta gigi', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}