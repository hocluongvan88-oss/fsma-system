<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditLogTrackingToDocuments extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'audit_log_id')) {
                $table->unsignedBigInteger('audit_log_id')->nullable()->after('id');
                $table->index('audit_log_id');
            }
        });

        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'audit_log_id')) {
                $table->unsignedBigInteger('audit_log_id')->nullable()->after('id');
                $table->index('audit_log_id');
            }
        });
    }

    public function down()
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
}
