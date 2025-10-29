<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL SECURITY FIX: Add organization_id to trace_relationships table
     * This ensures proper multi-tenant data isolation for SaaS compliance
     */
    public function up(): void
    {
        Schema::table('trace_relationships', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('id');
            $table->index('organization_id');
        });

        DB::statement('
            UPDATE trace_relationships tr
            INNER JOIN trace_records parent ON tr.parent_id = parent.id
            SET tr.organization_id = parent.organization_id
            WHERE tr.organization_id IS NULL
        ');

        Schema::table('trace_relationships', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });

        Schema::table('trace_relationships', function (Blueprint $table) {
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trace_relationships', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
