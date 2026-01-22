<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Phase 2: Backfill Data
     * - Generate slugs from nama_sub_kategori using Str::slug()
     * - Handle slug collisions with numeric suffixes
     * - Copy status_aktif values to is_active
     * - Convert kategori_sampah_id FK values to kategori_sampah tinyint
     * 
     * Requirements: 10.2, 10.3, 10.4, 11.6
     */
    public function up(): void
    {
        // Use transaction to ensure atomicity
        DB::transaction(function () {
            // Get all sub_kategori_sampah records
            $subKategoris = DB::table('sub_kategori_sampah')->get();
            
            foreach ($subKategoris as $subKategori) {
                // 1. Generate slug from nama_sub_kategori
                $slug = Str::slug($subKategori->nama_sub_kategori);
                
                // 2. Handle slug collisions with numeric suffixes
                $counter = 1;
                $originalSlug = $slug;
                
                // Check for existing slugs within same bank_sampah_id and kategori_sampah
                // Note: We need to get kategori_sampah value first to check collisions properly
                $kategori = DB::table('kategori_sampah')
                    ->where('id', $subKategori->kategori_sampah_id)
                    ->first();
                
                // Map kategori_sampah_id to tinyint (1=kering, 2=basah -> 0=kering, 1=basah)
                $kategoriSampahValue = ($kategori->kode_kategori === 'kering') ? 0 : 1;
                
                // Check for slug collisions within same bank and kategori
                while (DB::table('sub_kategori_sampah')
                    ->where('bank_sampah_id', $subKategori->bank_sampah_id)
                    ->where('kategori_sampah', $kategoriSampahValue)
                    ->where('slug', $slug)
                    ->where('id', '!=', $subKategori->id)
                    ->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                
                // 3. Update the record with backfilled data
                DB::table('sub_kategori_sampah')
                    ->where('id', $subKategori->id)
                    ->update([
                        'slug' => $slug,
                        'is_active' => (bool) $subKategori->status_aktif,
                        'kategori_sampah' => $kategoriSampahValue,
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Rollback: Set new columns back to NULL
     */
    public function down(): void
    {
        DB::table('sub_kategori_sampah')->update([
            'slug' => null,
            'is_active' => true, // Reset to default
            'kategori_sampah' => null,
        ]);
    }
};
