<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class BankSampahDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get all operating hours
        $jamOperasional = $this->jamOperasional->map(function($jam) {
            return [
                'hari' => $jam->day_name,
                'buka' => true,
                'jam_buka' => Carbon::parse($jam->open_time)->format('H:i'),
                'jam_tutup' => Carbon::parse($jam->close_time)->format('H:i'),
            ];
        });
        
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
            'alamat_lengkap' => $this->alamat_lengkap,
            'deskripsi' => $this->deskripsi,
            'insight' => $this->insight,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'status_operasional' => (bool) $this->status_operasional,
            'nomor_telepon' => $this->nomor_telepon_publik,
            'email' => $this->email,
            'foto_usaha_url' => $this->foto_usaha_url,
            'jumlah_nasabah' => (int) $this->jumlah_nasabah,
            'tonase_sampah' => (float) $this->tonase_sampah,
            'kategori_sampah' => $kategoriSampah,
            'lokasi' => [
                'provinsi' => $this->provinsi->nama_provinsi ?? null,
                'kabupaten_kota' => $this->kabupatenKota->nama_kabupaten_kota ?? null,
                'kecamatan' => $this->kecamatan->nama_kecamatan ?? null,
                'kelurahan_desa' => $this->kelurahanDesa->nama_kelurahan_desa ?? null,
            ],
            'jam_operasional' => $jamOperasional,
            'member_status' => $this->when(isset($this->member_status), $this->member_status),
            'member_data' => $this->when(isset($this->member_data), $this->member_data),
        ];
    }
}
