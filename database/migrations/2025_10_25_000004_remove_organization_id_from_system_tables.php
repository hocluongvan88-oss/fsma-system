<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove organization_id from system-wide configuration tables
        // These tables are NOT organization-specific and should not have organization_id
        
        $systemTables = [
            'signature_record_types',
            'pricing',
            'signature_delegations',
            'signature_revocations',
            'signature_verifications',
        ];

        foreach ($systemTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'organization_id')) {
                Schema::table($table, function (Blueprint $table) {
                    // Drop foreign key if it exists
                    try {
                        $table->dropForeign(['organization_id']);
                    } catch (\Exception $e) {
                        // Foreign key might not exist
                    }
                    
                    // Drop index if it exists
                    try {
                        $table->dropIndex(['organization_id']);
                    } catch (\Exception $e) {
                        // Index might not exist
                    }
                    
                    // Drop column
                    $table->dropColumn('organization_id');
                });
            }
        }
    }

    public function down(): void
    {
        // Rollback is not recommended for this migration
        // as it would re-add columns to system-wide tables
    }
};
