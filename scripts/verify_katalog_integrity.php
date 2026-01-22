<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Verifying Katalog Sampah Data Integrity ===\n\n";

// Check katalog_sampah table
$totalKatalog = DB::table('katalog_sampah')->count();
echo "1. Total katalog_sampah records: $totalKatalog\n";

if ($totalKatalog > 0) {
    // Check for orphaned references
    echo "\n2. Checking for orphaned sub_kategori_sampah_id references...\n";
    $orphaned = DB::table('katalog_sampah as k')
        ->leftJoin('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
        ->whereNotNull('k.sub_kategori_sampah_id')
        ->whereNull('sk.id')
        ->count();
    
    if ($orphaned > 0) {
        echo "   ⚠️  WARNING: Found $orphaned orphaned katalog records\n";
        
        // Show details
        $orphanedRecords = DB::table('katalog_sampah as k')
            ->leftJoin('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
            ->whereNotNull('k.sub_kategori_sampah_id')
            ->whereNull('sk.id')
            ->select('k.id', 'k.nama_item_sampah', 'k.sub_kategori_sampah_id')
            ->limit(5)
            ->get();
        
        foreach ($orphanedRecords as $record) {
            echo "      - Katalog ID: {$record->id}, Item: {$record->nama_item_sampah}, Invalid Sub-Kategori ID: {$record->sub_kategori_sampah_id}\n";
        }
    } else {
        echo "   ✓ No orphaned references found\n";
    }
    
    // Check category consistency
    echo "\n3. Checking category consistency between katalog and sub_kategori...\n";
    $inconsistent = DB::table('katalog_sampah as k')
        ->join('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
        ->whereRaw('k.kategori_sampah != sk.kategori_sampah')
        ->count();
    
    if ($inconsistent > 0) {
        echo "   ⚠️  WARNING: Found $inconsistent records with category mismatch\n";
        
        // Show details
        $mismatchRecords = DB::table('katalog_sampah as k')
            ->join('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
            ->whereRaw('k.kategori_sampah != sk.kategori_sampah')
            ->select('k.id', 'k.nama_item_sampah', 'k.kategori_sampah as katalog_kategori', 'sk.kategori_sampah as sub_kategori_kategori', 'sk.nama_sub_kategori')
            ->limit(5)
            ->get();
        
        foreach ($mismatchRecords as $record) {
            echo "      - Katalog ID: {$record->id}, Item: {$record->nama_item_sampah}\n";
            echo "        Katalog kategori: {$record->katalog_kategori}, Sub-kategori kategori: {$record->sub_kategori_kategori}\n";
            echo "        Sub-kategori: {$record->nama_sub_kategori}\n";
        }
    } else {
        echo "   ✓ All katalog records have consistent categories with their sub-kategori\n";
    }
    
    // Sample katalog records
    echo "\n4. Sample katalog records (first 5):\n";
    $samples = DB::table('katalog_sampah as k')
        ->leftJoin('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
        ->select('k.id', 'k.nama_item_sampah', 'k.kategori_sampah', 'k.sub_kategori_sampah_id', 'sk.nama_sub_kategori', 'sk.slug')
        ->limit(5)
        ->get();
    
    foreach ($samples as $record) {
        echo "\n   Katalog ID: {$record->id}\n";
        echo "   - Item: {$record->nama_item_sampah}\n";
        echo "   - Kategori: {$record->kategori_sampah}\n";
        echo "   - Sub-Kategori ID: " . ($record->sub_kategori_sampah_id ?? 'NULL') . "\n";
        echo "   - Sub-Kategori: " . ($record->nama_sub_kategori ?? 'N/A') . " (" . ($record->slug ?? 'N/A') . ")\n";
    }
    
    // Statistics
    echo "\n5. Statistics:\n";
    $withSubKategori = DB::table('katalog_sampah')->whereNotNull('sub_kategori_sampah_id')->count();
    $withoutSubKategori = DB::table('katalog_sampah')->whereNull('sub_kategori_sampah_id')->count();
    
    echo "   - Katalog with sub_kategori: $withSubKategori\n";
    echo "   - Katalog without sub_kategori: $withoutSubKategori\n";
    
    $kering = DB::table('katalog_sampah')->where('kategori_sampah', 0)->count();
    $basah = DB::table('katalog_sampah')->where('kategori_sampah', 1)->count();
    
    echo "   - Kering (0): $kering\n";
    echo "   - Basah (1): $basah\n";
    
} else {
    echo "   ℹ️  No records found in katalog_sampah table\n";
}

echo "\n=== Verification Complete ===\n";

// Summary
if ($totalKatalog > 0) {
    if ($orphaned == 0 && $inconsistent == 0) {
        echo "\n✅ All katalog_sampah data integrity checks passed!\n";
        echo "   Ready to proceed to Phase 3 (add constraints) and Phase 4 (add FK).\n";
    } else {
        echo "\n⚠️  Data integrity issues found. Please fix before proceeding to Phase 3 & 4.\n";
    }
} else {
    echo "\n✅ No katalog data to verify. Ready to proceed to Phase 3.\n";
}
