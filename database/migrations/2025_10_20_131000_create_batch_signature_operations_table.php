<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_signature_operations', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_operation_id')->unique()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('operation_type'); // 'revoke', 'verify', 'resign'
            $table->integer('total_signatures');
            $table->integer('processed_count')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('reason')->nullable();
            $table->text('details')->nullable();
            $table->json('error_log')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('status');
            $table->index('operation_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_signature_operations');
    }
};
