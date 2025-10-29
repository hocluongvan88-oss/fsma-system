<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('has_traceability')->default(true)->after('features');
            $table->boolean('has_document_management')->default(true)->after('has_traceability');
            $table->boolean('has_e_signatures')->default(false)->after('has_document_management');
            $table->boolean('has_certificates')->default(false)->after('has_e_signatures');
            $table->boolean('has_data_retention')->default(false)->after('has_certificates');
            $table->boolean('has_archival')->default(false)->after('has_data_retention');
            $table->boolean('has_compliance_report')->default(false)->after('has_archival');
            $table->string('support_level')->default('email')->after('has_compliance_report'); // email, email_chat, priority, dedicated
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'has_traceability',
                'has_document_management',
                'has_e_signatures',
                'has_certificates',
                'has_data_retention',
                'has_archival',
                'has_compliance_report',
                'support_level'
            ]);
        });
    }
};
