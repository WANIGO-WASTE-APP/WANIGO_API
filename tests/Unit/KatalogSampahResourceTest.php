<?php

namespace Tests\Unit;

use App\Http\Resources\KatalogSampahResource;
use App\Models\BankSampah;
use App\Models\KatalogSampah;
use App\Models\SubKategoriSampah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogSampahResourceTest extends TestCase
{
    use RefreshDatabase;

    protected $bankSampah;
    protected $subKategori;

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

        // Create test sub-category
        $this->subKategori = SubKategoriSampah::create([
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
    }

    /**
     * Test that resource includes all required katalog fields
     *
     * @test
     */
    public function test_resource_includes_all_katalog_fields()
    {
        $katalog = KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $this->subKategori->id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Botol PET 600ml',
            'harga_per_kg' => 3000.00,
            'deskripsi_item_sampah' => 'Botol plastik bekas minuman',
            'gambar_item_sampah' => 'katalog/botol-pet.jpg',
            'status_aktif' => true,
        ]);

        $resource = new KatalogSampahResource($katalog);
        $array = $resource->toArray(request());

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('nama', $array);
        $this->assertArrayHasKey('harga', $array);
        $this->assertArrayHasKey('deskripsi', $array);
        $this->assertArrayHasKey('gambar_url', $array);
        
        $this->assertEquals($katalog->id, $array['id']);
        $this->assertEquals('Botol PET 600ml', $array['nama']);
        $this->assertEquals(3000.00, $array['harga']);
        $this->assertEquals('Botol plastik bekas minuman', $array['deskripsi']);
    }

    /**
     * Test that resource includes kategori_sampah field for backward compatibility
     *
     * @test
     */
    public function test_resource_includes_kategori_sampah_for_backward_compatibility()
    {
        $katalog = KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $this->subKategori->id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Botol PET 600ml',
            'harga_per_kg' => 3000.00,
            'status_aktif' => true,
        ]);

        $resource = new KatalogSampahResource($katalog);
        $array = $resource->toArray(request());

        $this->assertArrayHasKey('kategori_sampah', $array);
        $this->assertEquals('kering', $array['kategori_sampah']);
    }

    /**
     * Test that resource includes sub_kategori object with all required fields
     *
     * @test
     */
    public function test_resource_includes_sub_kategori_object_with_all_fields()
    {
        $katalog = KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $this->subKategori->id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Botol PET 600ml',
            'harga_per_kg' => 3000.00,
            'status_aktif' => true,
        ]);

        // Load the relationship
        $katalog->load('subKategoriSampah');

        $resource = new KatalogSampahResource($katalog);
        $array = $resource->toArray(request());

        $this->assertArrayHasKey('sub_kategori', $array);
        $this->assertIsArray($array['sub_kategori']);
        
        $subKategori = $array['sub_kategori'];
        $this->assertArrayHasKey('id', $subKategori);
        $this->assertArrayHasKey('nama', $subKategori);
        $this->assertArrayHasKey('slug', $subKategori);
        $this->assertArrayHasKey('icon', $subKategori);
        $this->assertArrayHasKey('warna', $subKategori);
        
        $this->assertEquals($this->subKategori->id, $subKategori['id']);
        $this->assertEquals('Botol Plastik', $subKategori['nama']);
        $this->assertEquals('botol-plastik', $subKategori['slug']);
        $this->assertEquals('bottle', $subKategori['icon']);
        $this->assertEquals('#2196F3', $subKategori['warna']);
    }

    /**
     * Test that resource handles null sub_kategori_sampah_id gracefully
     *
     * @test
     */
    public function test_resource_handles_null_sub_kategori_gracefully()
    {
        $katalog = KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => null, // No sub-kategori
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Botol PET 600ml',
            'harga_per_kg' => 3000.00,
            'status_aktif' => true,
        ]);

        $resource = new KatalogSampahResource($katalog);
        $array = $resource->toArray(request());

        // sub_kategori should not be present when null
        $this->assertArrayNotHasKey('sub_kategori', $array);
        
        // Other fields should still be present
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('nama', $array);
        $this->assertArrayHasKey('kategori_sampah', $array);
    }

    /**
     * Test that resource works with basah kategori
     *
     * @test
     */
    public function test_resource_works_with_basah_kategori()
    {
        $subKategoriBasah = SubKategoriSampah::create([
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

        $katalog = KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $subKategoriBasah->id,
            'kategori_sampah' => 1,
            'nama_item_sampah' => 'Sisa Makanan',
            'harga_per_kg' => 500.00,
            'status_aktif' => true,
        ]);

        $katalog->load('subKategoriSampah');

        $resource = new KatalogSampahResource($katalog);
        $array = $resource->toArray(request());

        $this->assertEquals('basah', $array['kategori_sampah']);
        $this->assertEquals('Organik', $array['sub_kategori']['nama']);
        $this->assertEquals('organik', $array['sub_kategori']['slug']);
    }

    /**
     * Test that harga is returned as float
     *
     * @test
     */
    public function test_harga_is_returned_as_float()
    {
        $katalog = KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $this->subKategori->id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Botol PET 600ml',
            'harga_per_kg' => 3000.50,
            'status_aktif' => true,
        ]);

        $resource = new KatalogSampahResource($katalog);
        $array = $resource->toArray(request());

        $this->assertIsFloat($array['harga']);
        $this->assertEquals(3000.50, $array['harga']);
    }
}
