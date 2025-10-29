<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fixed: Use 'id' instead of 'slug' since packages table uses string id as primary key
     */
    public function up(): void
    {
        // Get the Free package ID
        $freePackage = DB::table('packages')
            ->where('id', 'free')  // Changed from 'slug' to 'id'
            ->first();
        
        if (!$freePackage) {
            // If Free package doesn't exist, create it
            DB::table('packages')->insert([
                'id' => 'free',  // Use 'id' as primary key (string)
                'name' => 'Free',
                'description' => 'Free tier package',
                'max_cte_records_monthly' => 5,
                'max_documents' => 10,
                'max_users' => 1,
                'is_visible' => true,
                'is_selectable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $freePackageId = 'free';
        } else {
            $freePackageId = $freePackage->id;
        }
        
        // Assign Free package to all organizations that don't have a package_id
        DB::table('organizations')
            ->whereNull('package_id')
            ->update([
                'package_id' => $freePackageId,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set package_id back to NULL for organizations
        DB::table('organizations')
            ->update([
                'package_id' => null,
                'updated_at' => now(),
            ]);
    }
};
