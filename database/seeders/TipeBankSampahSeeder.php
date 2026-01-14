<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipeBankSampah;

class TipeBankSampahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipeBankSampah = [
            [
                'kode_tipe' => 'INDUK',
                'nama_tipe' => 'Bank Sampah Induk',
                'deskripsi' => 'Bank sampah pusat yang mengelola dan mengkoordinir beberapa bank sampah unit di wilayahnya.',
            ],
            [
                'kode_tipe' => 'UNIT',
                'nama_tipe' => 'Bank Sampah Unit',
                'deskripsi' => 'Bank sampah tingkat kelurahan/desa yang melayani masyarakat setempat dan menyetor ke bank sampah induk.',
            ],
            [
                'kode_tipe' => 'MANDIRI',
                'nama_tipe' => 'Bank Sampah Mandiri',
                'deskripsi' => 'Bank sampah yang beroperasi secara independen tanpa afiliasi dengan bank sampah induk.',
            ],
        ];

        foreach ($tipeBankSampah as $data) {
            TipeBankSampah::create($data);
        }

        $this->command->info('✅ 3 Tipe Bank Sampah berhasil di-seed!');
    }
}
