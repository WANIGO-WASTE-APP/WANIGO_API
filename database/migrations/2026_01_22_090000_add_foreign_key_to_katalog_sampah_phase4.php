<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Phase 4: Add Foreign Key to Katalog Sampah
     * - Validate all existing katalog_sampah references are valid
     * - Add foreign key constraint with ON DELETE RESTRICT
     * - Throw exception if orphaned records found
     * 
     * Requirements: 5.1, 5.4, 10.6
     */
    public function up(): void
    {
        // Step 1: Validate all existing references
        // Find katalog_sampah records with non-null sub_kategori_sampah_id
        // that don't have a matching sub_kategori_sampah record
        $orphanedRecords = DB::table('katalog_sampah as k')
            ->leftJoin('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
            ->whereNotNull('k.sub_kategori_sampah_id')
            ->whereNull('sk.id')
            ->select('k.id', 'k.nama_item_sampah', 'k.sub_kategori_sampah_id')
            ->get();
        
        // Step 2: If orphaned records exist, throw exception with details
        if ($orphanedRecords->isNotEmpty()) {
            $orphanedCount = $orphanedRecords->count();
            $orphanedIds = $orphanedRecords->pluck('id')->toArray();
            $orphanedList = $orphanedRecords->map(function ($record) {
                return "ID: {$record->id}, Nama: {$record->nama_item_sampah}, sub_kategori_sampah_id: {$record->sub_kategori_sampah_id}";
            })->take(10)->implode("\n  - ");
            
            $message = "Cannot add foreign key constraint: Found {$orphanedCount} orphaned katalog_sampah records.\n\n";
            $message .= "These records have sub_kategori_sampah_id values that don't exist in sub_kategori_sampah table:\n";
            $message .= "  - {$orphanedList}\n";
            
            if ($orphanedCount > 10) {
                $message .= "  ... and " . ($orphanedCount - 10) . " more records.\n";
            }
            
            $message .= "\nOrphaned record IDs: " . implode(', ', $orphanedIds) . "\n\n";
            $message .= "Please fix these records before running this migration:\n";
            $message .= "  1. Set sub_kategori_sampah_id to NULL for invalid references, OR\n";
            $message .= "  2. Update sub_kategori_sampah_id to valid sub_kategori_sampah.id values, OR\n";
            $message .= "  3. Delete the orphaned katalog_sampah records\n\n";
            $message .= "Example SQL to set to NULL:\n";
            $message .= "  UPDATE katalog_sampah SET sub_kategori_sampah_id = NULL WHERE id IN (" . implode(', ', $orphanedIds) . ");\n";
            
            throw new \Exception($message);
        }
        
        // Step 3: Add foreign key constraint with ON DELETE RESTRICT
        // This prevents deletion of sub_kategori_sampah if katalog items reference it
        Schema::table('katalog_sampah', function (Blueprint $table) {
            $table->foreign('sub_kategori_sampah_id')
                ->references('id')
                ->on('sub_kategori_sampah')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Rollback: Drop foreign key constraint
     */
    public function down(): void
    {
        Schema::table('katalog_sampah', function (Blueprint $table) {
            // Drop the foreign key constraint
            // Laravel will automatically determine the constraint name
            $table->dropForeign(['sub_kategori_sampah_id']);
        });
    }
};
