<?php

/**
 * Script to audit and fix users with NULL organization_id
 * 
 * This script:
 * 1. Finds all users with organization_id = NULL
 * 2. Identifies which are legitimate System Admins
 * 3. Flags users who should have an organization but don't
 * 4. Provides recommendations for fixing
 */

use App\Models\User;
use App\Models\Organization;
use App\Helpers\AdminHelper;
use Illuminate\Support\Facades\DB;

echo "=== USER ORGANIZATION_ID AUDIT ===\n\n";

// Step 1: Find all users with NULL organization_id
$usersWithoutOrg = User::whereNull('organization_id')->get();

echo "Found " . $usersWithoutOrg->count() . " users with NULL organization_id\n\n";

if ($usersWithoutOrg->isEmpty()) {
    echo "✓ All users have organization_id assigned\n";
    exit(0);
}

// Step 2: Categorize users
$systemAdmins = [];
$regularUsers = [];

foreach ($usersWithoutOrg as $user) {
    if (AdminHelper::isSystemAdmin($user)) {
        $systemAdmins[] = $user;
    } else {
        $regularUsers[] = $user;
    }
}

echo "--- SYSTEM ADMINS (Legitimate NULL organization_id) ---\n";
if (empty($systemAdmins)) {
    echo "  None found\n";
} else {
    foreach ($systemAdmins as $admin) {
        echo "  ✓ ID: {$admin->id}, Email: {$admin->email}, Name: {$admin->name}\n";
    }
}
echo "\n";

echo "--- REGULAR USERS (CRITICAL: Should have organization_id) ---\n";
if (empty($regularUsers)) {
    echo "  ✓ None found - All regular users have organization_id\n";
} else {
    echo "  ⚠ SECURITY ISSUE: Found " . count($regularUsers) . " regular users without organization_id\n\n";
    
    foreach ($regularUsers as $user) {
        echo "  ✗ ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
        echo "    Role: {$user->role}\n";
        echo "    Created: {$user->created_at}\n";
        echo "    Last Login: " . ($user->last_login_at ?? 'Never') . "\n";
        
        // Try to find their organization from their activity
        $cteEvent = DB::table('cte_events')
            ->where('created_by', $user->id)
            ->whereNotNull('organization_id')
            ->first();
        
        if ($cteEvent) {
            echo "    → Suggested organization_id: {$cteEvent->organization_id} (from CTE activity)\n";
        }
        
        echo "\n";
    }
    
    // Step 3: Provide fix recommendations
    echo "\n=== FIX RECOMMENDATIONS ===\n\n";
    
    echo "Option 1: Assign to default organization\n";
    $defaultOrg = Organization::first();
    if ($defaultOrg) {
        echo "  Default Organization: ID {$defaultOrg->id}, Name: {$defaultOrg->name}\n";
        echo "  Command: User::whereIn('id', [" . implode(',', array_column($regularUsers, 'id')) . "])->update(['organization_id' => {$defaultOrg->id}]);\n\n";
    }
    
    echo "Option 2: Assign based on activity (recommended)\n";
    echo "  Run the following SQL to assign based on their CTE activity:\n";
    echo "  UPDATE users u\n";
    echo "  JOIN cte_events ce ON u.id = ce.created_by\n";
    echo "  SET u.organization_id = ce.organization_id\n";
    echo "  WHERE u.organization_id IS NULL\n";
    echo "  AND ce.organization_id IS NOT NULL\n";
    echo "  GROUP BY u.id;\n\n";
    
    echo "Option 3: Disable accounts without organization\n";
    echo "  If these are inactive/test accounts, consider disabling them:\n";
    echo "  User::whereIn('id', [" . implode(',', array_column($regularUsers, 'id')) . "])->update(['is_active' => false]);\n\n";
}

echo "\n=== AUDIT COMPLETE ===\n";
