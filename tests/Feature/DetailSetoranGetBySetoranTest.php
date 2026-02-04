<?php

namespace Tests\Feature;

use App\Models\BankSampah;
use App\Models\DetailSetoran;
use App\Models\KatalogSampah;
use App\Models\SetoranSampah;
use App\Models\SubKategoriSampah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DetailSetoranGetBySetoranTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $bankSampah;
    protected $setoran;
    protected $katalogSampah;
    protected $detailSetoran;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test bank sampah
        $this->bankSampah = BankSampah::create([
            'nama_bank_sampah' => 'Bank Sampah Sejahtera',
            'alamat_bank_sampah' => 'Jl. Sejahtera No. 456',
            'nomor_telepon_publik' => '081234567890',
            'email' => 'sejahtera@banksampah.com',
            'status_operasional' => true,
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        // Create test sub-kategori
        $subKategori = SubKategoriSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'kategori_sampah' => 0,
            'kategori_sampah_id' => 1,
            'kode_sub_kategori' => 'SK-001',
            'nama_sub_kategori' => 'Botol Plastik',
            'slug' => 'botol-plastik',
            'icon' => 'bottle',
            'warna' => '#2196F3',
            'is_active' => true,
            'urutan' => 1,
        ]);

        // Create test katalog sampah
        $this->katalogSampah = KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $subKategori->id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Botol PET 600ml',
            'harga_per_kg' => 3000.00,
            'deskripsi_item_sampah' => 'Botol plastik bekas minuman',
            'status_aktif' => true,
        ]);

        // Create test setoran sampah
        $this->setoran = SetoranSampah::create([
            'user_id' => $this->user->id,
            'bank_sampah_id' => $this->bankSampah->id,
            'kode_setoran_sampah' => 'STR001000012024010112AB',
            'tanggal_setoran' => now(),
            'total_saldo' => 15000,
            'total_berat' => 5.0,
            'status_setoran' => 'Pengajuan',
        ]);

        // Create test detail setoran
        $this->detailSetoran = DetailSetoran::create([
            'setoran_sampah_id' => $this->setoran->id,
            'item_sampah_id' => $this->katalogSampah->id,
            'berat' => 5.0,
            'saldo' => 15000,
        ]);
    }

    /** @test */
    public function it_returns_detail_setoran_with_bank_name_in_setoran_object()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/nasabah/detail-setoran/by-setoran', [
            'setoran_sampah_id' => $this->setoran->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'detail_setoran',
                    'detail_by_sub_kategori',
                    'setoran' => [
                        'id',
                        'kode_setoran',
                        'nama_bank_sampah',
                        'status',
                        'tanggal_setoran',
                        'total_berat',
                        'total_nilai',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'setoran' => [
                        'nama_bank_sampah' => 'Bank Sampah Sejahtera',
                        'kode_setoran' => 'STR001000012024010112AB',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_returns_422_when_setoran_not_found()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/nasabah/detail-setoran/by-setoran', [
            'setoran_sampah_id' => 99999,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_returns_403_when_user_does_not_own_the_setoran()
    {
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->postJson('/api/nasabah/detail-setoran/by-setoran', [
            'setoran_sampah_id' => $this->setoran->id,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Setoran tidak ditemukan atau Anda tidak memiliki akses',
            ]);
    }

    /** @test */
    public function it_returns_all_detail_items_for_a_setoran()
    {
        // Create additional detail items
        $katalogSampah2 = KatalogSampah::create([
            'bank_sampah_id' => $this->bankSampah->id,
            'sub_kategori_sampah_id' => $this->katalogSampah->sub_kategori_sampah_id,
            'kategori_sampah' => 0,
            'nama_item_sampah' => 'Botol PET 1500ml',
            'harga_per_kg' => 3500.00,
            'deskripsi_item_sampah' => 'Botol plastik besar',
            'status_aktif' => true,
        ]);

        DetailSetoran::create([
            'setoran_sampah_id' => $this->setoran->id,
            'item_sampah_id' => $katalogSampah2->id,
            'berat' => 3.0,
            'saldo' => 10500,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/nasabah/detail-setoran/by-setoran', [
            'setoran_sampah_id' => $this->setoran->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.detail_setoran');
    }

    /** @test */
    public function it_returns_empty_array_for_setoran_with_no_items()
    {
        // Create a setoran with no detail items
        $emptySetoran = SetoranSampah::create([
            'user_id' => $this->user->id,
            'bank_sampah_id' => $this->bankSampah->id,
            'kode_setoran_sampah' => 'STR001000012024010112EF',
            'tanggal_setoran' => now(),
            'total_saldo' => 0,
            'total_berat' => 0,
            'status_setoran' => 'Pengajuan',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/nasabah/detail-setoran/by-setoran', [
            'setoran_sampah_id' => $emptySetoran->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data.detail_setoran')
            ->assertJson([
                'success' => true,
                'data' => [
                    'setoran' => [
                        'nama_bank_sampah' => 'Bank Sampah Sejahtera',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_validates_required_setoran_sampah_id()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/nasabah/detail-setoran/by-setoran', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }
}
