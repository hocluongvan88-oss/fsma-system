<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure default organization exists for products without organization_id
     * This prevents orphaned records and ensures data integrity
     */
    public function up(): void
    {
        // Check if organization with ID 1 exists, if not create it
        $organizationExists = DB::table('organizations')->where('id', 1)->exists();
        
        if (!$organizationExists) {
            DB::table('organizations')->insert([
                'id' => 1,
                'name' => 'Default Organization',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Don't delete the default organization on rollback
        // to prevent data loss
    }
};
