<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('notification_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('action'); // created, read, deleted, sent_email, failed_email
            $table->string('status')->default('success'); // success, failed
            $table->text('details')->nullable(); // JSON details
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            // Indexes for audit queries
            $table->index(['organization_id', 'created_at']);
            $table->index(['notification_id', 'action']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_audit_logs');
    }
};
