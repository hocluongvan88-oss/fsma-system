<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            $table->unsignedBigInteger('delegated_by_user_id')->nullable()->after('user_id');
            $table->string('delegation_authority', 255)->nullable()->after('delegated_by_user_id');
            $table->dateTime('delegation_valid_until')->nullable()->after('delegation_authority');
            $table->boolean('is_delegated_signature')->default(false)->after('delegation_valid_until');
            
            $table->dateTime('signature_expires_at')->nullable()->after('signature_valid_until');
            $table->boolean('is_expired')->default(false)->after('signature_expires_at');
            $table->dateTime('expiration_checked_at')->nullable()->after('is_expired');
            $table->string('expiration_status', 50)->default('active')->after('expiration_checked_at');
            
            $table->string('encryption_algorithm', 50)->default('AES-256-CBC')->after('expiration_status');
            $table->string('encrypted_fields', 500)->nullable()->after('encryption_algorithm');
            
            $table->foreign('delegated_by_user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index('delegated_by_user_id');
            $table->index('is_delegated_signature');
            $table->index('is_expired');
            $table->index('expiration_status');
        });
    }

    public function down(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            $table->dropForeign(['delegated_by_user_id']);
            $table->dropIndex(['delegated_by_user_id']);
            $table->dropIndex(['is_delegated_signature']);
            $table->dropIndex(['is_expired']);
            $table->dropIndex(['expiration_status']);
            
            $table->dropColumn([
                'delegated_by_user_id',
                'delegation_authority',
                'delegation_valid_until',
                'is_delegated_signature',
                'signature_expires_at',
                'is_expired',
                'expiration_checked_at',
                'expiration_status',
                'encryption_algorithm',
                'encrypted_fields',
            ]);
        });
    }
};
