<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BankSampah;

class BankSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankSampahData = [
            [
                'nama_bank_sampah' => 'Bank Sampah Surabaya Bersih',
                'alamat_bank_sampah' => 'Jl. Raya Darmo No. 123, Wonokromo',
                'deskripsi' => 'Bank sampah yang melayani wilayah Wonokromo dan sekitarnya. Menerima berbagai jenis sampah organik dan anorganik dengan harga kompetitif.',
                'latitude' => -7.289167,
                'longitude' => 112.731944,
                'status_operasional' => true,
                'email' => 'info@surabayabersih.com',
                'nomor_telepon_publik' => '031-5678901',
                'jumlah_nasabah' => 150,
                'tonase_sampah' => 2500.50,
            ],
            [
                'nama_bank_sampah' => 'Bank Sampah Kenjeran Hijau',
                'alamat_bank_sampah' => 'Jl. Kenjeran No. 45, Bulak',
                'deskripsi' => 'Bank sampah di kawasan pesisir Kenjeran yang fokus pada pengelolaan sampah plastik dan kertas. Aktif dalam edukasi lingkungan.',
                'latitude' => -7.234722,
                'longitude' => 112.778889,
                'status_operasional' => true,
                'email' => 'kenjeranhijau@gmail.com',
                'nomor_telepon_publik' => '031-3456789',
                'jumlah_nasabah' => 95,
                'tonase_sampah' => 1800.75,
            ],
            [
                'nama_bank_sampah' => 'Bank Sampah Gubeng Makmur',
                'alamat_bank_sampah' => 'Jl. Gubeng Kertajaya No. 78, Gubeng',
                'deskripsi' => 'Melayani masyarakat Gubeng dan sekitarnya. Spesialisasi dalam pengelolaan sampah elektronik dan logam.',
                'latitude' => -7.281111,
                'longitude' => 112.750556,
                'status_operasional' => true,
                'email' => 'gubengmakmur@yahoo.com',
                'nomor_telepon_publik' => '031-5012345',
                'jumlah_nasabah' => 120,
                'tonase_sampah' => 3200.25,
            ],
            [
                'nama_bank_sampah' => 'Bank Sampah Tandes Sejahtera',
                'alamat_bank_sampah' => 'Jl. Tandes Kidul No. 56, Tandes',
                'deskripsi' => 'Bank sampah yang berdiri sejak 2018, melayani warga Tandes dengan sistem penjemputan sampah door-to-door.',
                'latitude' => -7.256944,
                'longitude' => 112.693611,
                'status_operasional' => true,
                'email' => 'tandessejahtera@outlook.com',
                'nomor_telepon_publik' => '031-7890123',
                'jumlah_nasabah' => 200,
                'tonase_sampah' => 4100.00,
            ],
            [
                'nama_bank_sampah' => 'Bank Sampah Rungkut Lestari',
                'alamat_bank_sampah' => 'Jl. Rungkut Asri Tengah No. 12, Rungkut',
                'deskripsi' => 'Bank sampah modern dengan fasilitas lengkap. Menerima semua jenis sampah dan memberikan edukasi daur ulang kepada masyarakat.',
                'latitude' => -7.323056,
                'longitude' => 112.768889,
                'status_operasional' => true,
                'email' => 'rungkutlestari@gmail.com',
                'nomor_telepon_publik' => '031-8765432',
                'jumlah_nasabah' => 175,
                'tonase_sampah' => 3500.80,
            ],
        ];

        foreach ($bankSampahData as $data) {
            BankSampah::create($data);
        }

        $this->command->info('✅ 5 Bank Sampah di Surabaya berhasil di-seed!');
    }
}
