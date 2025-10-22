<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_record_types', function (Blueprint $table) {
            $table->id();
            $table->string('record_type', 100)->unique()->comment('e.g., products, cte_events, documents, trace_records');
            $table->string('model_class')->comment('Full namespace of the model class');
            $table->string('display_name')->comment('Human-readable name for UI');
            $table->text('description')->nullable();
            $table->json('content_fields')->comment('Fields to include in content hash');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('record_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_record_types');
    }
};
