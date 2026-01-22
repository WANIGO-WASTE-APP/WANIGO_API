<?php

namespace Tests\Feature;

use App\Models\BankSampah;
use App\Models\SubKategoriSampah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubKategoriByBankEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected $bankSampah;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test bank sampah
        $this->bankSampah = BankSampah::create([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'nomor_telepon_publik' => '081234567890',
            'email' => 'test@banksampah.com',
            'status_operasional' => true,
        ]);

        // Create test sub-categories for kering (dry waste)
        SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 0, // kering
            'kode_sub_kategori' => 'SK-001',
            'nama_sub_kategori' => 'Kertas',
            'slug' => 'kertas',
            'icon' => 'paper',
            'warna' => '#8BC34A',
            'is_active' => true,
            'urutan' => 1,
        ]);

        SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 0, // kering
            'kode_sub_kategori' => 'SK-002',
            'nama_sub_kategori' => 'Botol Plastik',
            'slug' => 'botol-plastik',
            'icon' => 'bottle',
            'warna' => '#2196F3',
            'is_active' => true,
            'urutan' => 2,
        ]);

        SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 0, // kering
            'kode_sub_kategori' => 'SK-003',
            'nama_sub_kategori' => 'Plastik',
            'slug' => 'plastik',
            'icon' => 'plastic-bag',
            'warna' => '#03A9F4',
            'is_active' => true,
            'urutan' => 3,
        ]);

        // Create test sub-categories for basah (wet waste)
        SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 1, // basah
            'kode_sub_kategori' => 'SB-001',
            'nama_sub_kategori' => 'Organik',
            'slug' => 'organik',
            'icon' => 'organic',
            'warna' => '#4CAF50',
            'is_active' => true,
            'urutan' => 1,
        ]);

        SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 1, // basah
            'kode_sub_kategori' => 'SB-002',
            'nama_sub_kategori' => 'Sisa Makanan',
            'slug' => 'sisa-makanan',
            'icon' => 'food-waste',
            'warna' => '#8BC34A',
            'is_active' => true,
            'urutan' => 2,
        ]);

        // Create an inactive sub-category (should not appear in results)
        SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 0,
            'kode_sub_kategori' => 'SK-999',
            'nama_sub_kategori' => 'Inactive Category',
            'slug' => 'inactive',
            'icon' => 'test',
            'warna' => '#000000',
            'is_active' => false,
            'urutan' => 99,
        ]);
    }

    /**
     * Test endpoint returns all active sub-categories grouped by kategori when no filter is applied
     *
     * @test
     */
    public function test_endpoint_returns_all_active_sub_categories_grouped()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/sub-kategori");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'kering' => [
                        '*' => [
                            'id',
                            'nama_sub_kategori',
                            'slug',
                            'icon',
                            'warna',
                            'urutan',
                            'kategori_sampah',
                        ],
                    ],
                    'basah' => [
                        '*' => [
                            'id',
                            'nama_sub_kategori',
                            'slug',
                            'icon',
                            'warna',
                            'urutan',
                            'kategori_sampah',
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data');
        
        // Should have 3 kering items (excluding inactive)
        $this->assertCount(3, $data['kering']);
        
        // Should have 2 basah items
        $this->assertCount(2, $data['basah']);
        
        // Verify all items have kategori_sampah field
        foreach ($data['kering'] as $item) {
            $this->assertEquals('kering', $item['kategori_sampah']);
        }
        
        foreach ($data['basah'] as $item) {
            $this->assertEquals('basah', $item['kategori_sampah']);
        }
    }

    /**
     * Test endpoint filters by kategori kering
     *
     * @test
     */
    public function test_endpoint_filters_by_kategori_kering()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/sub-kategori?kategori=kering");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should only have kering array
        $this->assertArrayHasKey('kering', $data);
        $this->assertArrayNotHasKey('basah', $data);
        
        // Should have 3 kering items
        $this->assertCount(3, $data['kering']);
        
        // Verify all are kering
        foreach ($data['kering'] as $item) {
            $this->assertEquals('kering', $item['kategori_sampah']);
        }
    }

    /**
     * Test endpoint filters by kategori basah
     *
     * @test
     */
    public function test_endpoint_filters_by_kategori_basah()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/sub-kategori?kategori=basah");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should only have basah array
        $this->assertArrayHasKey('basah', $data);
        $this->assertArrayNotHasKey('kering', $data);
        
        // Should have 2 basah items
        $this->assertCount(2, $data['basah']);
        
        // Verify all are basah
        foreach ($data['basah'] as $item) {
            $this->assertEquals('basah', $item['kategori_sampah']);
        }
    }

    /**
     * Test endpoint with kategori=semua returns both categories
     *
     * @test
     */
    public function test_endpoint_with_kategori_semua_returns_both()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/sub-kategori?kategori=semua");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should have both arrays
        $this->assertArrayHasKey('kering', $data);
        $this->assertArrayHasKey('basah', $data);
        
        $this->assertCount(3, $data['kering']);
        $this->assertCount(2, $data['basah']);
    }

    /**
     * Test endpoint validates invalid kategori parameter
     *
     * @test
     */
    public function test_endpoint_validates_invalid_kategori()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/sub-kategori?kategori=invalid");

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test endpoint orders by urutan within each category
     *
     * @test
     */
    public function test_endpoint_orders_by_urutan()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/sub-kategori");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Check kering ordering
        $keringItems = $data['kering'];
        $this->assertEquals(1, $keringItems[0]['urutan']);
        $this->assertEquals('Kertas', $keringItems[0]['nama_sub_kategori']);
        $this->assertEquals(2, $keringItems[1]['urutan']);
        $this->assertEquals('Botol Plastik', $keringItems[1]['nama_sub_kategori']);
        $this->assertEquals(3, $keringItems[2]['urutan']);
        $this->assertEquals('Plastik', $keringItems[2]['nama_sub_kategori']);
        
        // Check basah ordering
        $basahItems = $data['basah'];
        $this->assertEquals(1, $basahItems[0]['urutan']);
        $this->assertEquals('Organik', $basahItems[0]['nama_sub_kategori']);
        $this->assertEquals(2, $basahItems[1]['urutan']);
        $this->assertEquals('Sisa Makanan', $basahItems[1]['nama_sub_kategori']);
    }

    /**
     * Test endpoint includes all required fields
     *
     * @test
     */
    public function test_endpoint_includes_all_required_fields()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/sub-kategori?kategori=kering");

        $response->assertStatus(200);

        $data = $response->json('data');
        $item = $data['kering'][0];

        // Verify all required fields are present
        $this->assertArrayHasKey('id', $item);
        $this->assertArrayHasKey('nama_sub_kategori', $item);
        $this->assertArrayHasKey('slug', $item);
        $this->assertArrayHasKey('icon', $item);
        $this->assertArrayHasKey('warna', $item);
        $this->assertArrayHasKey('urutan', $item);
        $this->assertArrayHasKey('kategori_sampah', $item);

        // Verify field values
        $this->assertEquals('Kertas', $item['nama_sub_kategori']);
        $this->assertEquals('kertas', $item['slug']);
        $this->assertEquals('paper', $item['icon']);
        $this->assertEquals('#8BC34A', $item['warna']);
        $this->assertEquals(1, $item['urutan']);
        $this->assertEquals('kering', $item['kategori_sampah']);
    }

    /**
     * Test endpoint returns only active sub-categories
     *
     * @test
     */
    public function test_endpoint_returns_only_active_sub_categories()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/sub-kategori");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Count total items
        $totalItems = count($data['kering']) + count($data['basah']);
        
        // Should be 5 active items (3 kering + 2 basah), not 6 (excluding inactive)
        $this->assertEquals(5, $totalItems);
        
        // Verify inactive item is not in results
        $allNames = array_merge(
            array_column($data['kering'], 'nama_sub_kategori'),
            array_column($data['basah'], 'nama_sub_kategori')
        );
        
        $this->assertNotContains('Inactive Category', $allNames);
    }

    /**
     * Test endpoint returns empty arrays when no active sub-categories exist
     *
     * @test
     */
    public function test_endpoint_returns_empty_arrays_when_no_active_sub_categories()
    {
        // Create a new bank sampah with no sub-categories
        $emptyBank = BankSampah::create([
            'nama_bank_sampah' => 'Empty Bank Sampah',
            'alamat_bank_sampah' => 'Empty Address',
            'nomor_telepon_publik' => '081234567891',
            'email' => 'empty@banksampah.com',
            'status_operasional' => true,
        ]);

        $response = $this->getJson("/api/bank-sampah/{$emptyBank->id}/sub-kategori");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        $this->assertArrayHasKey('kering', $data);
        $this->assertArrayHasKey('basah', $data);
        $this->assertEmpty($data['kering']);
        $this->assertEmpty($data['basah']);
    }

    /**
     * Test endpoint response structure is correct
     *
     * @test
     */
    public function test_endpoint_response_structure_is_correct()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/sub-kategori");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sub kategori sampah berhasil diambil',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'kering',
                    'basah',
                ],
            ]);
    }
}
