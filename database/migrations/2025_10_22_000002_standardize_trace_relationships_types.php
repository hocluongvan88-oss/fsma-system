<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * FSMA 204 Compliance: Standardize trace_relationships types
     * 
     * Current issue: Inconsistent relationship types
     * - Some use 'INPUT', 'OUTPUT'
     * - Some use 'transformation', 'aggregation', 'disaggregation'
     * 
     * Solution: Standardize to INPUT/OUTPUT only for FSMA 204 compliance
     * - INPUT: Parent → Child (receiving, transformation input)
     * - OUTPUT: Child → Child or Child → null (transformation output, shipping)
     */
    public function up(): void
    {
        // First, migrate existing data to standardized types
        DB::statement("
            UPDATE trace_relationships 
            SET relationship_type = 'INPUT' 
            WHERE relationship_type IN ('transformation', 'aggregation')
        ");
        
        DB::statement("
            UPDATE trace_relationships 
            SET relationship_type = 'OUTPUT' 
            WHERE relationship_type = 'disaggregation'
        ");
        
        // Now update the enum to only allow INPUT/OUTPUT
        Schema::table('trace_relationships', function (Blueprint $table) {
            // Drop the old enum and create new one with only INPUT/OUTPUT
            DB::statement("
                ALTER TABLE trace_relationships 
                MODIFY COLUMN relationship_type ENUM('INPUT', 'OUTPUT') NOT NULL
            ");
        });
    }

    public function down(): void
    {
        // Restore original enum values
        Schema::table('trace_relationships', function (Blueprint $table) {
            DB::statement("
                ALTER TABLE trace_relationships 
                MODIFY COLUMN relationship_type ENUM('INPUT', 'OUTPUT', 'transformation', 'aggregation', 'disaggregation') NOT NULL
            ");
        });
    }
};
