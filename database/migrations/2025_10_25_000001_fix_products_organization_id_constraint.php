<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix existing products table to ensure organization_id has proper constraints
     * This migration handles the case where the table already exists with incorrect schema
     */
    public function up(): void
    {
        // Only run if table exists and needs fixing
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // This fixes the "Field 'organization_id' doesn't have a default value" error
                $table->unsignedBigInteger('organization_id')->default(1)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->change();
            });
        }
    }
};
