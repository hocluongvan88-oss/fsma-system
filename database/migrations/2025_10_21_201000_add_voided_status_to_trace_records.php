<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'voided' to the status enum
        DB::statement("ALTER TABLE trace_records MODIFY COLUMN status ENUM('active', 'consumed', 'shipped', 'destroyed', 'voided') DEFAULT 'active'");
    }

    public function down(): void
    {
        // Remove 'voided' from the status enum
        DB::statement("ALTER TABLE trace_records MODIFY COLUMN status ENUM('active', 'consumed', 'shipped', 'destroyed') DEFAULT 'active'");
    }
};
