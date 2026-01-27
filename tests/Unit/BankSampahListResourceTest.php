<?php

namespace Tests\Unit;

use App\Http\Resources\BankSampahListResource;
use App\Models\BankSampah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for BankSampahListResource
 * 
 * Tests the resource formatting for Bank Sampah list endpoints.
 * Verifies contact_info normalization, deprecated fields, image fallback,
 * and tonase_sampah exclusion.
 * 
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.7, 3.1
 */
class BankSampahListResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that contact_info object is present with phone and email fields
     * Requirement 2.1
     */
    public function test_contact_info_structure_is_present(): void
    {
        $bankSampah = new BankSampah([
            'id' => 1,
            'nama_bank_sampah' => 'Test Bank',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'status_operasional' => true,
            'nomor_telepon_publik' => '081234567890',
            'email' => 'test@bank.com',
            'foto_usaha' => 'test.jpg',
            'tonase_sampah' => 100.50,
        ]);
        
        // Mock relationships
        $bankSampah->setRelation('jamOperasional', collect());
        $bankSampah->setRelation('katalogSampah', collect());
        
        $resource = new BankSampahListResource($bankSampah);
        $array = $resource->toArray(request());
        
        $this->assertArrayHasKey('contact_info', $array);
        $this->assertArrayHasKey('phone', $array['contact_info']);
        $this->assertArrayHasKey('email', $array['contact_info']);
    }

    /**
     * Test phone resolution priority: nomor_telepon_publik is used
     * Requirement 2.2
     */
    public function test_phone_resolves_to_nomor_telepon_publik(): void
    {
        $bankSampah = new BankSampah([
            'id' => 1,
            'nama_bank_sampah' => 'Test Bank',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'status_operasional' => true,
            'nomor_telepon_publik' => '081234567890',
            'email' => 'test@bank.com',
        ]);
        
        $bankSampah->setRelation('jamOperasional', collect());
        $bankSampah->setRelation('katalogSampah', collect());
        
        $resource = new BankSampahListResource($bankSampah);
        $array = $resource->toArray(request());
        
        $this->assertEquals('081234567890', $array['contact_info']['phone']);
    }

    /**
     * Test phone resolution when nomor_telepon_publik is null
     * Requirement 2.2
     */
    public function test_phone_resolves_to_null_when_no_phone_available(): void
    {
        $bankSampah = new BankSampah([
            'id' => 1,
            'nama_bank_sampah' => 'Test Bank',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'status_operasional' => true,
            'nomor_telepon_publik' => null,
            'email' => 'test@bank.com',
        ]);
        
        $bankSampah->setRelation('jamOperasional', collect());
        $bankSampah->setRelation('katalogSampah', collect());
        
        $resource = new BankSampahListResource($bankSampah);
        $array = $resource->toArray(request());
        
        $this->assertNull($array['contact_info']['phone']);
    }

    /**
     * Test email field mapping
     * Requirement 2.3
     */
    public function test_email_field_is_mapped_correctly(): void
    {
        $bankSampah = new BankSampah([
            'id' => 1,
            'nama_bank_sampah' => 'Test Bank',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'status_operasional' => true,
            'email' => 'test@bank.com',
        ]);
        
        $bankSampah->setRelation('jamOperasional', collect());
        $bankSampah->setRelation('katalogSampah', collect());
        
        $resource = new BankSampahListResource($bankSampah);
        $array = $resource->toArray(request());
        
        $this->assertEquals('test@bank.com', $array['contact_info']['email']);
    }

    /**
     * Test that @deprecated object is present with old fields
     * Requirement 2.4
     */
    public function test_deprecated_fields_are_included(): void
    {
        $bankSampah = new BankSampah([
            'id' => 1,
            'nama_bank_sampah' => 'Test Bank',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'status_operasional' => true,
            'nomor_telepon_publik' => '081234567890',
            'email' => 'test@bank.com',
        ]);
        
        $bankSampah->setRelation('jamOperasional', collect());
        $bankSampah->setRelation('katalogSampah', collect());
        
        $resource = new BankSampahListResource($bankSampah);
        $array = $resource->toArray(request());
        
        $this->assertArrayHasKey('@deprecated', $array);
        $this->assertArrayHasKey('nomor_telepon', $array['@deprecated']);
        $this->assertArrayHasKey('nomor_telepon_publik', $array['@deprecated']);
        $this->assertEquals('081234567890', $array['@deprecated']['nomor_telepon_publik']);
    }

    /**
     * Test default image URL fallback when foto_usaha is null
     * Requirement 2.7
     */
    public function test_default_image_url_fallback_when_foto_usaha_is_null(): void
    {
        config(['app.default_bank_image_url' => '/images/default-bank.png']);
        
        $bankSampah = new BankSampah([
            'id' => 1,
            'nama_bank_sampah' => 'Test Bank',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'status_operasional' => true,
            'foto_usaha' => null,
        ]);
        
        $bankSampah->setRelation('jamOperasional', collect());
        $bankSampah->setRelation('katalogSampah', collect());
        
        $resource = new BankSampahListResource($bankSampah);
        $array = $resource->toArray(request());
        
        $this->assertEquals('/images/default-bank.png', $array['foto_usaha_url']);
    }

    /**
     * Test that foto_usaha_url is generated correctly when foto_usaha exists
     * Requirement 2.7
     */
    public function test_foto_usaha_url_is_generated_when_foto_usaha_exists(): void
    {
        $bankSampah = new BankSampah([
            'id' => 1,
            'nama_bank_sampah' => 'Test Bank',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'status_operasional' => true,
            'foto_usaha' => 'test-image.jpg',
        ]);
        
        $bankSampah->setRelation('jamOperasional', collect());
        $bankSampah->setRelation('katalogSampah', collect());
        
        $resource = new BankSampahListResource($bankSampah);
        $array = $resource->toArray(request());
        
        $this->assertStringContainsString('test-image.jpg', $array['foto_usaha_url']);
    }

    /**
     * Test that tonase_sampah field is excluded from list resource
     * Requirement 3.1
     */
    public function test_tonase_sampah_is_excluded_from_list_resource(): void
    {
        $bankSampah = new BankSampah([
            'id' => 1,
            'nama_bank_sampah' => 'Test Bank',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'status_operasional' => true,
            'tonase_sampah' => 100.50,
        ]);
        
        $bankSampah->setRelation('jamOperasional', collect());
        $bankSampah->setRelation('katalogSampah', collect());
        
        $resource = new BankSampahListResource($bankSampah);
        $array = $resource->toArray(request());
        
        $this->assertArrayNotHasKey('tonase_sampah', $array);
    }

    /**
     * Test that all required fields are present in the response
     */
    public function test_all_required_fields_are_present(): void
    {
        $bankSampah = new BankSampah([
            'id' => 1,
            'nama_bank_sampah' => 'Test Bank',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'status_operasional' => true,
            'nomor_telepon_publik' => '081234567890',
            'email' => 'test@bank.com',
            'foto_usaha' => 'test.jpg',
            'insight' => 'Test insight',
        ]);
        
        $bankSampah->setRelation('jamOperasional', collect());
        $bankSampah->setRelation('katalogSampah', collect());
        
        $resource = new BankSampahListResource($bankSampah);
        $array = $resource->toArray(request());
        
        $requiredFields = [
            'id',
            'nama_bank_sampah',
            'alamat',
            'latitude',
            'longitude',
            'status_operasional',
            'contact_info',
            'foto_usaha_url',
            'insight',
            'kategori_sampah',
            'jam_operasional_hari_ini',
            '@deprecated',
        ];
        
        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $array, "Field '$field' is missing from response");
        }
    }
}
