<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_revocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signature_id')->constrained('e_signatures')->onDelete('cascade');
            $table->foreignId('revoked_by_user_id')->constrained('users')->onDelete('restrict');
            $table->string('revocation_reason', 500);
            $table->string('revocation_category')->comment('user_request, security_breach, data_modification, compliance, other');
            $table->text('revocation_details')->nullable()->comment('Additional details about revocation');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_emergency_revocation')->default(false)->comment('Emergency revocation flag');
            $table->timestamp('revoked_at');
            $table->timestamps();
            
            $table->index('signature_id');
            $table->index('revoked_by_user_id');
            $table->index('revoked_at');
            $table->index('revocation_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_revocations');
    }
};
