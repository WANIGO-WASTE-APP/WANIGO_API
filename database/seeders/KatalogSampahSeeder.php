<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KatalogSampah;
use App\Models\BankSampah;
use App\Models\SubKategoriSampah;

class KatalogSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankSampahList = BankSampah::all();

        if ($bankSampahList->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada bank sampah. Jalankan BankSampahSeeder terlebih dahulu!');
            return;
        }

        $count = 0;
        foreach ($bankSampahList as $bankSampah) {
            // Get sub kategori for this specific bank sampah
            $subKategoriList = SubKategoriSampah::where('bank_sampah_id', $bankSampah->id)->get();

            if ($subKategoriList->isEmpty()) {
                $this->command->warn("⚠️  Bank sampah '{$bankSampah->nama_bank_sampah}' belum memiliki sub kategori.");
                continue;
            }

            // Harga standar untuk sub kategori umum (dalam Rupiah)
            $hargaKering = 2000; // Sampah kering
            $hargaBasah = 500;   // Sampah basah

            foreach ($subKategoriList as $subKategori) {
                // Tentukan harga berdasarkan kategori
                $hargaDasar = $subKategori->kategori_sampah_id == 1 ? $hargaKering : $hargaBasah;
                
                // Variasi harga ±15% dari harga standar
                $variasi = rand(-15, 15) / 100; // -15% sampai +15%
                $hargaFinal = $hargaDasar + ($hargaDasar * $variasi);
                
                KatalogSampah::create([
                    'bank_sampah_id' => $bankSampah->id,
                    'sub_kategori_sampah_id' => $subKategori->id,
                    'nama_sampah' => $subKategori->nama_sub_kategori,
                    'harga_per_kg' => round($hargaFinal, 0),
                    'satuan' => 'kg',
                    'deskripsi' => "Harga {$subKategori->nama_sub_kategori} di {$bankSampah->nama_bank_sampah}",
                    'status' => 'tersedia',
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            $this->command->info("✅ {$count} Katalog Sampah berhasil di-seed!");
            $this->command->info("   {$bankSampahList->count()} bank sampah dengan sub kategori masing-masing");
        } else {
            $this->command->warn('⚠️  Tidak ada katalog yang dibuat. Pastikan SubKategoriSampahSeeder sudah dijalankan!');
        }
    }
}
