<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_certificates', function (Blueprint $table) {
            $table->string('certificate_chain')->nullable()->after('certificate_pem');
            $table->string('root_ca_certificate')->nullable()->after('certificate_chain');
            $table->string('intermediate_ca_certificate')->nullable()->after('root_ca_certificate');
            $table->string('crl_url')->nullable()->after('intermediate_ca_certificate');
            $table->string('ocsp_url')->nullable()->after('crl_url');
            $table->timestamp('crl_last_checked')->nullable()->after('ocsp_url');
            $table->timestamp('ocsp_last_checked')->nullable()->after('crl_last_checked');
            $table->boolean('is_crl_valid')->default(true)->after('ocsp_last_checked');
            $table->boolean('is_ocsp_valid')->default(true)->after('is_crl_valid');
            $table->string('certificate_usage')->default('signing')->after('is_ocsp_valid'); // signing, encryption, both
            $table->integer('signature_count')->default(0)->after('certificate_usage');
            $table->timestamp('last_used_at')->nullable()->after('signature_count');
        });
    }

    public function down(): void
    {
        Schema::table('digital_certificates', function (Blueprint $table) {
            $table->dropColumn([
                'certificate_chain',
                'root_ca_certificate',
                'intermediate_ca_certificate',
                'crl_url',
                'ocsp_url',
                'crl_last_checked',
                'ocsp_last_checked',
                'is_crl_valid',
                'is_ocsp_valid',
                'certificate_usage',
                'signature_count',
                'last_used_at',
            ]);
        });
    }
};
