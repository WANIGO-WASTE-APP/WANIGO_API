<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SubKategoriSampahBackfillMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the backfill migration generates slugs correctly
     *
     * @test
     */
    public function test_backfill_migration_generates_slugs_from_nama_sub_kategori()
    {
        // Create test data
        $bankSampahId = DB::table('bank_sampah')->insertGetId([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat' => 'Test Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test sub_kategori_sampah records
        DB::table('sub_kategori_sampah')->insert([
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 1, // kering
                'nama_sub_kategori' => 'Botol Plastik',
                'kode_sub_kategori' => 'BP-001',
                'status_aktif' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 2, // basah
                'nama_sub_kategori' => 'Sisa Makanan',
                'kode_sub_kategori' => 'SM-001',
                'status_aktif' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Run the backfill migration
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_01_22_071238_backfill_sub_kategori_sampah_data_phase2.php']);

        // Verify slugs were generated
        $records = DB::table('sub_kategori_sampah')
            ->where('bank_sampah_id', $bankSampahId)
            ->get();

        $this->assertEquals('botol-plastik', $records[0]->slug);
        $this->assertEquals('sisa-makanan', $records[1]->slug);
    }

    /**
     * Test that the backfill migration handles slug collisions with numeric suffixes
     *
     * @test
     */
    public function test_backfill_migration_handles_slug_collisions()
    {
        // Create test data
        $bankSampahId = DB::table('bank_sampah')->insertGetId([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat' => 'Test Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test sub_kategori_sampah records with same name in same category
        DB::table('sub_kategori_sampah')->insert([
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 1, // kering
                'nama_sub_kategori' => 'Plastik',
                'kode_sub_kategori' => 'P-001',
                'status_aktif' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 1, // kering (same category)
                'nama_sub_kategori' => 'Plastik', // same name
                'kode_sub_kategori' => 'P-002',
                'status_aktif' => 1,
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Run the backfill migration
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_01_22_071238_backfill_sub_kategori_sampah_data_phase2.php']);

        // Verify slug collision was handled with numeric suffix
        $records = DB::table('sub_kategori_sampah')
            ->where('bank_sampah_id', $bankSampahId)
            ->orderBy('id')
            ->get();

        $this->assertEquals('plastik', $records[0]->slug);
        $this->assertEquals('plastik-1', $records[1]->slug);
    }

    /**
     * Test that the backfill migration copies status_aktif to is_active
     *
     * @test
     */
    public function test_backfill_migration_copies_status_aktif_to_is_active()
    {
        // Create test data
        $bankSampahId = DB::table('bank_sampah')->insertGetId([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat' => 'Test Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test sub_kategori_sampah records with different status_aktif values
        DB::table('sub_kategori_sampah')->insert([
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 1,
                'nama_sub_kategori' => 'Active Item',
                'kode_sub_kategori' => 'AI-001',
                'status_aktif' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 1,
                'nama_sub_kategori' => 'Inactive Item',
                'kode_sub_kategori' => 'II-001',
                'status_aktif' => 0,
                'urutan' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Run the backfill migration
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_01_22_071238_backfill_sub_kategori_sampah_data_phase2.php']);

        // Verify is_active matches status_aktif
        $records = DB::table('sub_kategori_sampah')
            ->where('bank_sampah_id', $bankSampahId)
            ->orderBy('id')
            ->get();

        $this->assertTrue((bool) $records[0]->is_active);
        $this->assertFalse((bool) $records[1]->is_active);
    }

    /**
     * Test that the backfill migration converts kategori_sampah_id to kategori_sampah tinyint
     *
     * @test
     */
    public function test_backfill_migration_converts_kategori_sampah_id_to_tinyint()
    {
        // Create test data
        $bankSampahId = DB::table('bank_sampah')->insertGetId([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat' => 'Test Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test sub_kategori_sampah records
        DB::table('sub_kategori_sampah')->insert([
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 1, // kering
                'nama_sub_kategori' => 'Kertas',
                'kode_sub_kategori' => 'K-001',
                'status_aktif' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 2, // basah
                'nama_sub_kategori' => 'Organik',
                'kode_sub_kategori' => 'O-001',
                'status_aktif' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Run the backfill migration
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_01_22_071238_backfill_sub_kategori_sampah_data_phase2.php']);

        // Verify kategori_sampah values (1=kering->0, 2=basah->1)
        $records = DB::table('sub_kategori_sampah')
            ->where('bank_sampah_id', $bankSampahId)
            ->orderBy('id')
            ->get();

        $this->assertEquals(0, $records[0]->kategori_sampah); // kering
        $this->assertEquals(1, $records[1]->kategori_sampah); // basah
    }

    /**
     * Test that slug collisions only occur within same bank and kategori scope
     *
     * @test
     */
    public function test_slug_collisions_are_scoped_to_bank_and_kategori()
    {
        // Create test data
        $bankSampahId = DB::table('bank_sampah')->insertGetId([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat' => 'Test Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test sub_kategori_sampah records with same name but different categories
        DB::table('sub_kategori_sampah')->insert([
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 1, // kering
                'nama_sub_kategori' => 'Plastik',
                'kode_sub_kategori' => 'P-K-001',
                'status_aktif' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_sampah_id' => $bankSampahId,
                'kategori_sampah_id' => 2, // basah (different category)
                'nama_sub_kategori' => 'Plastik', // same name
                'kode_sub_kategori' => 'P-B-001',
                'status_aktif' => 1,
                'urutan' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Run the backfill migration
        Artisan::call('migrate', ['--path' => 'database/migrations/2026_01_22_071238_backfill_sub_kategori_sampah_data_phase2.php']);

        // Verify both have same slug (no collision because different categories)
        $records = DB::table('sub_kategori_sampah')
            ->where('bank_sampah_id', $bankSampahId)
            ->orderBy('kategori_sampah_id')
            ->get();

        $this->assertEquals('plastik', $records[0]->slug);
        $this->assertEquals('plastik', $records[1]->slug); // Same slug is OK in different category
    }
}
