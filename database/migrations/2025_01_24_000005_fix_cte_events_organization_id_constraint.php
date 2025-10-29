<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix organization_id foreign key constraint for cte_events table
     * This migration handles data integrity issues by:
     * 1. Populating organization_id from related trace_records
     * 2. Handling orphaned records
     * 3. Adding proper foreign key constraint
     */
    public function up(): void
    {
        // Step 1: Populate organization_id from trace_records for records with NULL organization_id
        DB::statement('
            UPDATE cte_events ce
            INNER JOIN trace_records tr ON ce.trace_record_id = tr.id
            SET ce.organization_id = tr.organization_id
            WHERE ce.organization_id IS NULL AND tr.organization_id IS NOT NULL
        ');

        // Step 2: For any remaining NULL organization_id (orphaned records), 
        // assign to a default organization or handle them
        // Get the first organization ID as fallback
        $defaultOrgId = DB::table('organizations')->first()?->id;
        
        if ($defaultOrgId) {
            DB::statement('
                UPDATE cte_events
                SET organization_id = ?
                WHERE organization_id IS NULL
            ', [$defaultOrgId]);
        }

        // Step 3: Verify all organization_id values exist in organizations table
        // Remove any invalid foreign key references
        DB::statement('
            DELETE FROM cte_events
            WHERE organization_id IS NOT NULL 
            AND organization_id NOT IN (SELECT id FROM organizations)
        ');

        // Step 4: Make organization_id NOT NULL
        Schema::table('cte_events', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });

        // Step 5: Add foreign key constraint
        Schema::table('cte_events', function (Blueprint $table) {
            if (!$this->foreignKeyExists('cte_events', 'cte_events_organization_id_foreign')) {
                $table->foreign('organization_id')
                    ->references('id')
                    ->on('organizations')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            // Drop foreign key if it exists
            try {
                $table->dropForeign(['organization_id']);
            } catch (\Exception $e) {
                // Foreign key might not exist
            }
            
            // Make organization_id nullable again
            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });
    }

    /**
     * Check if foreign key exists
     */
    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $constraints = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = ? AND CONSTRAINT_NAME = ?
        ", [$table, $foreignKey]);

        return count($constraints) > 0;
    }
};
