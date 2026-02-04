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

class DetailSetoranGetItemDetailTest extends TestCase
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
            'nama_bank_sampah' => 'Bank Sampah Test',
            'alamat_bank_sampah' => 'Jl. Test No. 123',
            'nomor_telepon_publik' => '081234567890',
            'email' => 'test@banksampah.com',
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
    public function it_returns_detail_setoran_with_bank_name_and_kode_setoran()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/nasabah/detail-setoran/{$this->detailSetoran->id}/detail");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'detail_setoran',
                    'item_sampah',
                    'nama_bank_sampah',
                    'kode_setoran',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'nama_bank_sampah' => 'Bank Sampah Test',
                    'kode_setoran' => 'STR001000012024010112AB',
                ],
            ]);
    }

    /** @test */
    public function it_returns_404_when_detail_setoran_not_found()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/nasabah/detail-setoran/99999/detail');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Detail setoran tidak ditemukan',
            ]);
    }

    /** @test */
    public function it_returns_403_when_user_does_not_own_the_setoran()
    {
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/nasabah/detail-setoran/{$this->detailSetoran->id}/detail");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke data ini',
            ]);
    }

}
