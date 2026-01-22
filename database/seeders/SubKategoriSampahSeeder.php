<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubKategoriSampah;
use App\Models\BankSampah;
use App\Models\KategoriSampah;

class SubKategoriSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds comprehensive master data for waste sub-categories:
     * - 7 sub-categories for Sampah Kering (Dry Waste)
     * - 3 sub-categories for Sampah Basah (Wet Waste)
     * 
     * Implements idempotence using unique constraint check.
     */
    public function run(): void
    {
        // Get all bank sampah
        $bankSampahs = BankSampah::all();
        
        if ($bankSampahs->isEmpty()) {
            $this->command->warn('No bank sampah found. Please run BankSampahSeeder first.');
            return;
        }
        
        // Get kategori sampah for backward compatibility
        $kategoriKering = KategoriSampah::getKering();
        $kategoriBasah = KategoriSampah::getBasah();
        
        if (!$kategoriKering || !$kategoriBasah) {
            $this->command->error('Kategori sampah not found. Please run KategoriSampahSeeder first.');
            return;
        }
        
        // Master data for sub-categories
        $subKategoris = [
            // Sampah Kering (kategori_sampah = 0)
            [
                'kategori_sampah' => 0,
                'kategori_sampah_id' => $kategoriKering->id, // For backward compatibility
                'kode_sub_kategori' => 'SK-001',
                'nama_sub_kategori' => 'Kertas',
                'slug' => 'kertas',
                'deskripsi' => 'Kertas bekas, koran, majalah, buku',
                'icon' => 'paper',
                'warna' => '#8BC34A',
                'urutan' => 1,
            ],
            [
                'kategori_sampah' => 0,
                'kategori_sampah_id' => $kategoriKering->id,
                'kode_sub_kategori' => 'SK-002',
                'nama_sub_kategori' => 'Botol Plastik',
                'slug' => 'botol-plastik',
                'deskripsi' => 'Botol plastik PET, HDPE',
                'icon' => 'bottle',
                'warna' => '#2196F3',
                'urutan' => 2,
            ],
            [
                'kategori_sampah' => 0,
                'kategori_sampah_id' => $kategoriKering->id,
                'kode_sub_kategori' => 'SK-003',
                'nama_sub_kategori' => 'Plastik',
                'slug' => 'plastik',
                'deskripsi' => 'Plastik kemasan, kantong plastik',
                'icon' => 'plastic-bag',
                'warna' => '#03A9F4',
                'urutan' => 3,
            ],
            [
                'kategori_sampah' => 0,
                'kategori_sampah_id' => $kategoriKering->id,
                'kode_sub_kategori' => 'SK-004',
                'nama_sub_kategori' => 'Logam',
                'slug' => 'logam',
                'deskripsi' => 'Kaleng, aluminium, besi',
                'icon' => 'metal',
                'warna' => '#9E9E9E',
                'urutan' => 4,
            ],
            [
                'kategori_sampah' => 0,
                'kategori_sampah_id' => $kategoriKering->id,
                'kode_sub_kategori' => 'SK-005',
                'nama_sub_kategori' => 'Kaca',
                'slug' => 'kaca',
                'deskripsi' => 'Botol kaca, pecahan kaca',
                'icon' => 'glass',
                'warna' => '#00BCD4',
                'urutan' => 5,
            ],
            [
                'kategori_sampah' => 0,
                'kategori_sampah_id' => $kategoriKering->id,
                'kode_sub_kategori' => 'SK-006',
                'nama_sub_kategori' => 'Kardus',
                'slug' => 'kardus',
                'deskripsi' => 'Kardus bekas, karton',
                'icon' => 'cardboard',
                'warna' => '#795548',
                'urutan' => 6,
            ],
            [
                'kategori_sampah' => 0,
                'kategori_sampah_id' => $kategoriKering->id,
                'kode_sub_kategori' => 'SK-007',
                'nama_sub_kategori' => 'Elektronik',
                'slug' => 'elektronik',
                'deskripsi' => 'Barang elektronik bekas',
                'icon' => 'electronics',
                'warna' => '#607D8B',
                'urutan' => 7,
            ],
            // Sampah Basah (kategori_sampah = 1)
            [
                'kategori_sampah' => 1,
                'kategori_sampah_id' => $kategoriBasah->id,
                'kode_sub_kategori' => 'SB-001',
                'nama_sub_kategori' => 'Organik',
                'slug' => 'organik',
                'deskripsi' => 'Sampah organik umum',
                'icon' => 'organic',
                'warna' => '#4CAF50',
                'urutan' => 1,
            ],
            [
                'kategori_sampah' => 1,
                'kategori_sampah_id' => $kategoriBasah->id,
                'kode_sub_kategori' => 'SB-002',
                'nama_sub_kategori' => 'Sisa Makanan',
                'slug' => 'sisa-makanan',
                'deskripsi' => 'Sisa makanan, kulit buah',
                'icon' => 'food-waste',
                'warna' => '#8BC34A',
                'urutan' => 2,
            ],
            [
                'kategori_sampah' => 1,
                'kategori_sampah_id' => $kategoriBasah->id,
                'kode_sub_kategori' => 'SB-003',
                'nama_sub_kategori' => 'Daun',
                'slug' => 'daun',
                'deskripsi' => 'Daun kering, ranting',
                'icon' => 'leaf',
                'warna' => '#689F38',
                'urutan' => 3,
            ],
        ];
        
        // Create sub-categories for each bank sampah
        $createdCount = 0;
        $skippedCount = 0;
        
        foreach ($bankSampahs as $bank) {
            $this->command->info("Processing bank sampah: {$bank->nama_bank_sampah}");
            
            foreach ($subKategoris as $data) {
                // Check if already exists using unique constraint
                // (bank_sampah_id, kategori_sampah, slug)
                $exists = SubKategoriSampah::where('bank_sampah_id', $bank->id)
                    ->where('kategori_sampah', $data['kategori_sampah'])
                    ->where('slug', $data['slug'])
                    ->exists();
                
                if (!$exists) {
                    SubKategoriSampah::create(array_merge($data, [
                        'bank_sampah_id' => $bank->id,
                        'is_active' => true,
                    ]));
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            }
        }
        
        $this->command->info("Sub kategori sampah seeded successfully.");
        $this->command->info("Created: {$createdCount} sub-categories");
        $this->command->info("Skipped (already exists): {$skippedCount} sub-categories");
    }
}