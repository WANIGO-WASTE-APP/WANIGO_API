<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JadwalSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada data user dan bank sampah
        $users = DB::table('users')->where('role', 'nasabah')->pluck('id');
        $bankSampah = DB::table('bank_sampah')->pluck('id');
        $tipeJadwal = DB::table('tipe_jadwal_sampah')->pluck('id');

        if ($users->isEmpty() || $bankSampah->isEmpty() || $tipeJadwal->isEmpty()) {
            $this->command->warn('Tidak ada data user, bank sampah, atau tipe jadwal. Seeder dibatalkan.');
            return;
        }

        $jadwalSampah = [];
        $now = Carbon::now();

        // Jadwal untuk beberapa user
        foreach ($users->take(3) as $index => $userId) {
            $bankSampahId = $bankSampah->random();
            
            // Jadwal Pemilahan Sampah (tipe_jadwal_id = 0 atau yang pertama)
            $jadwalSampah[] = [
                'user_id' => $userId,
                'bank_sampah_id' => $bankSampahId,
                'tipe_jadwal_id' => $tipeJadwal->first(), // Pemilahan Sampah
                'frekuensi' => 'harian',
                'waktu_mulai' => '07:00:00',
                'tanggal_mulai' => $now->copy()->addDays($index)->format('Y-m-d'),
                'status' => 'belum selesai',
                'nomor_urut' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Jadwal Pemilahan Sampah kedua (mingguan)
            $jadwalSampah[] = [
                'user_id' => $userId,
                'bank_sampah_id' => $bankSampahId,
                'tipe_jadwal_id' => $tipeJadwal->first(),
                'frekuensi' => 'mingguan',
                'waktu_mulai' => '08:00:00',
                'tanggal_mulai' => $now->copy()->addDays(7 + $index)->format('Y-m-d'),
                'status' => 'belum selesai',
                'nomor_urut' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Jadwal Rencana Setoran (tipe_jadwal_id = 1 atau yang kedua)
            if ($tipeJadwal->count() > 1) {
                $jadwalSampah[] = [
                    'user_id' => $userId,
                    'bank_sampah_id' => $bankSampahId,
                    'tipe_jadwal_id' => $tipeJadwal->skip(1)->first(), // Rencana Setoran
                    'frekuensi' => null, // Rencana setoran biasanya tidak ada frekuensi
                    'waktu_mulai' => '09:00:00',
                    'tanggal_mulai' => $now->copy()->addDays(3 + $index)->format('Y-m-d'),
                    'status' => 'belum selesai',
                    'nomor_urut' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Jadwal yang sudah selesai (untuk history)
            $jadwalSampah[] = [
                'user_id' => $userId,
                'bank_sampah_id' => $bankSampahId,
                'tipe_jadwal_id' => $tipeJadwal->first(),
                'frekuensi' => 'harian',
                'waktu_mulai' => '07:00:00',
                'tanggal_mulai' => $now->copy()->subDays(7 + $index)->format('Y-m-d'),
                'status' => 'selesai',
                'nomor_urut' => 1,
                'created_at' => $now->copy()->subDays(7),
                'updated_at' => $now->copy()->subDays(7),
            ];

            // Jadwal bulanan
            $jadwalSampah[] = [
                'user_id' => $userId,
                'bank_sampah_id' => $bankSampahId,
                'tipe_jadwal_id' => $tipeJadwal->first(),
                'frekuensi' => 'bulanan',
                'waktu_mulai' => '10:00:00',
                'tanggal_mulai' => $now->copy()->addMonth()->startOfMonth()->format('Y-m-d'),
                'status' => 'belum selesai',
                'nomor_urut' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('jadwal_sampah')->insert($jadwalSampah);

        $this->command->info('Jadwal sampah berhasil di-seed: ' . count($jadwalSampah) . ' records');
    }
}
