<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Administrator KELAR',
            'username' => 'admin',
            'email' => 'admin@pos.com',
            'password' => Hash::make('admin123'), // Password default yang disandikan (enkripsi)
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}