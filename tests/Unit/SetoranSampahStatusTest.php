<?php

namespace Tests\Unit;

use App\Models\SetoranSampah;
use App\Models\User;
use App\Models\BankSampah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for SetoranSampah status functionality
 * 
 * Tests status constants, query scopes, and validation.
 * Requirements: 4.1, 4.2, 4.3, 4.7
 */
class SetoranSampahStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that status constants are defined correctly
     * Requirement 4.1
     */
    public function test_status_constants_are_defined()
    {
        $this->assertEquals('pengajuan', SetoranSampah::STATUS_PENGAJUAN);
        $this->assertEquals('diproses', SetoranSampah::STATUS_DIPROSES);
        $this->assertEquals('selesai', SetoranSampah::STATUS_SELESAI);
        $this->assertEquals('dibatalkan', SetoranSampah::STATUS_DIBATALKAN);
    }

    /**
     * Test ongoing scope returns only pengajuan and diproses
     * Requirement 4.2
     */
    public function test_ongoing_scope_returns_pengajuan_and_diproses()
    {
        $user = User::factory()->create();
        $bankSampah = BankSampah::factory()->create();

        // Create setoran with different statuses
        $pengajuan = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_PENGAJUAN,
            'total_saldo' => 10000,
            'total_berat' => 5.0,
            'kode_setoran_sampah' => 'STR001000012601TEST1',
        ]);

        $diproses = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_DIPROSES,
            'total_saldo' => 15000,
            'total_berat' => 7.5,
            'kode_setoran_sampah' => 'STR001000012601TEST2',
        ]);

        $selesai = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_SELESAI,
            'total_saldo' => 20000,
            'total_berat' => 10.0,
            'kode_setoran_sampah' => 'STR001000012601TEST3',
        ]);

        $dibatalkan = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_DIBATALKAN,
            'total_saldo' => 5000,
            'total_berat' => 2.5,
            'kode_setoran_sampah' => 'STR001000012601TEST4',
        ]);

        $ongoing = SetoranSampah::ongoing()->get();

        $this->assertCount(2, $ongoing);
        $this->assertTrue($ongoing->contains($pengajuan));
        $this->assertTrue($ongoing->contains($diproses));
        $this->assertFalse($ongoing->contains($selesai));
        $this->assertFalse($ongoing->contains($dibatalkan));
    }

    /**
     * Test ongoing scope excludes selesai
     * Requirement 4.3
     */
    public function test_ongoing_scope_excludes_selesai()
    {
        $user = User::factory()->create();
        $bankSampah = BankSampah::factory()->create();

        SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_SELESAI,
            'total_saldo' => 10000,
            'total_berat' => 5.0,
            'kode_setoran_sampah' => 'STR001000012601TEST5',
        ]);

        $ongoing = SetoranSampah::ongoing()->get();

        $this->assertCount(0, $ongoing);
    }

    /**
     * Test ongoing scope excludes dibatalkan
     * Requirement 4.7
     */
    public function test_ongoing_scope_excludes_dibatalkan()
    {
        $user = User::factory()->create();
        $bankSampah = BankSampah::factory()->create();

        SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_DIBATALKAN,
            'total_saldo' => 10000,
            'total_berat' => 5.0,
            'kode_setoran_sampah' => 'STR001000012601TEST6',
        ]);

        $ongoing = SetoranSampah::ongoing()->get();

        $this->assertCount(0, $ongoing);
    }

    /**
     * Test completed scope returns only selesai
     * Requirement 4.2
     */
    public function test_completed_scope_returns_only_selesai()
    {
        $user = User::factory()->create();
        $bankSampah = BankSampah::factory()->create();

        $pengajuan = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_PENGAJUAN,
            'total_saldo' => 10000,
            'total_berat' => 5.0,
            'kode_setoran_sampah' => 'STR001000012601TEST7',
        ]);

        $selesai = SetoranSampah::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $bankSampah->id,
            'tanggal_setoran' => now(),
            'status_setoran' => SetoranSampah::STATUS_SELESAI,
            'total_saldo' => 20000,
            'total_berat' => 10.0,
            'kode_setoran_sampah' => 'STR001000012601TEST8',
        ]);

        $completed = SetoranSampah::completed()->get();

        $this->assertCount(1, $completed);
        $this->assertTrue($completed->contains($selesai));
        $this->assertFalse($completed->contains($pengajuan));
    }

    /**
     * Test getValidStatuses returns all valid status values
     * Requirement 4.1
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
}
