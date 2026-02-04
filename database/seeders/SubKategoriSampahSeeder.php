<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubKategoriSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeds predefined waste sub-categories for Sampah Kering (Dry Waste):
     * - 32 sub-categories organized into 6 groups
     * - Grup Kertas (5), Grup Botol (9), Grup Bak (3), Grup Logam (3), Grup Plastik (4), Grup Lainnya (8)
     * 
     * Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 6.3, 6.4
     */
    public function run(): void
    {
        $bankSampahId = 1;
        $kategoriSampah = 0; // Sampah Kering
        
        // Get kategori_sampah_id for backward compatibility
        $kategoriSampahId = DB::table('kategori_sampah')
            ->where('nama_kategori', 'Sampah Kering')
            ->value('id');
        
        if (!$kategoriSampahId) {
            $this->command->error('Kategori Sampah "Sampah Kering" not found. Please run KategoriSampahSeeder first.');
            return;
        }
        
        // Check if the new data structure already exists (32 sub-categories)
        $existingCount = DB::table('sub_kategori_sampah')
            ->where('bank_sampah_id', $bankSampahId)
            ->where('kategori_sampah', $kategoriSampah)
            ->count();
        
        // If we have exactly 32 records, assume the new structure is already seeded
        if ($existingCount === 32) {
            $this->command->info("Sub kategori sampah data already seeded (32 records found).");
            return;
        }
        
        // If we have old data (not 32 records), clear it and reseed with new structure
        if ($existingCount > 0 && $existingCount !== 32) {
            $this->command->warn("Found {$existingCount} old sub-category records. Clearing and reseeding with new structure...");
            
            // First, set all katalog_sampah.sub_kategori_sampah_id to NULL for this bank and category
            // (old column name) to avoid foreign key constraint violations
            DB::table('katalog_sampah')
                ->whereIn('sub_kategori_sampah_id', function($query) use ($bankSampahId, $kategoriSampah) {
                    $query->select('id')
                        ->from('sub_kategori_sampah')
                        ->where('bank_sampah_id', $bankSampahId)
                        ->where('kategori_sampah', $kategoriSampah);
                })
                ->update(['sub_kategori_sampah_id' => null]);
            
            $this->command->info("Cleared katalog_sampah references to old sub-categories.");
            
            // Now delete old sub-categories for this bank and category
            DB::table('sub_kategori_sampah')
                ->where('bank_sampah_id', $bankSampahId)
                ->where('kategori_sampah', $kategoriSampah)
                ->delete();
            
            $this->command->info("Old data cleared.");
        }
        
        // Define sub-category groups data structure
        $subKategoriGroups = [
            'Grup Kertas' => ['Kardus', 'HVS / Kertas Putih', 'Buku', 'Koran Buram', 'Duplek'],
            'Grup Botol' => [
                'Botol BM', 
                'Botol PET', 
                'Botol Kotor', 
                'Botol Warna', 
                'Botol Campur Bersih', 
                'Botol Campur Kotor', 
                'Botol Beling', 
                'Botol Keras', 
                'Botol Minyak'
            ],
            'Grup Bak' => ['Bak Campur', 'Bak Keras', 'Bak Plastik'],
            'Grup Logam' => ['Aluminium', 'Kaleng', 'Besi'],
            'Grup Plastik' => ['Blowing', 'Plastik', 'Tempat Makan', 'Gembos'],
            'Grup Lainnya' => [
                'Gelas Mineral Bersih', 
                'Gelas Mineral Kotor', 
                'Gelas Warna Warni', 
                'Tutup Botol', 
                'Galon Le Mineral', 
                'Jelantah', 
                'Kabel Elektronik', 
                'Grabang'
            ]
        ];
        
        $urutan = 1;
        $subKategoriMap = [];
        $grupCounter = 1;
        
        // Insert sub-categories and store IDs in map
        foreach ($subKategoriGroups as $grupName => $items) {
            $itemCounter = 1;
            foreach ($items as $namaSubKategori) {
                // Generate slug using Str::slug()
                $slug = Str::slug($namaSubKategori);
                
                // Generate kode_sub_kategori (e.g., SK-001, SK-002, etc.)
                $kodeSubKategori = 'SK-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);
                
                // Use insertGetId() to capture sub-category ID
                $subKategoriId = DB::table('sub_kategori_sampah')->insertGetId([
                    'bank_sampah_id' => $bankSampahId,
                    'kategori_sampah_id' => $kategoriSampahId,
                    'kategori_sampah' => $kategoriSampah,
                    'nama_sub_kategori' => $namaSubKategori,
                    'kode_sub_kategori' => $kodeSubKategori,
                    'slug' => $slug,
                    'deskripsi' => null,
                    'icon' => null,
                    'warna' => null,
                    'urutan' => $urutan,
                    'status_aktif' => true,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Store sub-category ID in map for later katalog item mapping
                $subKategoriMap[$namaSubKategori] = $subKategoriId;
                $urutan++;
                $itemCounter++;
            }
            $grupCounter++;
        }
        
        $this->command->info("Sub kategori sampah seeded successfully.");
        $this->command->info("Created: " . count($subKategoriMap) . " sub-categories (urutan 1-" . ($urutan - 1) . ")");
        
        // Map katalog_sampah items to sub-categories (Task 3)
        $this->mapKatalogSampahToSubKategori($subKategoriMap);
    }
    
    /**
     * Map katalog_sampah items to sub_kategori_sampah based on name matching.
     * 
     * Uses longest-match algorithm for accurate item-to-subcategory mapping.
     * Requirements: 4.1, 4.2, 4.3, 4.4
     * 
     * @param array $subKategoriMap Map of sub-category names to IDs
     * @return void
     */
    private function mapKatalogSampahToSubKategori(array $subKategoriMap): void
    {
        // Query katalog_sampah items where kategori_sampah = 0 (Sampah Kering)
        $katalogItems = DB::table('katalog_sampah')
            ->where('kategori_sampah', 0)
            ->get();
        
        if ($katalogItems->isEmpty()) {
            $this->command->warn('No katalog sampah items found to map.');
            return;
        }
        
        $mappedCount = 0;
        $unmappedCount = 0;
        
        foreach ($katalogItems as $item) {
            $bestMatch = null;
            $longestMatchLength = 0;
            
            // Implement longest-match algorithm
            foreach ($subKategoriMap as $subKategoriName => $subKategoriId) {
                // Use case-insensitive matching with stripos()
                if (stripos($item->nama_item_sampah, $subKategoriName) !== false) {
                    $matchLength = strlen($subKategoriName);
                    
                    // Use the most specific match (longest matching substring)
                    if ($matchLength > $longestMatchLength) {
                        $bestMatch = $subKategoriId;
                        $longestMatchLength = $matchLength;
                    }
                }
            }
            
            // Update katalog_sampah.sub_kategori_id for matched items
            if ($bestMatch !== null) {
                DB::table('katalog_sampah')
                    ->where('id', $item->id)
                    ->update(['sub_kategori_id' => $bestMatch]);
                $mappedCount++;
            } else {
                // Leave sub_kategori_id as NULL for non-matching items
                $unmappedCount++;
            }
        }
        
        $this->command->info("Katalog sampah mapping completed.");
        $this->command->info("Mapped: {$mappedCount} items");
        $this->command->info("Unmapped: {$unmappedCount} items");
    }
}