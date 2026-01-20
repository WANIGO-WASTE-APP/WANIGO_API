<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bank_sampah', function (Blueprint $table) {
            // Composite index for location-based queries
            $table->index(['latitude', 'longitude'], 'idx_bank_sampah_location');
            
            // Index for status filtering
            $table->index('status_operasional', 'idx_bank_sampah_status');
            
            // Index for administrative region filtering
            $table->index('provinsi_id', 'idx_bank_sampah_provinsi');
            $table->index('kabupaten_kota_id', 'idx_bank_sampah_kabupaten');
            $table->index('kecamatan_id', 'idx_bank_sampah_kecamatan');
            
            // Composite index for common filter combinations
            $table->index(['status_operasional', 'provinsi_id'], 'idx_bank_sampah_status_provinsi');
            
            // Full-text index for name search
            $table->fullText('nama_bank_sampah', 'idx_bank_sampah_name_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_sampah', function (Blueprint $table) {
            // Drop indexes in reverse order
            $table->dropFullText('idx_bank_sampah_name_fulltext');
            $table->dropIndex('idx_bank_sampah_status_provinsi');
            $table->dropIndex('idx_bank_sampah_kecamatan');
            $table->dropIndex('idx_bank_sampah_kabupaten');
            $table->dropIndex('idx_bank_sampah_provinsi');
            $table->dropIndex('idx_bank_sampah_status');
            $table->dropIndex('idx_bank_sampah_location');
        });
    }
};
