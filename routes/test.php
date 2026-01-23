<?php

use Illuminate\Support\Facades\Route;
use App\Models\KatalogSampah;
use App\Http\Resources\KatalogSampahResource;

Route::get('/test-katalog', function() {
    try {
        // Test 1: Check if katalog exists
        $count = KatalogSampah::where('bank_sampah_id', 1)->count();
        
        // Test 2: Get one katalog with relationship
        $katalog = KatalogSampah::with('subKategoriSampah')
            ->where('bank_sampah_id', 1)
            ->first();
        
        // Test 3: Test resource
        $resource = $katalog ? new KatalogSampahResource($katalog) : null;
        
        return response()->json([
            'test' => 'success',
            'katalog_count' => $count,
            'katalog_found' => $katalog ? true : false,
            'katalog_data' => $katalog,
            'resource_data' => $resource,
            'sub_kategori_loaded' => $katalog && $katalog->subKategoriSampah ? true : false
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'test' => 'failed',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
