<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Fixing Data Integrity Issues ===\n\n";

// Fix Issue 1: Set orphaned sub_kategori_sampah_id to NULL
echo "Issue 1: Fixing orphaned katalog records...\n";

$orphanedIds = DB::table('katalog_sampah as k')
    ->leftJoin('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
    ->whereNotNull('k.sub_kategori_sampah_id')
    ->whereNull('sk.id')
    ->pluck('k.id');

echo "Found {$orphanedIds->count()} orphaned records\n";

if ($orphanedIds->count() > 0) {
    $updated1 = DB::table('katalog_sampah')
        ->whereIn('id', $orphanedIds)
        ->update(['sub_kategori_sampah_id' => null]);
    
    echo "✓ Fixed {$updated1} orphaned records\n\n";
} else {
    echo "✓ No orphaned records found\n\n";
}

// Fix Issue 2: Set category mismatch records to NULL
echo "Issue 2: Fixing category mismatch records...\n";

$mismatchIds = DB::table('katalog_sampah as k')
    ->join('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
    ->whereColumn('k.kategori_sampah', '!=', 'sk.kategori_sampah')
    ->pluck('k.id');

echo "Found {$mismatchIds->count()} category mismatch records\n";

if ($mismatchIds->count() > 0) {
    $updated2 = DB::table('katalog_sampah')
        ->whereIn('id', $mismatchIds)
        ->update(['sub_kategori_sampah_id' => null]);
    
    echo "✓ Fixed {$updated2} category mismatch records\n\n";
} else {
    echo "✓ No category mismatch records found\n\n";
}

// Verify fixes
echo "=== Verification ===\n";

$orphanedCount = DB::table('katalog_sampah as k')
    ->leftJoin('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
    ->whereNotNull('k.sub_kategori_sampah_id')
    ->whereNull('sk.id')
    ->count();

$mismatchCount = DB::table('katalog_sampah as k')
    ->join('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
    ->whereColumn('k.kategori_sampah', '!=', 'sk.kategori_sampah')
    ->count();

echo "Remaining orphaned records: {$orphanedCount}\n";
echo "Remaining category mismatches: {$mismatchCount}\n\n";

if ($orphanedCount === 0 && $mismatchCount === 0) {
    echo "✓ All data integrity issues fixed successfully!\n";
} else {
    echo "⚠ Some issues remain. Please investigate.\n";
}
