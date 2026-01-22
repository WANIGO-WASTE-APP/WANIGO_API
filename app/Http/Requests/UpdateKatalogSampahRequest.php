<?php

namespace App\Http\Requests;

use App\Rules\KatalogKategoriConsistency;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating an existing KatalogSampah item.
 * 
 * This request validates that:
 * - kategori_sampah is 0 or 1 if provided (Requirement 1.4)
 * - sub_kategori_sampah_id matches the kategori_sampah (Requirements 5.5, 6.2)
 * - Re-validates category consistency on update (Requirement 6.2)
 */
class UpdateKatalogSampahRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Add authorization logic here if needed
        return true;
    }

    /**
     * Prepare the data for validation.
     * 
     * This method runs before validation and allows us to get the existing
     * katalog item to use its kategori_sampah if not provided in the request.
     */
    protected function prepareForValidation(): void
    {
        // If kategori_sampah is not in the request, we'll need to get it from the model
        // This is handled in the rules() method using the route parameter
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the katalog item from the route parameter to access existing kategori_sampah
        $katalogId = $this->route('id') ?? $this->route('katalog_sampah');
        $katalog = \App\Models\KatalogSampah::find($katalogId);
        
        // Use the request's kategori_sampah if provided, otherwise use the existing one
        $kategoriSampah = $this->kategori_sampah ?? ($katalog ? $katalog->kategori_sampah : null);

        return [
            'bank_sampah_id' => 'sometimes|exists:bank_sampah,id',
            'sub_kategori_sampah_id' => [
                'nullable',
                'exists:sub_kategori_sampah,id',
                new KatalogKategoriConsistency($kategoriSampah)
            ],
            'kategori_sampah' => 'sometimes|in:0,1',
            'nama_item_sampah' => 'sometimes|string|max:100',
            'harga_per_kg' => 'sometimes|numeric|min:0',
            'deskripsi_item_sampah' => 'nullable|string',
            'cara_pemilahan' => 'nullable|string',
            'cara_pengemasahan' => 'nullable|string',
            'gambar_item_sampah' => 'nullable|string',
            'status_aktif' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bank_sampah_id.exists' => 'Bank sampah tidak ditemukan',
            'sub_kategori_sampah_id.exists' => 'Sub kategori sampah tidak ditemukan',
            'kategori_sampah.in' => 'Kategori sampah harus 0 (kering) atau 1 (basah)',
            'nama_item_sampah.max' => 'Nama item sampah maksimal 100 karakter',
            'harga_per_kg.numeric' => 'Harga per kg harus berupa angka',
            'harga_per_kg.min' => 'Harga per kg tidak boleh negatif',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'bank_sampah_id' => 'bank sampah',
            'sub_kategori_sampah_id' => 'sub kategori sampah',
            'kategori_sampah' => 'kategori sampah',
            'nama_item_sampah' => 'nama item sampah',
            'harga_per_kg' => 'harga per kg',
            'deskripsi_item_sampah' => 'deskripsi item sampah',
            'cara_pemilahan' => 'cara pemilahan',
            'cara_pengemasahan' => 'cara pengemasahan',
            'gambar_item_sampah' => 'gambar item sampah',
            'status_aktif' => 'status aktif',
        ];
    }
}
