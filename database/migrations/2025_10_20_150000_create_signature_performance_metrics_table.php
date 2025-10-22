<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('e_signature_id')->constrained('e_signatures')->onDelete('cascade');
            
            // Timing metrics (in milliseconds)
            $table->integer('signature_creation_time_ms')->nullable();
            $table->integer('timestamp_request_time_ms')->nullable();
            $table->integer('certificate_verification_time_ms')->nullable();
            $table->integer('hash_computation_time_ms')->nullable();
            $table->integer('encryption_time_ms')->nullable();
            $table->integer('total_signature_time_ms')->nullable();
            
            // Verification metrics
            $table->integer('verification_time_ms')->nullable();
            $table->integer('revocation_check_time_ms')->nullable();
            $table->integer('ltv_validation_time_ms')->nullable();
            
            // TSA metrics
            $table->string('tsa_provider')->nullable();
            $table->integer('tsa_response_time_ms')->nullable();
            $table->integer('tsa_retry_count')->default(0);
            $table->string('tsa_status')->nullable(); // success, failed, timeout
            
            // Resource metrics
            $table->integer('memory_used_mb')->nullable();
            $table->integer('cpu_time_ms')->nullable();
            
            // Throughput metrics
            $table->integer('signatures_per_minute')->nullable();
            $table->integer('verifications_per_minute')->nullable();
            
            // Error metrics
            $table->integer('error_count')->default(0);
            $table->text('error_log')->nullable();
            
            // Bottleneck analysis
            $table->string('bottleneck_component')->nullable(); // which component took longest
            $table->integer('bottleneck_time_ms')->nullable();
            $table->decimal('bottleneck_percentage', 5, 2)->nullable(); // percentage of total time
            
            $table->timestamps();
            $table->index('e_signature_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_performance_metrics');
    }
};
