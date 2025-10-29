<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        echo "Starting to populate organization_id for CTEEvent records...\n";
        
        // Step 1: Update CTEEvents that have a valid trace_record_id
        $updated = DB::table('cte_events as ce')
            ->join('trace_records as tr', 'ce.trace_record_id', '=', 'tr.id')
            ->whereNull('ce.organization_id')
            ->whereNotNull('tr.organization_id')
            ->update([
                'ce.organization_id' => DB::raw('tr.organization_id'),
                'ce.updated_at' => now()
            ]);
        
        echo "Updated {$updated} CTEEvent records with organization_id from TraceRecord\n";
        
        // Step 2: Check for orphaned CTEEvents (no trace_record_id or invalid trace_record_id)
        $orphaned = DB::table('cte_events')
            ->whereNull('organization_id')
            ->count();
        
        if ($orphaned > 0) {
            echo "WARNING: Found {$orphaned} CTEEvent records without organization_id\n";
            echo "These records may need manual review\n";
            
            // Log orphaned records for review
            $orphanedRecords = DB::table('cte_events')
                ->whereNull('organization_id')
                ->select('id', 'trace_record_id', 'event_type', 'event_date')
                ->get();
            
            foreach ($orphanedRecords as $record) {
                echo "  - CTEEvent ID: {$record->id}, TraceRecord ID: {$record->trace_record_id}, Type: {$record->event_type}\n";
            }
        }
        
        // Step 3: Verify all CTEEvents now have organization_id
        $remaining = DB::table('cte_events')
            ->whereNull('organization_id')
            ->count();
        
        if ($remaining === 0) {
            echo "SUCCESS: All CTEEvent records now have organization_id\n";
        } else {
            echo "WARNING: {$remaining} CTEEvent records still missing organization_id\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't reverse this migration as it's a data fix
        echo "This migration cannot be reversed as it's a data integrity fix\n";
    }
};
