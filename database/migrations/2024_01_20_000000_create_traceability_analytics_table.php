<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traceability_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trace_record_id')->constrained('trace_records')->onDelete('cascade');
            $table->enum('query_type', ['public', 'admin_report', 'api'])->default('public');
            $table->enum('direction', ['backward', 'forward', 'both'])->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['trace_record_id', 'created_at']);
            $table->index(['query_type', 'created_at']);
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traceability_analytics');
    }
};
