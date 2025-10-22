<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signature_id')->constrained('e_signatures')->onDelete('cascade');
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('verification_type', 50); // manual, automatic, batch
            $table->string('verification_status', 50); // valid, invalid, expired, revoked
            $table->text('verification_details')->nullable();
            $table->json('verification_checks')->nullable(); // Array of checks performed
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->integer('verification_duration_ms')->nullable(); // Time taken to verify
            $table->boolean('is_brute_force_attempt')->default(false);
            $table->timestamps();
            
            $table->index('signature_id');
            $table->index('verified_by_user_id');
            $table->index('verification_status');
            $table->index('verification_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_verifications');
    }
};
