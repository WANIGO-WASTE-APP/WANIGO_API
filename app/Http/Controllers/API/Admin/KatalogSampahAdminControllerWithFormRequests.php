<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKatalogSampahRequest;
use App\Http\Requests\UpdateKatalogSampahRequest;
use App\Models\KatalogSampah;
use Illuminate\Http\Request;

/**
 * Admin controller for managing KatalogSampah using Form Requests.
 * 
 * This is an alternative implementation that uses Form Request classes
 * for validation instead of inline validation. This approach is cleaner
 * and follows Laravel best practices.
 * 
 * Requirements: 1.4, 5.5, 6.1, 6.2
 */
class KatalogSampahAdminControllerWithFormRequests extends Controller
{
    /**
     * Store a new katalog sampah item.
     * 
     * Uses StoreKatalogSampahRequest for validation, which includes:
     * - kategori_sampah validation (0 or 1) - Requirement 1.4
     * - KatalogKategoriConsistency rule - Requirements 5.5, 6.1
     * 
     * @param StoreKatalogSampahRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreKatalogSampahRequest $request)
    {
        // Validation is automatically handled by the Form Request
        $katalog = KatalogSampah::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Katalog sampah berhasil dibuat',
            'data' => $katalog
        ], 201);
    }

    /**
     * Update an existing katalog sampah item.
     * 
     * Uses UpdateKatalogSampahRequest for validation, which includes:
     * - kategori_sampah validation (0 or 1) - Requirement 1.4
     * - KatalogKategoriConsistency rule - Requirements 5.5, 6.2
     * - Re-validates category consistency on update - Requirement 6.2
     * 
     * @param UpdateKatalogSampahRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateKatalogSampahRequest $request, $id)
    {
        $katalog = KatalogSampah::findOrFail($id);
        
        // Validation is automatically handled by the Form Request
        $katalog->update($request->validated());

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
        $request->validate([
            'bank_sampah_id' => 'required|exists:bank_sampah,id',
            'kategori' => 'sometimes|in:kering,basah,semua',
            'status_aktif' => 'sometimes|boolean',
        ]);

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
