<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add organization_id to signature_record_types
        if (Schema::hasTable('signature_record_types') && !Schema::hasColumn('signature_record_types', 'organization_id')) {
            Schema::table('signature_record_types', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->index('organization_id');
            });
        }

        // Add organization_id to signature_delegations
        if (Schema::hasTable('signature_delegations') && !Schema::hasColumn('signature_delegations', 'organization_id')) {
            Schema::table('signature_delegations', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->index('organization_id');
            });
        }

        // Add organization_id to signature_revocations
        if (Schema::hasTable('signature_revocations') && !Schema::hasColumn('signature_revocations', 'organization_id')) {
            Schema::table('signature_revocations', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->index('organization_id');
            });
        }

        // Add organization_id to signature_verifications
        if (Schema::hasTable('signature_verifications') && !Schema::hasColumn('signature_verifications', 'organization_id')) {
            Schema::table('signature_verifications', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->index('organization_id');
            });
        }

        // Add organization_id to pricing
        if (Schema::hasTable('pricing') && !Schema::hasColumn('pricing', 'organization_id')) {
            Schema::table('pricing', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->index('organization_id');
            });
        }

        // Add organization_id to leads
        if (Schema::hasTable('leads') && !Schema::hasColumn('leads', 'organization_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->index('organization_id');
            });
        }

        // Add organization_id to traceability_analytics
        if (Schema::hasTable('traceability_analytics') && !Schema::hasColumn('traceability_analytics', 'organization_id')) {
            Schema::table('traceability_analytics', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->index('organization_id');
            });
        }
    }

    public function down(): void
    {
        // Drop foreign keys and columns
        $tables = [
            'signature_record_types',
            'signature_delegations',
            'signature_revocations',
            'signature_verifications',
            'pricing',
            'leads',
            'traceability_analytics'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'organization_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['organization_id']);
                    $table->dropIndex(['organization_id']);
                    $table->dropColumn('organization_id');
                });
            }
        }
    }
};
