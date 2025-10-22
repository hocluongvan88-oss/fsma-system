<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transformation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transformation_event_id')->constrained('cte_events')->onDelete('cascade');
            $table->foreignId('input_trace_record_id')->constrained('trace_records')->onDelete('cascade');
            $table->decimal('quantity_used', 10, 2);
            $table->string('unit', 20);
            $table->timestamps();
            
            $table->index('transformation_event_id');
            $table->index('input_trace_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transformation_items');
    }
};
