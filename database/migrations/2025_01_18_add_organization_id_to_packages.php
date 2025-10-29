<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('packages') && !Schema::hasColumn('packages', 'organization_id')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->index('organization_id');
            });
        }

        if (Schema::hasTable('audit_logs_details') && !Schema::hasColumn('audit_logs_details', 'organization_id')) {
            Schema::table('audit_logs_details', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('audit_log_id');
                $table->index('organization_id');
            });
        }

        if (Schema::hasTable('digital_certificates') && !Schema::hasColumn('digital_certificates', 'organization_id')) {
            Schema::table('digital_certificates', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('user_id');
                $table->index('organization_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('packages') && Schema::hasColumn('packages', 'organization_id')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropIndex(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }

        if (Schema::hasTable('audit_logs_details') && Schema::hasColumn('audit_logs_details', 'organization_id')) {
            Schema::table('audit_logs_details', function (Blueprint $table) {
                $table->dropIndex(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }

        if (Schema::hasTable('digital_certificates') && Schema::hasColumn('digital_certificates', 'organization_id')) {
            Schema::table('digital_certificates', function (Blueprint $table) {
                $table->dropIndex(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }
    }
};
