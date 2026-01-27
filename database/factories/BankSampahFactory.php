<?php

namespace Database\Factories;

use App\Models\BankSampah;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankSampahFactory extends Factory
{
    protected $model = BankSampah::class;

    public function definition(): array
    {
        return [
            'nama_bank_sampah' => $this->faker->company . ' Bank Sampah',
            'alamat_bank_sampah' => $this->faker->address,
            'latitude' => $this->faker->latitude(-10, 10),
            'longitude' => $this->faker->longitude(95, 141),
            'status_operasional' => true,
            'nomor_telepon_publik' => $this->faker->phoneNumber,
            'email' => $this->faker->safeEmail,
            'tonase_sampah' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
