<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubKategoriSampahForeignKeyMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test environment with all prerequisite migrations
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Run all migrations up to Phase 3
        Artisan::call('migrate');
    }

    /**
     * Test that migration succeeds when all references are valid
     *
     * @test
     */
    public function test_migration_succeeds_with_valid_references()
    {
        // Create test data
        $bankSampahId = $this->createBankSampah();
        $subKategoriId = $this->createSubKategori($bankSampahId);
        
        // Create katalog with valid sub_kategori_sampah_id
        DB::table('katalog_sampah')->insert([
            'bank_sampah_id' => $bankSampahId,
            'sub_kategori_sampah_id' => $subKategoriId,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Test Item',
            'harga_per_kg' => 5000,
            'status_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Run Phase 4 migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php'
        ]);
        
        // Verify foreign key was added
        $this->assertTrue($this->foreignKeyExists('katalog_sampah', 'sub_kategori_sampah_id'));
    }

    /**
     * Test that migration succeeds when sub_kategori_sampah_id is null
     *
     * @test
     */
    public function test_migration_succeeds_with_null_references()
    {
        // Create test data
        $bankSampahId = $this->createBankSampah();
        
        // Create katalog with null sub_kategori_sampah_id (backward compatibility)
        DB::table('katalog_sampah')->insert([
            'bank_sampah_id' => $bankSampahId,
            'sub_kategori_sampah_id' => null,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Test Item',
            'harga_per_kg' => 5000,
            'status_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Run Phase 4 migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php'
        ]);
        
        // Verify foreign key was added
        $this->assertTrue($this->foreignKeyExists('katalog_sampah', 'sub_kategori_sampah_id'));
        
        // Verify null values are still allowed
        $katalog = DB::table('katalog_sampah')->first();
        $this->assertNull($katalog->sub_kategori_sampah_id);
    }

    /**
     * Test that migration fails when orphaned records exist
     *
     * @test
     */
    public function test_migration_fails_with_orphaned_records()
    {
        // Create test data
        $bankSampahId = $this->createBankSampah();
        
        // Create katalog with invalid sub_kategori_sampah_id (orphaned reference)
        DB::table('katalog_sampah')->insert([
            'bank_sampah_id' => $bankSampahId,
            'sub_kategori_sampah_id' => 99999, // Non-existent ID
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Orphaned Item',
            'harga_per_kg' => 5000,
            'status_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Expect exception when running Phase 4 migration
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Cannot add foreign key constraint/');
        $this->expectExceptionMessageMatches('/orphaned katalog_sampah records/');
        
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php'
        ]);
    }

    /**
     * Test that migration provides detailed error message for orphaned records
     *
     * @test
     */
    public function test_migration_provides_detailed_error_for_orphaned_records()
    {
        // Create test data
        $bankSampahId = $this->createBankSampah();
        
        // Create multiple orphaned katalog records
        DB::table('katalog_sampah')->insert([
            [
                'bank_sampah_id' => $bankSampahId,
                'sub_kategori_sampah_id' => 99999,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Orphaned Item 1',
                'harga_per_kg' => 5000,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'bank_sampah_id' => $bankSampahId,
                'sub_kategori_sampah_id' => 88888,
                'kategori_sampah' => 1,
                'nama_item_sampah' => 'Orphaned Item 2',
                'harga_per_kg' => 3000,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        
        try {
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php'
            ]);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Verify error message contains helpful information
            $message = $e->getMessage();
            $this->assertStringContainsString('Found 2 orphaned', $message);
            $this->assertStringContainsString('Orphaned Item 1', $message);
            $this->assertStringContainsString('Orphaned Item 2', $message);
            $this->assertStringContainsString('sub_kategori_sampah_id: 99999', $message);
            $this->assertStringContainsString('sub_kategori_sampah_id: 88888', $message);
            $this->assertStringContainsString('Please fix these records', $message);
            $this->assertStringContainsString('UPDATE katalog_sampah SET sub_kategori_sampah_id = NULL', $message);
        }
    }

    /**
     * Test that foreign key prevents deletion of referenced sub_kategori
     *
     * @test
     */
    public function test_foreign_key_prevents_deletion_of_referenced_sub_kategori()
    {
        // Create test data
        $bankSampahId = $this->createBankSampah();
        $subKategoriId = $this->createSubKategori($bankSampahId);
        
        // Create katalog referencing the sub_kategori
        DB::table('katalog_sampah')->insert([
            'bank_sampah_id' => $bankSampahId,
            'sub_kategori_sampah_id' => $subKategoriId,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Test Item',
            'harga_per_kg' => 5000,
            'status_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Run Phase 4 migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php'
        ]);
        
        // Attempt to delete the sub_kategori (should fail due to FK constraint)
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        DB::table('sub_kategori_sampah')->where('id', $subKategoriId)->delete();
    }

    /**
     * Test that foreign key allows deletion when no references exist
     *
     * @test
     */
    public function test_foreign_key_allows_deletion_when_no_references()
    {
        // Create test data
        $bankSampahId = $this->createBankSampah();
        $subKategoriId = $this->createSubKategori($bankSampahId);
        
        // Run Phase 4 migration (no katalog records created)
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php'
        ]);
        
        // Delete should succeed since no katalog references this sub_kategori
        $deleted = DB::table('sub_kategori_sampah')->where('id', $subKategoriId)->delete();
        
        $this->assertEquals(1, $deleted);
        $this->assertDatabaseMissing('sub_kategori_sampah', ['id' => $subKategoriId]);
    }

    /**
     * Test that migration rollback removes foreign key constraint
     *
     * @test
     */
    public function test_migration_rollback_removes_foreign_key()
    {
        // Create test data
        $bankSampahId = $this->createBankSampah();
        $subKategoriId = $this->createSubKategori($bankSampahId);
        
        // Run Phase 4 migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php'
        ]);
        
        // Verify foreign key exists
        $this->assertTrue($this->foreignKeyExists('katalog_sampah', 'sub_kategori_sampah_id'));
        
        // Rollback migration
        Artisan::call('migrate:rollback', [
            '--path' => 'database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php'
        ]);
        
        // Verify foreign key was removed
        $this->assertFalse($this->foreignKeyExists('katalog_sampah', 'sub_kategori_sampah_id'));
    }

    /**
     * Test that migration handles mixed valid and null references
     *
     * @test
     */
    public function test_migration_handles_mixed_valid_and_null_references()
    {
        // Create test data
        $bankSampahId = $this->createBankSampah();
        $subKategoriId = $this->createSubKategori($bankSampahId);
        
        // Create katalog with valid reference
        DB::table('katalog_sampah')->insert([
            'bank_sampah_id' => $bankSampahId,
            'sub_kategori_sampah_id' => $subKategoriId,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Valid Item',
            'harga_per_kg' => 5000,
            'status_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create katalog with null reference
        DB::table('katalog_sampah')->insert([
            'bank_sampah_id' => $bankSampahId,
            'sub_kategori_sampah_id' => null,
            'kategori_sampah' => 1,
            'nama_item_sampah' => 'Null Item',
            'harga_per_kg' => 3000,
            'status_aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Run Phase 4 migration (should succeed)
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php'
        ]);
        
        // Verify foreign key was added
        $this->assertTrue($this->foreignKeyExists('katalog_sampah', 'sub_kategori_sampah_id'));
        
        // Verify both records still exist
        $this->assertDatabaseHas('katalog_sampah', ['nama_item_sampah' => 'Valid Item']);
        $this->assertDatabaseHas('katalog_sampah', ['nama_item_sampah' => 'Null Item']);
    }

    // Helper methods

    /**
     * Create a test bank sampah
     */
    private function createBankSampah(): int
    {
        return DB::table('bank_sampah')->insertGetId([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat' => 'Test Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Create a test sub kategori sampah
     */
    private function createSubKategori(int $bankSampahId): int
    {
        // First create kategori_sampah if needed
        $kategoriId = DB::table('kategori_sampah')->insertGetId([
            'kode_kategori' => 'kering',
            'nama_kategori' => 'Sampah Kering',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return DB::table('sub_kategori_sampah')->insertGetId([
            'bank_sampah_id' => $bankSampahId,
            'kategori_sampah_id' => $kategoriId,
            'kategori_sampah' => 0,
            'kode_sub_kategori' => 'TEST-001',
            'slug' => 'test-sub-kategori',
            'nama_sub_kategori' => 'Test Sub Kategori',
            'status_aktif' => 1,
            'is_active' => true,
            'urutan' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Check if a foreign key exists on a table column
     */
    private function foreignKeyExists(string $table, string $column): bool
    {
        try {
            $foreignKeys = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys($table);
            
            foreach ($foreignKeys as $foreignKey) {
                if (in_array($column, $foreignKey->getLocalColumns())) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
