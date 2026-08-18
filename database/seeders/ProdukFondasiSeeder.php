<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/**
 * Fase 1 (revisi dosen) — produk fondasi.
 *
 * - Kategori "Bahan Habis Pakai": bahan kemasan/suplemen yang nanti (Fase 3)
 *   menjadi komponen BOM paket produk. Sekarang cukup ada sebagai master data
 *   agar histori dummy transaksi (Fase 1 langkah 5) bisa menjualnya realistis.
 * - Produk dengan tanggal_kadaluarsa mendekati sekarang: untuk fitur bundling
 *   produk mendekati kadaluarsa di Fase 3.
 *
 * Idempoten: updateOrInsert berbasis kode_produk (unik) & firstOrCreate kategori.
 */
class ProdukFondasiSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = Kategori::firstOrCreate(
            ['nama_kategori' => 'Bahan Habis Pakai'],
            ['deskripsi' => 'Kemasan dan bahan pendukung produk (fondasi komponen paket Fase 3)', 'created_at' => now(), 'updated_at' => now()]
        );

        $kategoriMakanan = Kategori::where('nama_kategori', 'Makanan Ringan')->first();

        $produk = [
            // Bahan Habis Pakai — nanti jadi komponen BOM paket di Fase 3.
            [
                'kode_produk' => 'BHP-001',
                'nama_produk' => 'Kardus Paket Kecil',
                'kategori_id' => $kategori->id,
                'harga_beli' => 1500,
                'harga_jual' => 2500,
                'stok' => 100,
                'stok_minimum' => 10,
                'satuan' => 'pcs',
                'is_active' => true,
                'tanggal_kadaluarsa' => null,
            ],
            [
                'kode_produk' => 'BHP-002',
                'nama_produk' => 'Plastik Kresek',
                'kategori_id' => $kategori->id,
                'harga_beli' => 500,
                'harga_jual' => 1000,
                'stok' => 150,
                'stok_minimum' => 20,
                'satuan' => 'pcs',
                'is_active' => true,
                'tanggal_kadaluarsa' => null,
            ],
            [
                'kode_produk' => 'BHP-003',
                'nama_produk' => 'Saus Sachet',
                'kategori_id' => $kategori->id,
                'harga_beli' => 1000,
                'harga_jual' => 2000,
                'stok' => 200,
                'stok_minimum' => 30,
                'satuan' => 'sachet',
                'is_active' => true,
                'tanggal_kadaluarsa' => Carbon::parse('2026-10-01'),
            ],
            [
                'kode_produk' => 'BHP-004',
                'nama_produk' => 'Karet Gelang',
                'kategori_id' => $kategori->id,
                'harga_beli' => 200,
                'harga_jual' => 500,
                'stok' => 300,
                'stok_minimum' => 30,
                'satuan' => 'pcs',
                'is_active' => true,
                'tanggal_kadaluarsa' => null,
            ],

            // Produk mendekati kadaluarsa (fondasi bundling Fase 3).
            [
                'kode_produk' => 'PRD-KAD-001',
                'nama_produk' => 'Biskuit Coklat Kemasan Kecil',
                'kategori_id' => $kategoriMakanan?->id ?? $kategori->id,
                'harga_beli' => 8000,
                'harga_jual' => 12000,
                'stok' => 40,
                'stok_minimum' => 5,
                'satuan' => 'pcs',
                'is_active' => true,
                'tanggal_kadaluarsa' => Carbon::parse('2026-08-15'),
            ],
            [
                'kode_produk' => 'PRD-KAD-002',
                'nama_produk' => 'Susu UHT Kecil (200ml)',
                'kategori_id' => $kategoriMakanan?->id ?? $kategori->id,
                'harga_beli' => 4500,
                'harga_jual' => 7000,
                'stok' => 30,
                'stok_minimum' => 5,
                'satuan' => 'pcs',
                'is_active' => true,
                'tanggal_kadaluarsa' => Carbon::parse('2026-08-20'),
            ],
            [
                'kode_produk' => 'PRD-KAD-003',
                'nama_produk' => 'Roti Gandum Kemasan',
                'kategori_id' => $kategoriMakanan?->id ?? $kategori->id,
                'harga_beli' => 9500,
                'harga_jual' => 14000,
                'stok' => 25,
                'stok_minimum' => 5,
                'satuan' => 'pcs',
                'is_active' => true,
                'tanggal_kadaluarsa' => Carbon::parse('2026-08-28'),
            ],
        ];

        foreach ($produk as $item) {
            DB::table('produk')->updateOrInsert(
                ['kode_produk' => $item['kode_produk']],
                array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                    'tanggal_kadaluarsa' => $item['tanggal_kadaluarsa']?->toDateString(),
                ])
            );
        }
    }
}
