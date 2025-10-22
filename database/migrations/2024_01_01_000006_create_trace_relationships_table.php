<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trace_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->constrained('trace_records')->onDelete('cascade');
            $table->foreignId('child_id')->nullable()->constrained('trace_records')->onDelete('cascade');
            $table->enum('relationship_type', ['INPUT', 'OUTPUT', 'transformation', 'aggregation', 'disaggregation']);
            $table->unsignedBigInteger('cte_event_id')->nullable();
            $table->timestamps();
            
            $table->index(['parent_id', 'child_id']);
            $table->index('relationship_type');
            $table->index('cte_event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trace_relationships');
    }
};
