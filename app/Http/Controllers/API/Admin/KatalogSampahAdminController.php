<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\KatalogSampah;
use App\Rules\KatalogKategoriConsistency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Admin controller for managing KatalogSampah (Waste Catalog) items.
 * 
 * This controller demonstrates proper validation using the KatalogKategoriConsistency rule
 * to ensure that katalog items match their sub-category's waste type.
 * 
 * Requirements: 1.4, 5.5, 6.1, 6.2
 */
class KatalogSampahAdminController extends Controller
{
    /**
     * Store a new katalog sampah item.
     * 
     * Validates that:
     * - kategori_sampah is 0 or 1 (Requirement 1.4)
     * - sub_kategori_sampah_id matches the kategori_sampah (Requirements 5.5, 6.1)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_sampah_id' => 'required|exists:bank_sampah,id',
            'sub_kategori_sampah_id' => [
                'nullable',
                'exists:sub_kategori_sampah,id',
                new KatalogKategoriConsistency($request->kategori_sampah)
            ],
            'kategori_sampah' => 'required|in:0,1',
            'nama_item_sampah' => 'required|string|max:100',
            'harga_per_kg' => 'required|numeric|min:0',
            'deskripsi_item_sampah' => 'nullable|string',
            'cara_pemilahan' => 'nullable|string',
            'cara_pengemasahan' => 'nullable|string',
            'gambar_item_sampah' => 'nullable|string',
            'status_aktif' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $katalog = KatalogSampah::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Katalog sampah berhasil dibuat',
            'data' => $katalog
        ], 201);
    }

    /**
     * Update an existing katalog sampah item.
     * 
     * Re-validates category consistency when updating (Requirement 6.2).
     * Uses the existing kategori_sampah if not provided in the request.
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $katalog = KatalogSampah::findOrFail($id);

        // Use existing kategori_sampah if not provided in request
        $kategoriSampah = $request->kategori_sampah ?? $katalog->kategori_sampah;

        $validator = Validator::make($request->all(), [
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $katalog->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Katalog sampah berhasil diperbarui',
            'data' => $katalog
        ]);
    }

    /**
     * Delete a katalog sampah item.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $katalog = KatalogSampah::findOrFail($id);
        $katalog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Katalog sampah berhasil dihapus'
        ]);
    }

    /**
     * Get all katalog sampah items for a specific bank sampah.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_sampah_id' => 'required|exists:bank_sampah,id',
            'kategori' => 'sometimes|in:kering,basah,semua',
            'status_aktif' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = KatalogSampah::with(['subKategoriSampah', 'bankSampah'])
            ->where('bank_sampah_id', $request->bank_sampah_id);

        // Filter by kategori if provided
        if ($request->has('kategori') && $request->kategori !== 'semua') {
            $query->byKategori($request->kategori);
        }

        // Filter by status_aktif if provided
        if ($request->has('status_aktif')) {
            $query->where('status_aktif', $request->status_aktif);
        }

        $katalog = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Katalog sampah berhasil diambil',
            'data' => $katalog
        ]);
    }

    /**
     * Get a single katalog sampah item.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $katalog = KatalogSampah::with(['subKategoriSampah', 'bankSampah'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail katalog sampah berhasil diambil',
            'data' => $katalog
        ]);
    }
}
