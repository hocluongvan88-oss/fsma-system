<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organization_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('feature_name'); // e.g., 'cte_records_monthly', 'documents', 'users'
            $table->integer('used_count')->default(0);
            $table->integer('limit_count')->nullable(); // null means unlimited
            $table->timestamp('reset_at')->nullable(); // For monthly quotas
            $table->timestamps();

            // Unique constraint: one quota per organization per feature
            $table->unique(['organization_id', 'feature_name']);
            
            // Index for faster queries
            $table->index(['organization_id', 'feature_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_quotas');
    }
};
