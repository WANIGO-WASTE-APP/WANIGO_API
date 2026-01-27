<?php

namespace App\Http\Resources;

use App\Traits\ResolvesContactInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * BankSampahListResource
 * 
 * API resource for Bank Sampah list endpoints.
 * Excludes tonase_sampah field for optimized payload size.
 * Includes normalized contact_info and deprecated fields for backward compatibility.
 * 
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.7, 3.1
 */
class BankSampahListResource extends JsonResource
{
    use ResolvesContactInfo;

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
            
            // Normalized contact information (Requirement 2.1, 2.2, 2.3)
            'contact_info' => [
                'phone' => $this->resolvePhone(),
                'email' => $this->email,
            ],
            
            // Apply default image URL fallback (Requirement 2.7)
            'foto_usaha_url' => $this->foto_usaha 
                ? (filter_var($this->foto_usaha, FILTER_VALIDATE_URL) 
                    ? $this->foto_usaha 
                    : url('storage/bank_sampah/' . $this->foto_usaha))
                : config('app.default_bank_image_url'),
            
            'insight' => $this->insight,
            'kategori_sampah' => $kategoriSampah,
            'jam_operasional_hari_ini' => [
                'buka' => $jamHariIni ? $jamHariIni->isBuka() : false,
                'jam_buka' => $jamHariIni ? Carbon::parse($jamHariIni->open_time)->format('H:i') : null,
                'jam_tutup' => $jamHariIni ? Carbon::parse($jamHariIni->close_time)->format('H:i') : null,
            ],
            'member_status' => $this->when(isset($this->member_status), $this->member_status),
            
            // Deprecated fields for backward compatibility (Requirement 2.4)
            // These will be removed in the next release
            '@deprecated' => [
                'nomor_telepon' => $this->nomor_telepon ?? null,
                'nomor_telepon_publik' => $this->nomor_telepon_publik,
            ],
        ];
    }
}
