<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Phase 1: Add new columns (non-breaking)
     * - Add slug column (varchar 100, nullable)
     * - Add is_active column (boolean, default true)
     * - Add kategori_sampah column (tinyint, nullable)
     * 
     * Requirements: 1.1, 2.1, 3.1
     */
    public function up(): void
    {
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            // Add slug column after kode_sub_kategori
            $table->string('slug', 100)->nullable()->after('kode_sub_kategori');
            
            // Add is_active column after status_aktif
            $table->boolean('is_active')->default(true)->after('status_aktif');
            
            // Add kategori_sampah column after kategori_sampah_id
            $table->tinyInteger('kategori_sampah')->nullable()->after('kategori_sampah_id');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Rollback: Drop the three new columns
     */
    public function down(): void
    {
        Schema::table('sub_kategori_sampah', function (Blueprint $table) {
            $table->dropColumn(['slug', 'is_active', 'kategori_sampah']);
        });
    }
};
