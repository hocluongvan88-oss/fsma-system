<?php

/**
 * Script to automatically fix users' organization_id based on their activity
 * 
 * This script assigns organization_id to users based on:
 * 1. Their CTE Events creation activity
 * 2. Their Trace Records creation activity
 * 3. Their Audit Log activity
 */

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== FIXING USERS ORGANIZATION_ID FROM ACTIVITY ===\n\n";

// Find users without organization_id (excluding System Admins)
$usersToFix = User::whereNull('organization_id')
    ->where('email', '!=', 'admin@fsma204.com') // Exclude System Admin
    ->get();

if ($usersToFix->isEmpty()) {
    echo "✓ No users need fixing\n";
    exit(0);
}

echo "Found " . $usersToFix->count() . " users to fix\n\n";

$fixed = 0;
$notFixed = [];

foreach ($usersToFix as $user) {
    echo "Processing User ID: {$user->id}, Email: {$user->email}\n";
    
    // Try to find organization from CTE Events
    $orgFromCTE = DB::table('cte_events')
        ->where('created_by', $user->id)
        ->whereNotNull('organization_id')
        ->select('organization_id')
        ->groupBy('organization_id')
        ->orderByRaw('COUNT(*) DESC')
        ->first();
    
    if ($orgFromCTE) {
        $user->organization_id = $orgFromCTE->organization_id;
        $user->save();
        echo "  ✓ Assigned organization_id: {$orgFromCTE->organization_id} (from CTE Events)\n";
        $fixed++;
        continue;
    }
    
    // Try to find organization from Trace Records
    $orgFromTrace = DB::table('trace_records')
        ->where('created_by', $user->id)
        ->whereNotNull('organization_id')
        ->select('organization_id')
        ->groupBy('organization_id')
        ->orderByRaw('COUNT(*) DESC')
        ->first();
    
    if ($orgFromTrace) {
        $user->organization_id = $orgFromTrace->organization_id;
        $user->save();
        echo "  ✓ Assigned organization_id: {$orgFromTrace->organization_id} (from Trace Records)\n";
        $fixed++;
        continue;
    }
    
    // Try to find organization from Audit Logs
    $orgFromAudit = DB::table('audit_logs')
        ->where('user_id', $user->id)
        ->whereNotNull('organization_id')
        ->select('organization_id')
        ->groupBy('organization_id')
        ->orderByRaw('COUNT(*) DESC')
        ->first();
    
    if ($orgFromAudit) {
        $user->organization_id = $orgFromAudit->organization_id;
        $user->save();
        echo "  ✓ Assigned organization_id: {$orgFromAudit->organization_id} (from Audit Logs)\n";
        $fixed++;
        continue;
    }
    
    // Could not find organization from activity
    echo "  ✗ Could not determine organization_id from activity\n";
    $notFixed[] = $user;
}

echo "\n=== SUMMARY ===\n";
echo "Fixed: {$fixed} users\n";
echo "Not Fixed: " . count($notFixed) . " users\n\n";

if (!empty($notFixed)) {
    echo "Users that could not be fixed:\n";
    foreach ($notFixed as $user) {
        echo "  - ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
    }
    echo "\nThese users may need manual assignment or should be disabled.\n";
}

echo "\n=== FIX COMPLETE ===\n";
