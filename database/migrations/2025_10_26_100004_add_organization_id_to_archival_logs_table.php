<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add organization_id column to archival_logs table
        if (Schema::hasTable('archival_logs') && !Schema::hasColumn('archival_logs', 'organization_id')) {
            Schema::table('archival_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->index('organization_id');
            });

            // Set default organization_id for existing records
            DB::table('archival_logs')
                ->whereNull('organization_id')
                ->update(['organization_id' => 1]);

            // Make organization_id NOT NULL after setting defaults
            Schema::table('archival_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable(false)->change();
                
                // Add foreign key constraint
                $table->foreign('organization_id')
                    ->references('id')
                    ->on('organizations')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('archival_logs') && Schema::hasColumn('archival_logs', 'organization_id')) {
            Schema::table('archival_logs', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }
    }
};
