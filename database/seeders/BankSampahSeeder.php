<?php

namespace Database\Seeders;

use App\Models\BankSampah;
use App\Models\JamOperasionalBankSampah;
use Illuminate\Database\Seeder;

class BankSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankSampahData = [
            [
                'nama_bank_sampah' => 'Bank Sampah Mojo',
                'alamat_bank_sampah' => 'Jl. Mojo No. 123',
                'deskripsi' => 'Bank sampah terbesar di Surabaya dengan layanan terbaik',
                'insight' => 'Hanya menerima sampah kering. Minimal setoran 5kg. Buka setiap hari kecuali hari libur nasional.',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'status_operasional' => true,
                'email' => 'mojo@banksampah.com',
                'nomor_telepon_publik' => '081234567890',
                'provinsi_id' => null, // Will be set later if needed
                'kabupaten_kota_id' => null, // Will be set later if needed
            ],
            [
                'nama_bank_sampah' => 'Bank Sampah Hijau Lestari',
                'alamat_bank_sampah' => 'Jl. Raya Darmo No. 45',
                'deskripsi' => 'Melayani masyarakat dengan ramah dan profesional',
                'insight' => 'Menerima sampah kering dan basah. Buka Senin-Sabtu pukul 08:00-16:00.',
                'latitude' => -7.2650,
                'longitude' => 112.7400,
                'status_operasional' => true,
                'email' => 'hijaulestari@banksampah.com',
                'nomor_telepon_publik' => '081234567891',
                'provinsi_id' => null,
                'kabupaten_kota_id' => null,
            ],
            [
                'nama_bank_sampah' => 'Bank Sampah Bersih Sehat',
                'alamat_bank_sampah' => 'Jl. Ahmad Yani No. 78',
                'deskripsi' => 'Bersama menjaga lingkungan untuk masa depan',
                'insight' => 'Hanya menerima sampah kering. Minimal setoran 3kg. Layanan jemput sampah tersedia.',
                'latitude' => -7.2800,
                'longitude' => 112.7600,
                'status_operasional' => true,
                'email' => 'bersihsehat@banksampah.com',
                'nomor_telepon_publik' => '081234567892',
                'provinsi_id' => null,
                'kabupaten_kota_id' => null,
            ],
            [
                'nama_bank_sampah' => 'Bank Sampah Mandiri',
                'alamat_bank_sampah' => 'Jl. Diponegoro No. 12',
                'deskripsi' => 'Membangun kemandirian melalui pengelolaan sampah',
                'insight' => 'Menerima semua jenis sampah kering. Harga kompetitif. Buka setiap hari.',
                'latitude' => -7.2700,
                'longitude' => 112.7300,
                'status_operasional' => true,
                'email' => 'mandiri@banksampah.com',
                'nomor_telepon_publik' => '081234567893',
                'provinsi_id' => null,
                'kabupaten_kota_id' => null,
            ],
            [
                'nama_bank_sampah' => 'Bank Sampah Sejahtera',
                'alamat_bank_sampah' => 'Jl. Basuki Rahmat No. 99',
                'deskripsi' => 'Sejahtera bersama dengan mengelola sampah',
                'insight' => 'Menerima sampah kering dan elektronik. Minimal setoran 2kg. Pembayaran langsung.',
                'latitude' => -7.2550,
                'longitude' => 112.7450,
                'status_operasional' => true,
                'email' => 'sejahtera@banksampah.com',
                'nomor_telepon_publik' => '081234567894',
                'provinsi_id' => null,
                'kabupaten_kota_id' => null,
            ],
        ];

        foreach ($bankSampahData as $data) {
            // Check if bank sampah already exists
            $existing = BankSampah::where('nama_bank_sampah', $data['nama_bank_sampah'])->first();
            
            if (!$existing) {
                $bankSampah = BankSampah::create($data);
                
                // Create jam operasional (Senin-Sabtu 08:00-16:00)
                for ($day = 1; $day <= 6; $day++) {
                    JamOperasionalBankSampah::create([
                        'bank_sampah_id' => $bankSampah->id,
                        'day_of_week' => $day,
                        'open_time' => '08:00:00',
                        'close_time' => '16:00:00',
                    ]);
                }
            }
        }
    }
}
