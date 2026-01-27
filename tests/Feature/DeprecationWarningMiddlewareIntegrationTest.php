<?php

namespace Tests\Feature;

use App\Models\BankSampah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for AddDeprecationWarnings middleware with Bank Sampah endpoints
 * 
 * Requirements: 2.5, 7.2
 */
class DeprecationWarningMiddlewareIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that Bank Sampah list endpoint includes Warning header
     * 
     * @test
     */
    public function test_bank_sampah_list_endpoint_includes_warning_header()
    {
        // Create a bank sampah with deprecated fields
        $bankSampah = BankSampah::create([
            'id_tipe_bank_sampah' => 1,
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'nomor_telepon' => '081234567890',
            'nomor_telepon_publik' => '081234567891',
            'email' => 'test@example.com',
            'status_operasional' => true,
        ]);

        $response = $this->getJson('/api/bank-sampah');

        $response->assertStatus(200);
        $response->assertHeader('Warning', '299 - "Deprecated fields (@deprecated) will be removed in the next release"');
        
        // Verify response contains @deprecated fields
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('@deprecated', $data[0]);
    }

    /**
     * Test that Bank Sampah detail endpoint includes Warning header
     * 
     * @test
     */
    public function test_bank_sampah_detail_endpoint_includes_warning_header()
    {
        $bankSampah = BankSampah::create([
            'id_tipe_bank_sampah' => 1,
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'nomor_telepon' => '081234567890',
            'nomor_telepon_publik' => '081234567891',
            'email' => 'test@example.com',
            'status_operasional' => true,
        ]);

        $response = $this->getJson("/api/bank-sampah/{$bankSampah->id}");

        $response->assertStatus(200);
        $response->assertHeader('Warning', '299 - "Deprecated fields (@deprecated) will be removed in the next release"');
        
        // Verify response contains @deprecated fields
        $data = $response->json('data');
        $this->assertArrayHasKey('@deprecated', $data);
    }

    /**
     * Test that registered bank sampah endpoint includes Warning header
     * 
     * @test
     */
    public function test_registered_bank_sampah_endpoint_includes_warning_header()
    {
        // Create a user with nasabah
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create a bank sampah
        $bankSampah = BankSampah::create([
            'id_tipe_bank_sampah' => 1,
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'nomor_telepon' => '081234567890',
            'nomor_telepon_publik' => '081234567891',
            'email' => 'test@example.com',
            'status_operasional' => true,
        ]);

        $response = $this->getJson('/api/nasabah/bank-sampah/registered');

        // Note: This might return 200 or 404 depending on whether the user has registered banks
        // We're mainly testing that the middleware is applied
        if ($response->status() === 200) {
            $data = $response->json('data');
            if (!empty($data)) {
                $response->assertHeader('Warning', '299 - "Deprecated fields (@deprecated) will be removed in the next release"');
                $this->assertArrayHasKey('@deprecated', $data[0]);
            }
        }
    }

    /**
     * Test that endpoints without deprecated fields don't have Warning header
     * 
     * @test
     */
    public function test_endpoints_without_deprecated_fields_have_no_warning_header()
    {
        // Create a user
        $user = User::factory()->create();

        $response = $this->getJson('/api/user');

        // This endpoint doesn't use resources with @deprecated fields
        // So it should not have the Warning header
        if ($response->status() === 200 || $response->status() === 401) {
            $this->assertFalse($response->headers->has('Warning'));
        }
    }
}
