<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('error_type');
            $table->text('error_message');
            $table->integer('error_code')->nullable();
            $table->string('file_path');
            $table->integer('line_number');
            $table->json('stack_trace')->nullable();
            $table->json('context')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('url')->nullable();
            $table->string('method')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('error_hash')->index();
            $table->boolean('is_resolved')->default(false)->index();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->enum('severity', ['info', 'warning', 'error', 'critical'])->default('error')->index();
            $table->integer('frequency')->default(1);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['error_hash', 'is_resolved']);
            $table->index(['severity', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
