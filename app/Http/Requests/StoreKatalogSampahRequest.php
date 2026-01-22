<?php

namespace App\Http\Requests;

use App\Rules\KatalogKategoriConsistency;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for creating a new KatalogSampah item.
 * 
 * This request validates that:
 * - kategori_sampah is 0 or 1 (Requirement 1.4)
 * - sub_kategori_sampah_id matches the kategori_sampah (Requirements 5.5, 6.1)
 * - All required fields are present and valid
 */
class StoreKatalogSampahRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bank_sampah_id' => 'required|exists:bank_sampah,id',
            'sub_kategori_sampah_id' => [
                'nullable',
                'exists:sub_kategori_sampah,id',
                new KatalogKategoriConsistency($this->kategori_sampah)
            ],
            'kategori_sampah' => 'required|in:0,1',
            'nama_item_sampah' => 'required|string|max:100',
            'harga_per_kg' => 'required|numeric|min:0',
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
            'bank_sampah_id.required' => 'Bank sampah wajib diisi',
            'bank_sampah_id.exists' => 'Bank sampah tidak ditemukan',
            'sub_kategori_sampah_id.exists' => 'Sub kategori sampah tidak ditemukan',
            'kategori_sampah.required' => 'Kategori sampah wajib diisi',
            'kategori_sampah.in' => 'Kategori sampah harus 0 (kering) atau 1 (basah)',
            'nama_item_sampah.required' => 'Nama item sampah wajib diisi',
            'nama_item_sampah.max' => 'Nama item sampah maksimal 100 karakter',
            'harga_per_kg.required' => 'Harga per kg wajib diisi',
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
