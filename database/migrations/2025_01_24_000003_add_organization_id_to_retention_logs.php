<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retention_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('retention_logs', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->index('organization_id');
                $table->foreign('organization_id')
                    ->references('id')
                    ->on('organizations')
                    ->onDelete('cascade');
            }
        });

        // Populate organization_id from retention_policy relationship
        DB::statement('
            UPDATE retention_logs rl
            SET organization_id = (
                SELECT rp.organization_id FROM retention_policies rp WHERE rp.id = rl.retention_policy_id
            )
            WHERE rl.organization_id IS NULL AND rl.retention_policy_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('retention_logs', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
