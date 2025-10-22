<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            $table->timestamp('signature_valid_from')->nullable()->after('signed_at')->comment('Signature validity start date');
            $table->timestamp('signature_valid_until')->nullable()->after('signature_valid_from')->comment('Signature validity end date');
            $table->integer('signature_validity_period_days')->default(365)->after('signature_valid_until')->comment('Validity period in days');
            
            $table->text('timestamp_token_der')->nullable()->after('timestamp_token')->comment('DER encoded timestamp token');
            $table->timestamp('timestamp_utc_time')->nullable()->after('timestamp_token_der')->comment('UTC time from timestamp');
            $table->string('timestamp_tsa_url')->nullable()->after('timestamp_utc_time')->comment('TSA URL used');
            $table->text('timestamp_tsa_certificate')->nullable()->after('timestamp_tsa_url')->comment('TSA certificate subject');
            
            $table->boolean('certificate_revocation_checked')->default(false)->after('timestamp_tsa_certificate')->comment('Whether certificate revocation was checked');
            $table->timestamp('certificate_revocation_checked_at')->nullable()->after('certificate_revocation_checked')->comment('When revocation was last checked');
            $table->string('certificate_revocation_status')->nullable()->after('certificate_revocation_checked_at')->comment('good, revoked, unknown');
            $table->text('certificate_revocation_reason')->nullable()->after('certificate_revocation_status')->comment('Reason if revoked');
            
            $table->json('verification_report')->nullable()->after('certificate_revocation_reason')->comment('Detailed verification report');
            $table->boolean('verification_passed')->nullable()->after('verification_report')->comment('Overall verification result');
            $table->timestamp('last_verified_at')->nullable()->after('verification_passed')->comment('Last verification timestamp');
            
            // Add indexes for performance
            $table->index('signature_valid_until');
            $table->index('certificate_revocation_checked');
            $table->index('verification_passed');
        });
    }

    public function down(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            $table->dropColumn([
                'signature_valid_from',
                'signature_valid_until',
                'signature_validity_period_days',
                'timestamp_token_der',
                'timestamp_utc_time',
                'timestamp_tsa_url',
                'timestamp_tsa_certificate',
                'certificate_revocation_checked',
                'certificate_revocation_checked_at',
                'certificate_revocation_status',
                'certificate_revocation_reason',
                'verification_report',
                'verification_passed',
                'last_verified_at',
            ]);
        });
    }
};
