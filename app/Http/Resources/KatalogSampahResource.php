<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KatalogSampahResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama_item_sampah,
            'harga' => (float) $this->harga_per_kg,
            'deskripsi' => $this->deskripsi_item_sampah,
            'gambar_url' => $this->gambar_item_sampah_url,
            
            // Backward compatibility field
            'kategori_sampah' => $this->kategori_sampah_text,
            
            // Sub-kategori information (handle null gracefully)
            'sub_kategori' => $this->when($this->subKategoriSampah, function() {
                return [
                    'id' => $this->subKategoriSampah->id,
                    'nama' => $this->subKategoriSampah->nama_sub_kategori,
                    'slug' => $this->subKategoriSampah->slug,
                    'icon' => $this->subKategoriSampah->icon,
                    'warna' => $this->subKategoriSampah->warna,
                ];
            }),
        ];
    }
}
