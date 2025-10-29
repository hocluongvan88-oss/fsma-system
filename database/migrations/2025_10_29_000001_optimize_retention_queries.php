<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FSMA 204 Compliance: Database optimization for retention queries
     */
    public function up(): void
    {
        Schema::table('retention_policies', function (Blueprint $table) {
            $table->index(['organization_id', 'is_active', 'data_type'], 'idx_retention_policies_org_active_type');
            $table->index(['organization_id', 'updated_at'], 'idx_retention_policies_org_updated');
        });

        Schema::table('retention_logs', function (Blueprint $table) {
            $table->index(['organization_id', 'status', 'executed_at'], 'idx_retention_logs_org_status_executed');
            $table->index(['organization_id', 'created_at'], 'idx_retention_logs_org_created');
            // Only add backup_verified index if column exists
            if (Schema::hasColumn('retention_logs', 'backup_verified')) {
                $table->index(['backup_verified', 'organization_id'], 'idx_retention_logs_backup_verified');
            }
        });

        Schema::table('trace_records', function (Blueprint $table) {
            $table->index(['organization_id', 'created_at'], 'idx_trace_records_org_created');
        });

        Schema::table('cte_events', function (Blueprint $table) {
            $table->index(['organization_id', 'created_at'], 'idx_cte_events_org_created');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['organization_id', 'created_at'], 'idx_audit_logs_org_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retention_policies', function (Blueprint $table) {
            $table->dropIndex('idx_retention_policies_org_active_type');
            $table->dropIndex('idx_retention_policies_org_updated');
        });

        Schema::table('retention_logs', function (Blueprint $table) {
            $table->dropIndex('idx_retention_logs_org_status_executed');
            $table->dropIndex('idx_retention_logs_org_created');
            if (Schema::hasColumn('retention_logs', 'backup_verified')) {
                $table->dropIndex('idx_retention_logs_backup_verified');
            }
        });

        Schema::table('trace_records', function (Blueprint $table) {
            $table->dropIndex('idx_trace_records_org_created');
        });

        Schema::table('cte_events', function (Blueprint $table) {
            $table->dropIndex('idx_cte_events_org_created');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_logs_org_created');
        });
    }
};
