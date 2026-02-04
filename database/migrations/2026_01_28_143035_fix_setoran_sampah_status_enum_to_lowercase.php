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
     * Fix status_setoran enum values from capitalized to lowercase
     * to match the model constants.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Change column to string temporarily to update values
            DB::statement("ALTER TABLE setoran_sampah MODIFY COLUMN status_setoran VARCHAR(20)");
        }
        
        // Update all existing values to lowercase
        DB::table('setoran_sampah')
            ->where('status_setoran', 'Pengajuan')
            ->update(['status_setoran' => 'pengajuan']);
        
        DB::table('setoran_sampah')
            ->where('status_setoran', 'Diproses')
            ->update(['status_setoran' => 'diproses']);
        
        DB::table('setoran_sampah')
            ->where('status_setoran', 'Selesai')
            ->update(['status_setoran' => 'selesai']);
        
        DB::table('setoran_sampah')
            ->where('status_setoran', 'Dibatalkan')
            ->update(['status_setoran' => 'dibatalkan']);
        
        if ($driver === 'mysql') {
            // Change back to enum with lowercase values
            DB::statement("ALTER TABLE setoran_sampah MODIFY COLUMN status_setoran ENUM('pengajuan', 'diproses', 'selesai', 'dibatalkan') DEFAULT 'pengajuan'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // Change column to string temporarily
            DB::statement("ALTER TABLE setoran_sampah MODIFY COLUMN status_setoran VARCHAR(20)");
        }
        
        // Revert to capitalized values
        DB::table('setoran_sampah')
            ->where('status_setoran', 'pengajuan')
            ->update(['status_setoran' => 'Pengajuan']);
        
        DB::table('setoran_sampah')
            ->where('status_setoran', 'diproses')
            ->update(['status_setoran' => 'Diproses']);
        
        DB::table('setoran_sampah')
            ->where('status_setoran', 'selesai')
            ->update(['status_setoran' => 'Selesai']);
        
        DB::table('setoran_sampah')
            ->where('status_setoran', 'dibatalkan')
            ->update(['status_setoran' => 'Dibatalkan']);
        
        if ($driver === 'mysql') {
            // Change back to enum with capitalized values
            DB::statement("ALTER TABLE setoran_sampah MODIFY COLUMN status_setoran ENUM('Pengajuan', 'Diproses', 'Selesai', 'Dibatalkan') DEFAULT 'Pengajuan'");
        }
    }
};
