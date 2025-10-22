<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            $table->text('meaning_of_signature')->nullable()->after('reason');
            $table->string('signature_algorithm', 50)->default('SHA512')->after('signature_hash');
            $table->text('record_content_hash')->nullable()->after('signature_algorithm');
            $table->string('certificate_id')->nullable()->after('record_content_hash');
            $table->text('timestamp_token')->nullable()->after('certificate_id');
            $table->boolean('is_revoked')->default(false)->after('timestamp_token');
            $table->timestamp('revoked_at')->nullable()->after('is_revoked');
            $table->text('revocation_reason')->nullable()->after('revoked_at');
            $table->string('mfa_method')->nullable()->comment('totp, sms, backup_code')->after('revocation_reason');
            $table->string('user_agent')->nullable()->after('mfa_method');
            
            $table->index('certificate_id');
            $table->index('is_revoked');
        });
    }

    public function down(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            $table->dropColumn([
                'meaning_of_signature',
                'signature_algorithm',
                'record_content_hash',
                'certificate_id',
                'timestamp_token',
                'is_revoked',
                'revoked_at',
                'revocation_reason',
                'mfa_method',
                'user_agent',
            ]);
        });
    }
};
