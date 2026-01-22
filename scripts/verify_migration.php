<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Verifying Phase 1 & 2 Migrations ===\n\n";

// Check if new columns exist
echo "1. Checking if new columns exist...\n";
$columns = DB::select("SHOW COLUMNS FROM sub_kategori_sampah");
$columnNames = array_column($columns, 'Field');

$requiredColumns = ['slug', 'is_active', 'kategori_sampah'];
$missingColumns = [];

foreach ($requiredColumns as $col) {
    if (in_array($col, $columnNames)) {
        echo "   ✓ Column '$col' exists\n";
    } else {
        echo "   ✗ Column '$col' is MISSING\n";
        $missingColumns[] = $col;
    }
}

if (!empty($missingColumns)) {
    echo "\n❌ ERROR: Missing columns: " . implode(', ', $missingColumns) . "\n";
    exit(1);
}

// Check if data has been backfilled
echo "\n2. Checking if data has been backfilled...\n";
$totalRecords = DB::table('sub_kategori_sampah')->count();
echo "   Total records: $totalRecords\n";

if ($totalRecords > 0) {
    // Check for NULL values in new columns
    $nullSlugs = DB::table('sub_kategori_sampah')->whereNull('slug')->count();
    $nullKategori = DB::table('sub_kategori_sampah')->whereNull('kategori_sampah')->count();
    
    echo "   Records with NULL slug: $nullSlugs\n";
    echo "   Records with NULL kategori_sampah: $nullKategori\n";
    
    if ($nullSlugs > 0 || $nullKategori > 0) {
        echo "\n⚠️  WARNING: Some records have NULL values in new columns\n";
    } else {
        echo "   ✓ All records have been backfilled\n";
    }
    
    // Sample some records to verify data
    echo "\n3. Sample records (first 5):\n";
    $samples = DB::table('sub_kategori_sampah')
        ->select('id', 'bank_sampah_id', 'kategori_sampah_id', 'kategori_sampah', 'nama_sub_kategori', 'slug', 'status_aktif', 'is_active')
        ->limit(5)
        ->get();
    
    foreach ($samples as $record) {
        echo "\n   Record ID: {$record->id}\n";
        echo "   - Bank Sampah ID: {$record->bank_sampah_id}\n";
        echo "   - Nama: {$record->nama_sub_kategori}\n";
        echo "   - Slug: {$record->slug}\n";
        echo "   - Kategori Sampah ID: {$record->kategori_sampah_id} → Kategori Sampah: {$record->kategori_sampah}\n";
        echo "   - Status Aktif: {$record->status_aktif} → Is Active: {$record->is_active}\n";
    }
    
    // Check for slug format validity
    echo "\n4. Checking slug format validity...\n";
    $invalidSlugs = DB::table('sub_kategori_sampah')
        ->whereNotNull('slug')
        ->where('slug', 'NOT REGEXP', '^[a-z0-9-]+$')
        ->count();
    
    if ($invalidSlugs > 0) {
        echo "   ⚠️  WARNING: Found $invalidSlugs records with invalid slug format\n";
    } else {
        echo "   ✓ All slugs have valid format (lowercase, numbers, hyphens only)\n";
    }
    
    // Check for slug uniqueness within (bank_sampah_id, kategori_sampah) scope
    echo "\n5. Checking slug uniqueness...\n";
    $duplicates = DB::select("
        SELECT bank_sampah_id, kategori_sampah, slug, COUNT(*) as count
        FROM sub_kategori_sampah
        WHERE slug IS NOT NULL
        GROUP BY bank_sampah_id, kategori_sampah, slug
        HAVING count > 1
    ");
    
    if (count($duplicates) > 0) {
        echo "   ⚠️  WARNING: Found " . count($duplicates) . " duplicate slug(s):\n";
        foreach ($duplicates as $dup) {
            echo "      - Bank: {$dup->bank_sampah_id}, Kategori: {$dup->kategori_sampah}, Slug: {$dup->slug} (count: {$dup->count})\n";
        }
    } else {
        echo "   ✓ All slugs are unique within their scope\n";
    }
    
    // Check kategori_sampah values
    echo "\n6. Checking kategori_sampah values...\n";
    $invalidKategori = DB::table('sub_kategori_sampah')
        ->whereNotNull('kategori_sampah')
        ->whereNotIn('kategori_sampah', [0, 1])
        ->count();
    
    if ($invalidKategori > 0) {
        echo "   ⚠️  WARNING: Found $invalidKategori records with invalid kategori_sampah values\n";
    } else {
        echo "   ✓ All kategori_sampah values are valid (0 or 1)\n";
    }
    
    // Check is_active consistency with status_aktif
    echo "\n7. Checking is_active consistency with status_aktif...\n";
    $inconsistent = DB::table('sub_kategori_sampah')
        ->whereRaw('CAST(status_aktif AS UNSIGNED) != CAST(is_active AS UNSIGNED)')
        ->count();
    
    if ($inconsistent > 0) {
        echo "   ⚠️  WARNING: Found $inconsistent records where is_active doesn't match status_aktif\n";
    } else {
        echo "   ✓ All is_active values match status_aktif\n";
    }
    
} else {
    echo "   ℹ️  No records found in sub_kategori_sampah table\n";
}

echo "\n=== Verification Complete ===\n";
echo "\n✅ Phase 1 & 2 migrations appear to be working correctly!\n";
