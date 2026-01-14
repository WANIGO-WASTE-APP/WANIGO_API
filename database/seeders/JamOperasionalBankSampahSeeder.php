<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JamOperasionalBankSampah;
use App\Models\BankSampah;

class JamOperasionalBankSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all bank sampah
        $bankSampahList = BankSampah::all();

        if ($bankSampahList->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada bank sampah. Jalankan BankSampahSeeder terlebih dahulu!');
            return;
        }

        // Jam operasional standar untuk setiap bank sampah
        // 0 = Minggu, 1 = Senin, ..., 6 = Sabtu
        $jamOperasionalTemplate = [
            // Senin - Jumat
            ['day_of_week' => 1, 'open_time' => '08:00:00', 'close_time' => '16:00:00'],
            ['day_of_week' => 2, 'open_time' => '08:00:00', 'close_time' => '16:00:00'],
            ['day_of_week' => 3, 'open_time' => '08:00:00', 'close_time' => '16:00:00'],
            ['day_of_week' => 4, 'open_time' => '08:00:00', 'close_time' => '16:00:00'],
            ['day_of_week' => 5, 'open_time' => '08:00:00', 'close_time' => '16:00:00'],
            // Sabtu (setengah hari)
            ['day_of_week' => 6, 'open_time' => '08:00:00', 'close_time' => '12:00:00'],
        ];

        $count = 0;
        foreach ($bankSampahList as $bankSampah) {
            foreach ($jamOperasionalTemplate as $jam) {
                JamOperasionalBankSampah::create([
                    'bank_sampah_id' => $bankSampah->id,
                    'day_of_week' => $jam['day_of_week'],
                    'open_time' => $jam['open_time'],
                    'close_time' => $jam['close_time'],
                ]);
                $count++;
            }
        }

        $this->command->info("✅ {$count} Jam Operasional berhasil di-seed untuk {$bankSampahList->count()} bank sampah!");
        $this->command->info('   Operasional: Senin-Jumat (08:00-16:00), Sabtu (08:00-12:00)');
    }
}
