<?php

namespace Tests\Unit;

use App\Models\BankSampah;
use App\Models\SubKategoriSampah;
use App\Rules\KatalogKategoriConsistency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogKategoriConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected $bankSampah;
    protected $subKategoriKering;
    protected $subKategoriBasah;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test bank sampah
        $this->bankSampah = BankSampah::create([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat' => 'Test Address',
            'no_telp' => '081234567890',
            'email' => 'test@banksampah.com',
        ]);

        // Create test sub-categories
        $this->subKategoriKering = SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 0, // kering
            'kode_sub_kategori' => 'SK-001',
            'nama_sub_kategori' => 'Botol Plastik',
            'slug' => 'botol-plastik',
            'is_active' => true,
            'urutan' => 1,
        ]);

        $this->subKategoriBasah = SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 1, // basah
            'kode_sub_kategori' => 'SB-001',
            'nama_sub_kategori' => 'Organik',
            'slug' => 'organik',
            'is_active' => true,
            'urutan' => 1,
        ]);
    }

    /**
     * Test that validation passes when kategori_sampah matches sub_kategori's kategori_sampah
     *
     * @test
     */
    public function test_validation_passes_when_kategori_matches()
    {
        $rule = new KatalogKategoriConsistency(0); // kering

        $this->assertTrue(
            $rule->passes('sub_kategori_sampah_id', $this->subKategoriKering->id),
            'Validation should pass when kategori_sampah matches'
        );
    }

    /**
     * Test that validation fails when kategori_sampah does not match sub_kategori's kategori_sampah
     *
     * @test
     */
    public function test_validation_fails_when_kategori_does_not_match()
    {
        $rule = new KatalogKategoriConsistency(0); // kering

        $this->assertFalse(
            $rule->passes('sub_kategori_sampah_id', $this->subKategoriBasah->id),
            'Validation should fail when kategori_sampah does not match'
        );
    }

    /**
     * Test that validation passes when sub_kategori_sampah_id is null (backward compatibility)
     *
     * @test
     */
    public function test_validation_passes_when_sub_kategori_id_is_null()
    {
        $rule = new KatalogKategoriConsistency(0); // kering

        $this->assertTrue(
            $rule->passes('sub_kategori_sampah_id', null),
            'Validation should pass when sub_kategori_sampah_id is null for backward compatibility'
        );
    }

    /**
     * Test that validation fails when sub_kategori_sampah_id does not exist
     *
     * @test
     */
    public function test_validation_fails_when_sub_kategori_does_not_exist()
    {
        $rule = new KatalogKategoriConsistency(0); // kering

        $this->assertFalse(
            $rule->passes('sub_kategori_sampah_id', 99999),
            'Validation should fail when sub_kategori_sampah_id does not exist'
        );
    }

    /**
     * Test that error message is clear and includes kategori type
     *
     * @test
     */
    public function test_error_message_is_clear_for_kering()
    {
        $rule = new KatalogKategoriConsistency(0); // kering

        $message = $rule->message();

        $this->assertStringContainsString('kering', $message);
        $this->assertStringContainsString('Sub kategori sampah harus sesuai dengan kategori sampah', $message);
    }

    /**
     * Test that error message is clear and includes kategori type for basah
     *
     * @test
     */
    public function test_error_message_is_clear_for_basah()
    {
        $rule = new KatalogKategoriConsistency(1); // basah

        $message = $rule->message();

        $this->assertStringContainsString('basah', $message);
        $this->assertStringContainsString('Sub kategori sampah harus sesuai dengan kategori sampah', $message);
    }

    /**
     * Test validation with basah kategori
     *
     * @test
     */
    public function test_validation_works_correctly_for_basah_kategori()
    {
        $rule = new KatalogKategoriConsistency(1); // basah

        // Should pass for basah sub-kategori
        $this->assertTrue(
            $rule->passes('sub_kategori_sampah_id', $this->subKategoriBasah->id),
            'Validation should pass for basah kategori with basah sub-kategori'
        );

        // Should fail for kering sub-kategori
        $this->assertFalse(
            $rule->passes('sub_kategori_sampah_id', $this->subKategoriKering->id),
            'Validation should fail for basah kategori with kering sub-kategori'
        );
    }
}
