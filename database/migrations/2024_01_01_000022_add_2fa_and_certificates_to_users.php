<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_fa_enabled')->default(false)->after('is_active');
            $table->string('two_fa_secret')->nullable()->after('two_fa_enabled');
            $table->json('backup_codes')->nullable()->after('two_fa_secret');
            $table->timestamp('two_fa_enabled_at')->nullable()->after('backup_codes');
            
            $table->string('certificate_id')->nullable()->unique()->after('two_fa_enabled_at');
            $table->text('public_key')->nullable()->after('certificate_id');
            $table->text('certificate_pem')->nullable()->after('public_key');
            $table->timestamp('certificate_expires_at')->nullable()->after('certificate_pem');
            $table->boolean('certificate_revoked')->default(false)->after('certificate_expires_at');
            $table->timestamp('certificate_revoked_at')->nullable()->after('certificate_revoked');
            
            $table->index('certificate_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_fa_enabled',
                'two_fa_secret',
                'backup_codes',
                'two_fa_enabled_at',
                'certificate_id',
                'public_key',
                'certificate_pem',
                'certificate_expires_at',
                'certificate_revoked',
                'certificate_revoked_at',
            ]);
        });
    }
};
