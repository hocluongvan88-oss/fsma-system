<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kiểm tra bảng tồn tại và cột signature_format chưa có
        if (Schema::hasTable('e_signatures') && !Schema::hasColumn('e_signatures', 'signature_format')) {
            Schema::table('e_signatures', function (Blueprint $table) {
                // XAdES Format Support
                $table->string('signature_format')->default('SHA512')->after('signature_algorithm');
                $table->text('xades_metadata')->nullable()->after('signature_format');
                $table->string('certificate_subject')->nullable()->after('xades_metadata');
                $table->string('certificate_issuer')->nullable()->after('certificate_subject');
                $table->string('certificate_serial_number')->nullable()->after('certificate_issuer');
                $table->string('tsa_url')->nullable()->after('certificate_serial_number');
                $table->string('tsa_certificate_subject')->nullable()->after('tsa_url');
                
                // Long-Term Validation (LTV) Support
                $table->text('ltv_timestamp_chain')->nullable()->after('tsa_certificate_subject');
                $table->text('ltv_certificate_chain')->nullable()->after('ltv_timestamp_chain');
                $table->text('ltv_crl_response')->nullable()->after('ltv_certificate_chain');
                $table->text('ltv_ocsp_response')->nullable()->after('ltv_crl_response');
                $table->timestamp('ltv_last_validation_at')->nullable()->after('ltv_ocsp_response');
                $table->boolean('ltv_enabled')->default(false)->after('ltv_last_validation_at');
                
                // Batch Operation Tracking
                $table->string('batch_operation_id')->nullable()->index()->after('ltv_enabled');
                $table->string('batch_operation_type')->nullable()->after('batch_operation_id');
                $table->integer('batch_operation_sequence')->nullable()->after('batch_operation_type');
                $table->integer('batch_total_count')->nullable()->after('batch_operation_sequence');
                
                // Enhanced Attributes Logging
                $table->text('signature_attributes')->nullable()->after('batch_total_count');
                $table->text('signature_metadata')->nullable()->after('signature_attributes');
                $table->string('signature_status')->default('valid')->after('signature_metadata');
                
                // Indexes for performance
                $table->index('signature_format');
                $table->index('ltv_enabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('e_signatures')) {
            Schema::table('e_signatures', function (Blueprint $table) {
                // Drop indexes (không cần Doctrine)
                if (Schema::hasColumn('e_signatures', 'signature_format')) {
                    $table->dropIndex(['signature_format']);
                }

                if (Schema::hasColumn('e_signatures', 'ltv_enabled')) {
                    $table->dropIndex(['ltv_enabled']);
                }

                if (Schema::hasColumn('e_signatures', 'batch_operation_id')) {
                    $table->dropIndex(['batch_operation_id']);
                }

                // Drop columns nếu tồn tại
                $columns = [
                    'signature_format',
                    'xades_metadata',
                    'certificate_subject',
                    'certificate_issuer',
                    'certificate_serial_number',
                    'tsa_url',
                    'tsa_certificate_subject',
                    'ltv_timestamp_chain',
                    'ltv_certificate_chain',
                    'ltv_crl_response',
                    'ltv_ocsp_response',
                    'ltv_last_validation_at',
                    'ltv_enabled',
                    'batch_operation_id',
                    'batch_operation_type',
                    'batch_operation_sequence',
                    'batch_total_count',
                    'signature_attributes',
                    'signature_metadata',
                    'signature_status',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('e_signatures', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
