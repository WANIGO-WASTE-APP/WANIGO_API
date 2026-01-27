<?php

namespace Tests\Feature;

use App\Models\BankSampah;
use App\Models\MemberBankSampah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test for registered bank sampah endpoint
 * 
 * Requirements: 2.1, 3.2, 5.1, 5.2
 */
class RegisteredBankSampahEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that registered endpoint returns list with BankSampahListResource format
     * 
     * @test
     */
    public function test_registered_endpoint_returns_bank_sampah_list_resource_format()
    {
        // Create a user
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create bank sampah
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

        // Register user as member
        MemberBankSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'kode_nasabah' => 'TEST123',
            'status_keanggotaan' => 'aktif',
            'tanggal_daftar' => now(),
        ]);

        $response = $this->getJson('/api/nasabah/bank-sampah/registered');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'nama_bank_sampah',
                    'alamat',
                    'latitude',
                    'longitude',
                    'status_operasional',
                    'contact_info' => [
                        'phone',
                        'email',
                    ],
                    'foto_usaha_url',
                    'kategori_sampah',
                    'jam_operasional_hari_ini',
                    '@deprecated' => [
                        'nomor_telepon',
                        'nomor_telepon_publik',
                    ],
                ],
            ],
        ]);

        // Verify response values
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Test Bank Sampah', $data[0]['nama_bank_sampah']);
        $this->assertEquals('081234567891', $data[0]['contact_info']['phone']); // nomor_telepon_publik has priority
        $this->assertEquals('test@example.com', $data[0]['contact_info']['email']);
        $this->assertArrayHasKey('@deprecated', $data[0]);
    }

    /**
     * Test that registered endpoint excludes tonase_sampah
     * 
     * @test
     */
    public function test_registered_endpoint_excludes_tonase_sampah()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $bankSampah = BankSampah::create([
            'id_tipe_bank_sampah' => 1,
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'nomor_telepon' => '081234567890',
            'email' => 'test@example.com',
            'status_operasional' => true,
            'tonase_sampah' => 1000.50,
        ]);

        MemberBankSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'kode_nasabah' => 'TEST123',
            'status_keanggotaan' => 'aktif',
            'tanggal_daftar' => now(),
        ]);

        $response = $this->getJson('/api/nasabah/bank-sampah/registered');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayNotHasKey('tonase_sampah', $data[0]);
    }

    /**
     * Test that registered endpoint returns empty array when user has no registrations
     * 
     * @test
     */
    public function test_registered_endpoint_returns_empty_array_when_no_registrations()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/nasabah/bank-sampah/registered');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [],
        ]);
    }

    /**
     * Test that registered endpoint only returns active memberships
     * 
     * @test
     */
    public function test_registered_endpoint_only_returns_active_memberships()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        // Create two bank sampah
        $activeBankSampah = BankSampah::create([
            'id_tipe_bank_sampah' => 1,
            'nama_bank_sampah' => 'Active Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'nomor_telepon' => '081234567890',
            'email' => 'active@example.com',
            'status_operasional' => true,
        ]);

        $inactiveBankSampah = BankSampah::create([
            'id_tipe_bank_sampah' => 1,
            'nama_bank_sampah' => 'Inactive Bank Sampah',
            'alamat_bank_sampah' => 'Test Address 2',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'nomor_telepon' => '081234567891',
            'email' => 'inactive@example.com',
            'status_operasional' => true,
        ]);

        // Register user as active member in first bank
        MemberBankSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $activeBankSampah->id,
            'kode_nasabah' => 'TEST123',
            'status_keanggotaan' => 'aktif',
            'tanggal_daftar' => now(),
        ]);

        // Register user as inactive member in second bank
        MemberBankSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $inactiveBankSampah->id,
            'kode_nasabah' => 'TEST456',
            'status_keanggotaan' => 'nonaktif',
            'tanggal_daftar' => now(),
        ]);

        $response = $this->getJson('/api/nasabah/bank-sampah/registered');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        // Should only return the active membership
        $this->assertCount(1, $data);
        $this->assertEquals('Active Bank Sampah', $data[0]['nama_bank_sampah']);
    }

    /**
     * Test that registered endpoint includes deprecation warning header
     * 
     * @test
     */
    public function test_registered_endpoint_includes_deprecation_warning_header()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

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

        MemberBankSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'kode_nasabah' => 'TEST123',
            'status_keanggotaan' => 'aktif',
            'tanggal_daftar' => now(),
        ]);

        $response = $this->getJson('/api/nasabah/bank-sampah/registered');

        $response->assertStatus(200);
        $response->assertHeader('Warning', '299 - "Deprecated fields (@deprecated) will be removed in the next release"');
    }

    /**
     * Test that registered endpoint requires authentication
     * 
     * @test
     */
    public function test_registered_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/nasabah/bank-sampah/registered');

        $response->assertStatus(401);
    }
}
