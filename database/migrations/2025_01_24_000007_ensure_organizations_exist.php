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
        // Check if organizations table exists and has data
        if (Schema::hasTable('organizations')) {
            // Get all users with organization_id that don't exist in organizations table
            $orphanedUsers = DB::table('users')
                ->whereNotNull('organization_id')
                ->whereNotIn('organization_id', DB::table('organizations')->select('id'))
                ->get();

            if ($orphanedUsers->count() > 0) {
                // Create missing organizations based on user organization_ids
                $missingOrgIds = $orphanedUsers->pluck('organization_id')->unique();
                
                foreach ($missingOrgIds as $orgId) {
                    if (!DB::table('organizations')->where('id', $orgId)->exists()) {
                        DB::table('organizations')->insert([
                            'id' => $orgId,
                            'name' => 'Organization ' . $orgId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only ensures data consistency, no need to reverse
    }
};
