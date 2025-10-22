<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('doc_number')->unique();
            $table->string('title');
            $table->enum('type', ['traceability_plan', 'sop', 'fda_correspondence', 'training', 'other']);
            $table->enum('status', ['draft', 'review', 'approved', 'archived'])->default('draft');
            $table->string('version', 20)->default('1.0');
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('file_path')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('status');
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
