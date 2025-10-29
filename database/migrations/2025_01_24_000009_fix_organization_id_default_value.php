<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix: "Field 'organization_id' doesn't have a default value"
     * 
     * This migration ensures that:
     * 1. All tables with NOT NULL organization_id have proper handling
     * 2. Existing NULL values are populated from related tables
     * 3. Default organization is assigned to orphaned records
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // ===== FIX FOR CTE_EVENTS TABLE =====
        // Step 1: Populate organization_id from trace_records for NULL values
        DB::statement('
            UPDATE cte_events ce
            INNER JOIN trace_records tr ON ce.trace_record_id = tr.id
            SET ce.organization_id = tr.organization_id
            WHERE ce.organization_id IS NULL AND tr.organization_id IS NOT NULL
        ');

        // Step 2: For remaining NULL values, assign to first available organization
        $firstOrgId = DB::table('organizations')->orderBy('id')->first()?->id;
        if ($firstOrgId) {
            DB::statement('
                UPDATE cte_events
                SET organization_id = ?
                WHERE organization_id IS NULL
            ', [$firstOrgId]);
        }

        // Step 3: Remove invalid foreign key references
        DB::statement('
            DELETE FROM cte_events
            WHERE organization_id IS NOT NULL 
            AND organization_id NOT IN (SELECT id FROM organizations)
        ');

        // ===== FIX FOR AUDIT_LOGS TABLE =====
        // Step 1: Populate organization_id from users for NULL values
        DB::statement('
            UPDATE audit_logs al
            INNER JOIN users u ON al.user_id = u.id
            SET al.organization_id = u.organization_id
            WHERE al.organization_id IS NULL AND u.organization_id IS NOT NULL
        ');

        // Step 2: For remaining NULL values, assign to first available organization
        if ($firstOrgId) {
            DB::statement('
                UPDATE audit_logs
                SET organization_id = ?
                WHERE organization_id IS NULL
            ', [$firstOrgId]);
        }

        // Step 3: Remove invalid foreign key references
        DB::statement('
            DELETE FROM audit_logs
            WHERE organization_id IS NOT NULL 
            AND organization_id NOT IN (SELECT id FROM organizations)
        ');

        // ===== FIX FOR RETENTION_POLICIES TABLE =====
        // Step 1: Populate organization_id from users for NULL values
        DB::statement('
            UPDATE retention_policies rp
            INNER JOIN users u ON rp.created_by = u.id
            SET rp.organization_id = u.organization_id
            WHERE rp.organization_id IS NULL AND u.organization_id IS NOT NULL
        ');

        // Step 2: For remaining NULL values, assign to first available organization
        if ($firstOrgId) {
            DB::statement('
                UPDATE retention_policies
                SET organization_id = ?
                WHERE organization_id IS NULL
            ', [$firstOrgId]);
        }

        // Step 3: Remove invalid foreign key references
        DB::statement('
            DELETE FROM retention_policies
            WHERE organization_id IS NOT NULL 
            AND organization_id NOT IN (SELECT id FROM organizations)
        ');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // This migration only populates data, no schema changes to rollback
    }
};
