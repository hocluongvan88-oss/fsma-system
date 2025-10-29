<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // First, ensure the column exists and modify it to have a default value
            $table->unsignedBigInteger('organization_id')->default(1)->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Revert to nullable without default
            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });
    }
};
