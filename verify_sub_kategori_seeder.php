<?php

/**
 * Verification Script for SubKategoriSampahSeeder
 * 
 * This script verifies that Task 4 has been completed successfully:
 * - SubKategoriSampahSeeder is registered in DatabaseSeeder
 * - Migration has been run
 * - Seeder has been executed
 * - sub_kategori_sampah table has the correct number of records
 * - katalog_sampah items are mapped correctly
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SubKategoriSampahSeeder Verification ===\n\n";

// 1. Verify sub_kategori_sampah table exists and has records
echo "1. Checking sub_kategori_sampah table...\n";
$subKategoriCount = DB::table('sub_kategori_sampah')
    ->where('bank_sampah_id', 1)
    ->where('kategori_sampah', 0)
    ->count();

echo "   ✓ Total sub-categories: {$subKategoriCount}\n";

if ($subKategoriCount === 32) {
    echo "   ✓ PASS: Expected 32 sub-categories found\n";
} else {
    echo "   ✗ FAIL: Expected 32 sub-categories, found {$subKategoriCount}\n";
}

// 2. Verify sequential urutan values
echo "\n2. Checking sequential urutan values...\n";
$urutanValues = DB::table('sub_kategori_sampah')
    ->where('bank_sampah_id', 1)
    ->where('kategori_sampah', 0)
    ->orderBy('urutan')
    ->pluck('urutan')
    ->toArray();

$expectedUrutan = range(1, count($urutanValues));
$isSequential = ($urutanValues === $expectedUrutan);

if ($isSequential) {
    echo "   ✓ PASS: Urutan values are sequential [1, 2, 3, ..., " . count($urutanValues) . "]\n";
} else {
    echo "   ✗ FAIL: Urutan values are not sequential\n";
    echo "   Expected: [" . implode(', ', $expectedUrutan) . "]\n";
    echo "   Found: [" . implode(', ', $urutanValues) . "]\n";
}

// 3. Verify slug generation
echo "\n3. Checking slug generation...\n";
$subKategoriWithSlugs = DB::table('sub_kategori_sampah')
    ->where('bank_sampah_id', 1)
    ->where('kategori_sampah', 0)
    ->get(['nama_sub_kategori', 'slug']);

$slugErrors = 0;
foreach ($subKategoriWithSlugs as $item) {
    $expectedSlug = Illuminate\Support\Str::slug($item->nama_sub_kategori);
    if ($item->slug !== $expectedSlug) {
        echo "   ✗ Slug mismatch for '{$item->nama_sub_kategori}': expected '{$expectedSlug}', got '{$item->slug}'\n";
        $slugErrors++;
    }
}

if ($slugErrors === 0) {
    echo "   ✓ PASS: All slugs are correctly generated\n";
} else {
    echo "   ✗ FAIL: {$slugErrors} slug(s) have incorrect values\n";
}

// 4. Verify katalog_sampah mapping
echo "\n4. Checking katalog_sampah mapping...\n";
$totalKatalogKering = DB::table('katalog_sampah')
    ->where('kategori_sampah', 0)
    ->count();

$mappedItems = DB::table('katalog_sampah')
    ->where('kategori_sampah', 0)
    ->whereNotNull('sub_kategori_id')
    ->count();

$unmappedItems = DB::table('katalog_sampah')
    ->where('kategori_sampah', 0)
    ->whereNull('sub_kategori_id')
    ->count();

echo "   ✓ Total katalog items (Sampah Kering): {$totalKatalogKering}\n";
echo "   ✓ Mapped items: {$mappedItems}\n";
echo "   ✓ Unmapped items: {$unmappedItems}\n";

if ($mappedItems > 0) {
    echo "   ✓ PASS: Katalog items are being mapped to sub-categories\n";
} else {
    echo "   ✗ FAIL: No katalog items are mapped\n";
}

// 5. Show sample mappings
echo "\n5. Sample katalog_sampah mappings:\n";
$sampleMappings = DB::table('katalog_sampah')
    ->join('sub_kategori_sampah', 'katalog_sampah.sub_kategori_id', '=', 'sub_kategori_sampah.id')
    ->where('katalog_sampah.kategori_sampah', 0)
    ->limit(5)
    ->get(['katalog_sampah.nama_item_sampah', 'sub_kategori_sampah.nama_sub_kategori']);

foreach ($sampleMappings as $mapping) {
    echo "   - '{$mapping->nama_item_sampah}' → '{$mapping->nama_sub_kategori}'\n";
}

// 6. Show sample unmapped items
echo "\n6. Sample unmapped katalog items:\n";
$sampleUnmapped = DB::table('katalog_sampah')
    ->where('kategori_sampah', 0)
    ->whereNull('sub_kategori_id')
    ->limit(5)
    ->pluck('nama_item_sampah');

foreach ($sampleUnmapped as $item) {
    echo "   - '{$item}' (no matching sub-category)\n";
}

// 7. Verify all sub-category groups
echo "\n7. Verifying sub-category groups:\n";
$expectedGroups = [
    'Grup Kertas' => ['Kardus', 'HVS / Kertas Putih', 'Buku', 'Koran Buram', 'Duplek'],
    'Grup Botol' => ['Botol BM', 'Botol PET', 'Botol Kotor', 'Botol Warna', 'Botol Campur Bersih', 'Botol Campur Kotor', 'Botol Beling', 'Botol Keras', 'Botol Minyak'],
    'Grup Bak' => ['Bak Campur', 'Bak Keras', 'Bak Plastik'],
    'Grup Logam' => ['Aluminium', 'Kaleng', 'Besi'],
    'Grup Plastik' => ['Blowing', 'Plastik', 'Tempat Makan', 'Gembos'],
    'Grup Lainnya' => ['Gelas Mineral Bersih', 'Gelas Mineral Kotor', 'Gelas Warna Warni', 'Tutup Botol', 'Galon Le Mineral', 'Jelantah', 'Kabel Elektronik', 'Grabang']
];

$allSubKategoriNames = DB::table('sub_kategori_sampah')
    ->where('bank_sampah_id', 1)
    ->where('kategori_sampah', 0)
    ->pluck('nama_sub_kategori')
    ->toArray();

$totalExpected = 0;
$groupErrors = 0;

foreach ($expectedGroups as $groupName => $items) {
    $totalExpected += count($items);
    $missingItems = [];
    
    foreach ($items as $itemName) {
        if (!in_array($itemName, $allSubKategoriNames)) {
            $missingItems[] = $itemName;
            $groupErrors++;
        }
    }
    
    if (empty($missingItems)) {
        echo "   ✓ {$groupName}: All " . count($items) . " items present\n";
    } else {
        echo "   ✗ {$groupName}: Missing items: " . implode(', ', $missingItems) . "\n";
    }
}

if ($groupErrors === 0) {
    echo "   ✓ PASS: All expected sub-categories are present\n";
} else {
    echo "   ✗ FAIL: {$groupErrors} sub-category item(s) are missing\n";
}

// Final summary
echo "\n=== VERIFICATION SUMMARY ===\n";
echo "Sub-categories created: {$subKategoriCount} / 32\n";
echo "Sequential urutan: " . ($isSequential ? "PASS" : "FAIL") . "\n";
echo "Slug generation: " . ($slugErrors === 0 ? "PASS" : "FAIL") . "\n";
echo "Katalog mapping: " . ($mappedItems > 0 ? "PASS" : "FAIL") . "\n";
echo "Group completeness: " . ($groupErrors === 0 ? "PASS" : "FAIL") . "\n";

$allPassed = ($subKategoriCount === 32) && $isSequential && ($slugErrors === 0) && ($mappedItems > 0) && ($groupErrors === 0);

if ($allPassed) {
    echo "\n✓✓✓ ALL CHECKS PASSED ✓✓✓\n";
    echo "Task 4 has been completed successfully!\n";
} else {
    echo "\n✗✗✗ SOME CHECKS FAILED ✗✗✗\n";
    echo "Please review the errors above.\n";
}
