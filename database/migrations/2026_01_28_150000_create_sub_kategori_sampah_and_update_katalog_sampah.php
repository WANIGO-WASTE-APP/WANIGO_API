<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates sub_kategori_sampah table and adds sub_kategori_id column to katalog_sampah.
     * This migration implements the Master Data Sub Kategori Sampah feature.
     * 
     * Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.1, 2.2, 2.3, 2.4, 5.1, 5.2, 5.3, 5.4, 6.1, 6.2, 6.5, 6.6
     */
    public function up(): void
    {
        // Create sub_kategori_sampah table if it doesn't exist
        if (!Schema::hasTable('sub_kategori_sampah')) {
            Schema::create('sub_kategori_sampah', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bank_sampah_id');
                $table->tinyInteger('kategori_sampah');
                $table->string('nama_sub_kategori', 100);
                $table->string('slug', 120);
                $table->integer('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Foreign key constraint with CASCADE on delete
                $table->foreign('bank_sampah_id')
                      ->references('id')
                      ->on('bank_sampah')
                      ->onDelete('cascade');
                
                // Unique constraint on (bank_sampah_id, kategori_sampah, slug)
                $table->unique(['bank_sampah_id', 'kategori_sampah', 'slug'], 'unique_bank_kategori_slug');
                
                // Index for filtering by bank, category, and active status
                $table->index(['bank_sampah_id', 'kategori_sampah', 'is_active'], 'idx_bank_kategori_active');
            });
        }
        
        // Add sub_kategori_id column to katalog_sampah if it doesn't exist
        if (!Schema::hasColumn('katalog_sampah', 'sub_kategori_id')) {
            Schema::table('katalog_sampah', function (Blueprint $table) {
                // Add column after kategori_sampah for logical ordering
                $table->unsignedBigInteger('sub_kategori_id')->nullable()->after('kategori_sampah');
                
                // Foreign key constraint with SET NULL on delete
                $table->foreign('sub_kategori_id')
                      ->references('id')
                      ->on('sub_kategori_sampah')
                      ->onDelete('set null');
                
                // Index for efficient lookups
                $table->index('sub_kategori_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     * 
     * Rollback: Drops the foreign key, column, and table in reverse order.
     * Uses safe checks to prevent errors if constraints or tables don't exist.
     */
    public function down(): void
    {
        // Drop foreign key and column from katalog_sampah
        if (Schema::hasColumn('katalog_sampah', 'sub_kategori_id')) {
            Schema::table('katalog_sampah', function (Blueprint $table) {
                // Drop foreign key constraint
                $table->dropForeign(['sub_kategori_id']);
                
                // Drop the column
                $table->dropColumn('sub_kategori_id');
            });
        }
        
        // Drop sub_kategori_sampah table
        Schema::dropIfExists('sub_kategori_sampah');
    }
};
