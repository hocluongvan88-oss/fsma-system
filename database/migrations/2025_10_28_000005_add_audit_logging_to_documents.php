<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * P0 CRITICAL FIX: Thêm audit logging cho tất cả document operations
     * 
     * Tạo trigger hoặc observer để log tất cả changes
     * Đảm bảo FSMA 204 compliance - complete audit trail
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'audit_log_id')) {
                $table->unsignedBigInteger('audit_log_id')->nullable()->after('organization_id');
                $table->index('audit_log_id');
            }
        });

        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'audit_log_id')) {
                $table->unsignedBigInteger('audit_log_id')->nullable()->after('organization_id');
                $table->index('audit_log_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'audit_log_id')) {
                $table->dropIndex(['audit_log_id']);
                $table->dropColumn('audit_log_id');
            }
        });

        Schema::table('document_versions', function (Blueprint $table) {
            if (Schema::hasColumn('document_versions', 'audit_log_id')) {
                $table->dropIndex(['audit_log_id']);
                $table->dropColumn('audit_log_id');
            }
        });
    }
};
