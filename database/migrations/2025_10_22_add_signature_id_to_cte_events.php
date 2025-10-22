<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add signature_id column to cte_events table for e-signature integration
     * This allows tracking which e-signature authorized each CTE event
     */
    public function up(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            $table->foreignId('signature_id')
                ->nullable()
                ->after('created_by')
                ->constrained('e_signatures')
                ->onDelete('set null')
                ->comment('E-signature that authorized this CTE event');
            
            // Add index for performance when querying by signature
            $table->index('signature_id');
        });
    }

    public function down(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            $table->dropForeign(['signature_id']);
            $table->dropIndex(['signature_id']);
            $table->dropColumn('signature_id');
        });
    }
};
