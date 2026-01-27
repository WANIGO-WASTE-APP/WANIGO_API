<?php

namespace Tests\Unit;

use App\Models\SetoranSampah;
use App\Models\User;
use App\Models\BankSampah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetoranSampahModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that status constants are defined with correct lowercase values.
     *
     * @test
     */
    public function test_status_constants_are_defined_correctly()
    {
        $this->assertEquals('pengajuan', SetoranSampah::STATUS_PENGAJUAN);
        $this->assertEquals('diproses', SetoranSampah::STATUS_DIPROSES);
        $this->assertEquals('selesai', SetoranSampah::STATUS_SELESAI);
        $this->assertEquals('dibatalkan', SetoranSampah::STATUS_DIBATALKAN);
    }

    /**
     * Test getValidStatuses returns all valid status values.
     *
     * @test
     */
    public function test_get_valid_statuses_returns_all_statuses()
    {
        $validStatuses = SetoranSampah::getValidStatuses();

        $this->assertIsArray($validStatuses);
        $this->assertCount(4, $validStatuses);
        $this->assertContains('pengajuan', $validStatuses);
        $this->assertContains('diproses', $validStatuses);
        $this->assertContains('selesai', $validStatuses);
        $this->assertContains('dibatalkan', $validStatuses);
    }

    /**
     * Test ongoing scope returns only pengajuan and diproses records.
     *
     * @test
     */
    public function test_ongoing_scope_returns_only_pengajuan_and_diproses()
    {
        // Create test data
        $user = User::factory()->create();
        $bankSampah = BankSampah::create([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'status_operasional' => true,
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        // Create setoran with different statuses
        $pengajuan = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_PENGAJUAN,
            'kode_setoran_sampah' => 'TEST001',
        ]);

        $diproses = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_DIPROSES,
            'kode_setoran_sampah' => 'TEST002',
        ]);

        $selesai = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_SELESAI,
            'kode_setoran_sampah' => 'TEST003',
        ]);

        $dibatalkan = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_DIBATALKAN,
            'kode_setoran_sampah' => 'TEST004',
        ]);

        // Query using ongoing scope
        $ongoingRecords = SetoranSampah::ongoing()->get();

        // Assert only pengajuan and diproses are returned
        $this->assertCount(2, $ongoingRecords);
        $this->assertTrue($ongoingRecords->contains('id', $pengajuan->id));
        $this->assertTrue($ongoingRecords->contains('id', $diproses->id));
        $this->assertFalse($ongoingRecords->contains('id', $selesai->id));
        $this->assertFalse($ongoingRecords->contains('id', $dibatalkan->id));
    }

    /**
     * Test ongoing scope excludes selesai status.
     *
     * @test
     */
    public function test_ongoing_scope_excludes_selesai()
    {
        $user = User::factory()->create();
        $bankSampah = BankSampah::create([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'status_operasional' => true,
        ]);

        SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_SELESAI,
            'kode_setoran_sampah' => 'TEST005',
        ]);

        $ongoingRecords = SetoranSampah::ongoing()->get();

        $this->assertCount(0, $ongoingRecords);
    }

    /**
     * Test ongoing scope excludes dibatalkan status.
     *
     * @test
     */
    public function test_ongoing_scope_excludes_dibatalkan()
    {
        $user = User::factory()->create();
        $bankSampah = BankSampah::create([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'status_operasional' => true,
        ]);

        SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_DIBATALKAN,
            'kode_setoran_sampah' => 'TEST006',
        ]);

        $ongoingRecords = SetoranSampah::ongoing()->get();

        $this->assertCount(0, $ongoingRecords);
    }

    /**
     * Test completed scope returns only selesai records.
     *
     * @test
     */
    public function test_completed_scope_returns_only_selesai()
    {
        $user = User::factory()->create();
        $bankSampah = BankSampah::create([
            'nama_bank_sampah' => 'Test Bank Sampah',
            'alamat_bank_sampah' => 'Test Address',
            'status_operasional' => true,
        ]);

        // Create setoran with different statuses
        SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_PENGAJUAN,
            'kode_setoran_sampah' => 'TEST007',
        ]);

        $selesai = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_SELESAI,
            'kode_setoran_sampah' => 'TEST008',
        ]);

        SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_DIBATALKAN,
            'kode_setoran_sampah' => 'TEST009',
        ]);

        // Query using completed scope
        $completedRecords = SetoranSampah::completed()->get();

        // Assert only selesai is returned
        $this->assertCount(1, $completedRecords);
        $this->assertEquals($selesai->id, $completedRecords->first()->id);
    }
}
