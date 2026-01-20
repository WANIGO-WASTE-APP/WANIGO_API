<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class BankSampahListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get today's operating hours
        $hariIni = now()->dayOfWeek;
        $jamHariIni = $this->jamOperasional->firstWhere('day_of_week', $hariIni);
        
        // Determine accepted waste categories
        $kategoriSampah = $this->katalogSampah
            ->where('status_aktif', true)
            ->pluck('kategori_sampah')
            ->unique()
            ->map(function($kategori) {
                return $kategori == 0 ? 'kering' : 'basah';
            })
            ->values()
            ->toArray();
        
        return [
            'id' => $this->id,
            'nama_bank_sampah' => $this->nama_bank_sampah,
            'alamat' => $this->alamat_bank_sampah,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'distance_km' => $this->when(isset($this->distance_km), round($this->distance_km, 2)),
            'status_operasional' => (bool) $this->status_operasional,
            'nomor_telepon' => $this->nomor_telepon_publik,
            'email' => $this->email,
            'foto_usaha_url' => $this->foto_usaha_url,
            'insight' => $this->insight,
            'kategori_sampah' => $kategoriSampah,
            'jam_operasional_hari_ini' => [
                'buka' => $jamHariIni ? $jamHariIni->isBuka() : false,
                'jam_buka' => $jamHariIni ? Carbon::parse($jamHariIni->open_time)->format('H:i') : null,
                'jam_tutup' => $jamHariIni ? Carbon::parse($jamHariIni->close_time)->format('H:i') : null,
            ],
            'member_status' => $this->when(isset($this->member_status), $this->member_status),
        ];
    }
}
