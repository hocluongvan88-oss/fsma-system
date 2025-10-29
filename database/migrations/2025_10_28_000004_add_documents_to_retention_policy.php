<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix: Packages should be global (organization_id = NULL) so they appear for all organizations
     * The OrganizationScope trait filters by organization_id, but packages should be shared across all orgs
     */
    public function up(): void
    {
        // Ensure all packages have organization_id = NULL (global packages)
        DB::table('packages')->update([
            'organization_id' => null,
        ]);
        
        DB::statement('ALTER TABLE packages MODIFY organization_id BIGINT UNSIGNED NULL DEFAULT NULL');
    }

    public function down(): void
    {
        // No rollback needed - packages should remain global
    }
};
