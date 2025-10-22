<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegator_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('delegatee_user_id')->constrained('users')->onDelete('cascade');
            $table->string('delegation_authority', 255); // What authority is delegated
            $table->text('delegation_scope')->nullable(); // JSON: record types, actions allowed
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->boolean('is_active')->default(true);
            $table->text('revocation_reason')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
            
            $table->index('delegator_user_id');
            $table->index('delegatee_user_id');
            $table->index('is_active');
            $table->index('valid_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_delegations');
    }
};
