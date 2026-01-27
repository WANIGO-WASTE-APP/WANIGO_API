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
     * Update status_setoran values from old format to new format:
     * - pending/requested → pengajuan
     * - processing/in_progress → diproses
     * - done/completed → selesai
     * 
     * Requirements: 4.4, 7.4, 7.5
     */
    public function up(): void
    {
        // Map old status values to new values
        DB::table('setoran_sampah')
            ->whereIn('status_setoran', ['pending', 'requested'])
            ->update(['status_setoran' => 'pengajuan']);
        
        DB::table('setoran_sampah')
            ->whereIn('status_setoran', ['processing', 'in_progress'])
            ->update(['status_setoran' => 'diproses']);
        
        DB::table('setoran_sampah')
            ->whereIn('status_setoran', ['done', 'completed'])
            ->update(['status_setoran' => 'selesai']);
    }

    /**
     * Reverse the migrations.
     * 
     * Rollback status values to original format for backward compatibility.
     */
    public function down(): void
    {
        // Reverse mapping for rollback
        DB::table('setoran_sampah')
            ->where('status_setoran', 'pengajuan')
            ->update(['status_setoran' => 'pending']);
        
        DB::table('setoran_sampah')
            ->where('status_setoran', 'diproses')
            ->update(['status_setoran' => 'processing']);
        
        DB::table('setoran_sampah')
            ->where('status_setoran', 'selesai')
            ->update(['status_setoran' => 'completed']);
    }
};
