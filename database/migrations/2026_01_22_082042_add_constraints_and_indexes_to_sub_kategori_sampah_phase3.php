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
     * Phase 3: Add Constraints and Indexes
     * - Make slug and kategori_sampah columns NOT NULL
     * - Drop old unique constraint on (bank_sampah_id, kode_sub_kategori)
     * - Add new unique constraint on (bank_sampah_id, kategori_sampah, slug)
     * - Add composite index on (bank_sampah_id, kategori_sampah, is_active)
     * 
     * Requirements: 2.4, 4.1, 4.3
     */
    public function up(): void
    {
        // Step 1: Make slug and kategori_sampah columns NOT NULL
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            $table->string('slug', 100)->nullable(false)->change();
            $table->tinyInteger('kategori_sampah')->nullable(false)->change();
        });
        
        // Step 2: Add new unique constraint on (bank_sampah_id, kategori_sampah, slug)
        // This must be done BEFORE dropping the old constraint to ensure we always have
        // a unique constraint that can support the foreign key on bank_sampah_id
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            $table->unique(
                ['bank_sampah_id', 'kategori_sampah', 'slug'],
                'unique_bank_kategori_slug'
            );
        });
        
        // Step 3: Drop old unique constraint on (bank_sampah_id, kode_sub_kategori)
        // Now safe to drop since we have the new unique constraint
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            $table->dropUnique(['bank_sampah_id', 'kode_sub_kategori']);
        });
        
        // Step 4: Add composite index on (bank_sampah_id, kategori_sampah, is_active)
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            $table->index(
                ['bank_sampah_id', 'kategori_sampah', 'is_active'],
                'idx_bank_kategori_active'
            );
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Rollback: Drop constraints and indexes, make columns nullable again
     */
    public function down(): void
    {
        // Step 1: Drop composite index
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            $table->dropIndex('idx_bank_kategori_active');
        });
        
        // Step 2: Re-add old unique constraint BEFORE dropping new one
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            $table->unique(['bank_sampah_id', 'kode_sub_kategori']);
        });
        
        // Step 3: Drop new unique constraint
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            $table->dropUnique('unique_bank_kategori_slug');
        });
        
        // Step 4: Make columns nullable again
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            $table->string('slug', 100)->nullable()->change();
            $table->tinyInteger('kategori_sampah')->nullable()->change();
        });
    }
};
