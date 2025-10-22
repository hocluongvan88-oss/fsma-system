<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE trace_relationships MODIFY COLUMN relationship_type ENUM('INPUT', 'OUTPUT', 'transformation', 'aggregation', 'disaggregation') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE trace_relationships MODIFY COLUMN relationship_type ENUM('transformation', 'aggregation', 'disaggregation') NOT NULL");
    }
};
