<?php

use Illuminate\Support\Facades\DB;

// Check CTEEvent records without organization_id
$missingOrgId = DB::table('cte_events')
    ->whereNull('organization_id')
    ->count();

echo "[v0] CTEEvent records without organization_id: {$missingOrgId}\n";

if ($missingOrgId > 0) {
    echo "[v0] Populating organization_id from trace_records...\n";
    
    // Update CTEEvent organization_id from related TraceRecord
    $updated = DB::table('cte_events as ce')
        ->join('trace_records as tr', 'ce.trace_record_id', '=', 'tr.id')
        ->whereNull('ce.organization_id')
        ->update([
            'ce.organization_id' => DB::raw('tr.organization_id')
        ]);
    
    echo "[v0] Updated {$updated} CTEEvent records with organization_id\n";
}

// Verify all CTEEvents now have organization_id
$stillMissing = DB::table('cte_events')
    ->whereNull('organization_id')
    ->count();

if ($stillMissing > 0) {
    echo "[v0] WARNING: {$stillMissing} CTEEvent records still missing organization_id\n";
    echo "[v0] These records may not have a valid trace_record_id\n";
    
    // Show orphaned records
    $orphaned = DB::table('cte_events')
        ->whereNull('organization_id')
        ->select('id', 'event_type', 'trace_record_id', 'created_at')
        ->limit(10)
        ->get();
    
    echo "[v0] Sample orphaned records:\n";
    foreach ($orphaned as $record) {
        echo "[v0]   ID: {$record->id}, Type: {$record->event_type}, TraceRecord: {$record->trace_record_id}\n";
    }
} else {
    echo "[v0] SUCCESS: All CTEEvent records have organization_id\n";
}

// Show organization distribution
$distribution = DB::table('cte_events')
    ->select('organization_id', DB::raw('count(*) as count'))
    ->groupBy('organization_id')
    ->get();

echo "[v0] CTEEvent distribution by organization:\n";
foreach ($distribution as $row) {
    $orgId = $row->organization_id ?? 'NULL';
    echo "[v0]   Organization {$orgId}: {$row->count} events\n";
}
