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
        Schema::table('trace_relationships', function (Blueprint $table) {
            $table->foreign('cte_event_id')
                  ->references('id')
                  ->on('cte_events')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trace_relationships', function (Blueprint $table) {
            $table->dropForeign(['cte_event_id']);
        });
    }
};
