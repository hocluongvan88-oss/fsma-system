<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            if (!Schema::hasColumn('cte_events', 'void_count')) {
                $table->integer('void_count')->default(0)->after('status')->comment('Number of times this event has been voided');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            if (Schema::hasColumn('cte_events', 'void_count')) {
                $table->dropColumn('void_count');
            }
        });
    }
};
