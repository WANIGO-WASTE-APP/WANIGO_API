<?php

namespace Tests\Feature;

use App\Models\BankSampah;
use App\Models\KatalogSampah;
use App\Models\SubKategoriSampah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogByBankEndpointTest extends TestCase
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
            'alamat_bank_sampah' => 'Test Address',
            'nomor_telepon_publik' => '081234567890',
            'email' => 'test@banksampah.com',
            'status_operasional' => true,
        ]);

        // Create test sub-categories
        $this->subKategoriKering = SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 0, // kering
            'kode_sub_kategori' => 'SK-001',
            'nama_sub_kategori' => 'Botol Plastik',
            'slug' => 'botol-plastik',
            'icon' => 'bottle',
            'warna' => '#2196F3',
            'is_active' => true,
            'urutan' => 1,
        ]);

        $this->subKategoriBasah = SubKategoriSampah::create([
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

        // Create test katalog items
        KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $this->subKategoriKering->id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Botol PET 600ml',
            'harga_per_kg' => 3000.00,
            'deskripsi_item_sampah' => 'Botol plastik bekas minuman',
            'status_aktif' => true,
        ]);

        KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $this->subKategoriBasah->id,
            'kategori_sampah' => 1,
            'nama_item_sampah' => 'Sisa Makanan',
            'harga_per_kg' => 500.00,
            'deskripsi_item_sampah' => 'Sisa makanan organik',
            'status_aktif' => true,
        ]);

        // Create an inactive item (should not appear in results)
        KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $this->subKategoriKering->id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Inactive Item',
            'harga_per_kg' => 1000.00,
            'status_aktif' => false,
        ]);
    }

    /**
     * Test endpoint returns all active katalog items when no filter is applied
     *
     * @test
     */
    public function test_endpoint_returns_all_active_items_without_filter()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'nama',
                        'harga',
                        'deskripsi',
                        'gambar_url',
                        'kategori_sampah',
                        'sub_kategori' => [
                            'id',
                            'nama',
                            'slug',
                            'icon',
                            'warna',
                        ],
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                    'from',
                    'to',
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data); // Only active items
        $this->assertEquals(2, $response->json('meta.total'));
    }

    /**
     * Test endpoint filters by kategori kering
     *
     * @test
     */
    public function test_endpoint_filters_by_kategori_kering()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog?kategori=kering");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('kering', $data[0]['kategori_sampah']);
        $this->assertEquals('Botol PET 600ml', $data[0]['nama']);
    }

    /**
     * Test endpoint filters by kategori basah
     *
     * @test
     */
    public function test_endpoint_filters_by_kategori_basah()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog?kategori=basah");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('basah', $data[0]['kategori_sampah']);
        $this->assertEquals('Sisa Makanan', $data[0]['nama']);
    }

    /**
     * Test endpoint filters by sub_kategori_id
     *
     * @test
     */
    public function test_endpoint_filters_by_sub_kategori_id()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog?sub_kategori_id={$this->subKategoriKering->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->subKategoriKering->id, $data[0]['sub_kategori']['id']);
        $this->assertEquals('Botol PET 600ml', $data[0]['nama']);
    }

    /**
     * Test endpoint validates invalid kategori parameter
     *
     * @test
     */
    public function test_endpoint_validates_invalid_kategori()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog?kategori=invalid");

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test endpoint validates invalid sub_kategori_id
     *
     * @test
     */
    public function test_endpoint_validates_invalid_sub_kategori_id()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog?sub_kategori_id=99999");

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test endpoint supports pagination
     *
     * @test
     */
    public function test_endpoint_supports_pagination()
    {
        // Create more items for pagination test
        for ($i = 1; $i <= 25; $i++) {
            KatalogSampah::create([
                'bank_sampah_id' => $this->bankSampah->id,
                'sub_kategori_sampah_id' => $this->subKategoriKering->id,
                'kategori_sampah' => 0,
                'nama_item_sampah' => "Item $i",
                'harga_per_kg' => 1000.00 * $i,
                'status_aktif' => true,
            ]);
        }

        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog?per_page=10");

        $response->assertStatus(200);

        $meta = $response->json('meta');
        $this->assertEquals(10, $meta['per_page']);
        $this->assertEquals(27, $meta['total']); // 2 original + 25 new
        $this->assertEquals(3, $meta['last_page']);
        $this->assertCount(10, $response->json('data'));
    }

    /**
     * Test endpoint orders by sub_kategori urutan then nama_item_sampah
     *
     * @test
     */
    public function test_endpoint_orders_correctly()
    {
        // Create another sub-kategori with higher urutan
        $subKategoriKertas = SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 0,
            'kode_sub_kategori' => 'SK-002',
            'nama_sub_kategori' => 'Kertas',
            'slug' => 'kertas',
            'icon' => 'paper',
            'warna' => '#8BC34A',
            'is_active' => true,
            'urutan' => 2, // Higher urutan
        ]);

        // Create items with different names in same sub-kategori
        KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $this->subKategoriKering->id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Zebra Item', // Should come after Botol PET
            'harga_per_kg' => 2000.00,
            'status_aktif' => true,
        ]);

        KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $subKategoriKertas->id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Kertas HVS',
            'harga_per_kg' => 1500.00,
            'status_aktif' => true,
        ]);

        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog?kategori=kering");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // First should be from subKategoriKering (urutan=1), ordered by name
        $this->assertEquals('Botol PET 600ml', $data[0]['nama']);
        $this->assertEquals('Zebra Item', $data[1]['nama']);
        
        // Last should be from subKategoriKertas (urutan=2)
        $this->assertEquals('Kertas HVS', $data[2]['nama']);
    }

    /**
     * Test endpoint includes sub_kategori information
     *
     * @test
     */
    public function test_endpoint_includes_sub_kategori_information()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog?kategori=kering");

        $response->assertStatus(200);

        $data = $response->json('data');
        $subKategori = $data[0]['sub_kategori'];

        $this->assertArrayHasKey('id', $subKategori);
        $this->assertArrayHasKey('nama', $subKategori);
        $this->assertArrayHasKey('slug', $subKategori);
        $this->assertArrayHasKey('icon', $subKategori);
        $this->assertArrayHasKey('warna', $subKategori);

        $this->assertEquals($this->subKategoriKering->id, $subKategori['id']);
        $this->assertEquals('Botol Plastik', $subKategori['nama']);
        $this->assertEquals('botol-plastik', $subKategori['slug']);
        $this->assertEquals('bottle', $subKategori['icon']);
        $this->assertEquals('#2196F3', $subKategori['warna']);
    }

    /**
     * Test endpoint includes backward compatibility kategori_sampah field
     *
     * @test
     */
    public function test_endpoint_includes_backward_compatibility_field()
    {
        $response = $this->getJson("/api/bank-sampah/{$this->bankSampah->id}/katalog");

        $response->assertStatus(200);

        $data = $response->json('data');
        
        foreach ($data as $item) {
            $this->assertArrayHasKey('kategori_sampah', $item);
            $this->assertContains($item['kategori_sampah'], ['kering', 'basah']);
        }
    }
}
