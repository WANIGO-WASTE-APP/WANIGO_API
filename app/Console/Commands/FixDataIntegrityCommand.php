<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDataIntegrityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data-integrity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix data integrity issues in katalog_sampah table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Fixing Data Integrity Issues ===');
        $this->newLine();

        // Fix Issue 1: Set orphaned sub_kategori_sampah_id to NULL
        $this->info('Issue 1: Fixing orphaned katalog records...');

        $orphanedIds = DB::table('katalog_sampah as k')
            ->leftJoin('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
            ->whereNotNull('k.sub_kategori_sampah_id')
            ->whereNull('sk.id')
            ->pluck('k.id');

        $this->line("Found {$orphanedIds->count()} orphaned records");

        if ($orphanedIds->count() > 0) {
            $updated1 = DB::table('katalog_sampah')
                ->whereIn('id', $orphanedIds)
                ->update(['sub_kategori_sampah_id' => null]);
            
            $this->info("✓ Fixed {$updated1} orphaned records");
        } else {
            $this->info("✓ No orphaned records found");
        }
        
        $this->newLine();

        // Fix Issue 2: Set category mismatch records to NULL
        $this->info('Issue 2: Fixing category mismatch records...');

        $mismatchIds = DB::table('katalog_sampah as k')
            ->join('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
            ->whereColumn('k.kategori_sampah', '!=', 'sk.kategori_sampah')
            ->pluck('k.id');

        $this->line("Found {$mismatchIds->count()} category mismatch records");

        if ($mismatchIds->count() > 0) {
            $updated2 = DB::table('katalog_sampah')
                ->whereIn('id', $mismatchIds)
                ->update(['sub_kategori_sampah_id' => null]);
            
            $this->info("✓ Fixed {$updated2} category mismatch records");
        } else {
            $this->info("✓ No category mismatch records found");
        }
        
        $this->newLine();

        // Verify fixes
        $this->info('=== Verification ===');

        $orphanedCount = DB::table('katalog_sampah as k')
            ->leftJoin('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
            ->whereNotNull('k.sub_kategori_sampah_id')
            ->whereNull('sk.id')
            ->count();

        $mismatchCount = DB::table('katalog_sampah as k')
            ->join('sub_kategori_sampah as sk', 'k.sub_kategori_sampah_id', '=', 'sk.id')
            ->whereColumn('k.kategori_sampah', '!=', 'sk.kategori_sampah')
            ->count();

        $this->line("Remaining orphaned records: {$orphanedCount}");
        $this->line("Remaining category mismatches: {$mismatchCount}");
        $this->newLine();

        if ($orphanedCount === 0 && $mismatchCount === 0) {
            $this->info('✓ All data integrity issues fixed successfully!');
            return 0;
        } else {
            $this->error('⚠ Some issues remain. Please investigate.');
            return 1;
        }
    }
}
