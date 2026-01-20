<?php

namespace Database\Seeders;

use App\Models\KatalogSampah;
use App\Models\BankSampah;
use Illuminate\Database\Seeder;

class KatalogSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first bank sampah for seeding
        $bankSampah = BankSampah::first();
        
        if (!$bankSampah) {
            $this->command->warn('No bank sampah found. Please run BankSampahSeeder first.');
            return;
        }

        $katalogData = [
            // Sampah Kering (kategori_sampah = 0)
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 1, // Plastik
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Botol Plastik PET',
                'harga_per_kg' => 3000,
                'deskripsi_item_sampah' => 'Botol plastik bekas minuman kemasan',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 1,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Plastik HDPE',
                'harga_per_kg' => 2500,
                'deskripsi_item_sampah' => 'Plastik keras seperti jerigen dan ember',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 1,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Plastik PP',
                'harga_per_kg' => 2000,
                'deskripsi_item_sampah' => 'Plastik polypropylene seperti gelas plastik',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 2, // Kertas
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Kertas HVS',
                'harga_per_kg' => 1500,
                'deskripsi_item_sampah' => 'Kertas putih bekas print atau fotokopi',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 2,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Kardus',
                'harga_per_kg' => 1200,
                'deskripsi_item_sampah' => 'Kardus bekas kemasan',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 2,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Koran',
                'harga_per_kg' => 1000,
                'deskripsi_item_sampah' => 'Koran bekas',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 2,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Majalah',
                'harga_per_kg' => 800,
                'deskripsi_item_sampah' => 'Majalah dan buku bekas',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 3, // Logam
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Kaleng Aluminium',
                'harga_per_kg' => 5000,
                'deskripsi_item_sampah' => 'Kaleng minuman aluminium',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 3,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Besi',
                'harga_per_kg' => 3500,
                'deskripsi_item_sampah' => 'Besi bekas',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 3,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Tembaga',
                'harga_per_kg' => 45000,
                'deskripsi_item_sampah' => 'Kabel tembaga bekas',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 4, // Kaca
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Botol Kaca Bening',
                'harga_per_kg' => 1000,
                'deskripsi_item_sampah' => 'Botol kaca bening bekas',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 4,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Botol Kaca Warna',
                'harga_per_kg' => 800,
                'deskripsi_item_sampah' => 'Botol kaca berwarna bekas',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 5, // Elektronik
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Kabel Listrik',
                'harga_per_kg' => 8000,
                'deskripsi_item_sampah' => 'Kabel listrik bekas',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 5,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Komponen Elektronik',
                'harga_per_kg' => 15000,
                'deskripsi_item_sampah' => 'PCB dan komponen elektronik bekas',
                'status_aktif' => true,
            ],
            // Sampah Basah (kategori_sampah = 1)
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 6, // Organik
                'kategori_sampah' => 1,
                'nama_item_sampah' => 'Sisa Sayuran',
                'harga_per_kg' => 500,
                'deskripsi_item_sampah' => 'Sisa sayuran untuk kompos',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 6,
                'kategori_sampah' => 1,
                'nama_item_sampah' => 'Sisa Buah',
                'harga_per_kg' => 500,
                'deskripsi_item_sampah' => 'Sisa buah untuk kompos',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 6,
                'kategori_sampah' => 1,
                'nama_item_sampah' => 'Daun Kering',
                'harga_per_kg' => 300,
                'deskripsi_item_sampah' => 'Daun kering untuk kompos',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 1,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Plastik Campur',
                'harga_per_kg' => 1500,
                'deskripsi_item_sampah' => 'Plastik campur berbagai jenis',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 2,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Kertas Campur',
                'harga_per_kg' => 700,
                'deskripsi_item_sampah' => 'Kertas campur berbagai jenis',
                'status_aktif' => true,
            ],
            [
                'bank_sampah_id' => $bankSampah->id,
                'sub_kategori_sampah_id' => 3,
                'kategori_sampah' => 0,
                'nama_item_sampah' => 'Logam Campur',
                'harga_per_kg' => 2500,
                'deskripsi_item_sampah' => 'Logam campur berbagai jenis',
                'status_aktif' => true,
            ],
        ];

        foreach ($katalogData as $data) {
            // Check if item already exists
            $existing = KatalogSampah::where('bank_sampah_id', $data['bank_sampah_id'])
                ->where('nama_item_sampah', $data['nama_item_sampah'])
                ->first();
            
            if (!$existing) {
                KatalogSampah::create($data);
            }
        }
    }
}
