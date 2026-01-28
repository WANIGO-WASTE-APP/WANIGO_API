<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    // DatabaseSeeder.php
    public function run()
    {
        $this->call([
            TipeBankSampahSeeder::class,
            KategoriSampahSeeder::class,
            BankSampahSeeder::class,              // ← Bank sampah harus dibuat dulu
            SubKategoriSampahSeeder::class,       // ← Baru sub kategori (per bank)
            JamOperasionalBankSampahSeeder::class,
            KatalogSampahSeeder::class,
            JadwalSampahSeeder::class,            // ← Jadwal sampah
        ]);
    }
}
