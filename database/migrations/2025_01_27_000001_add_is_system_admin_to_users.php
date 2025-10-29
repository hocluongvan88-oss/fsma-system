<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add is_system_admin column to distinguish System Admin from Organization Admin
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_system_admin')) {
                $table->boolean('is_system_admin')->default(false)->after('role')->comment('System-wide admin with global access, not bound to organization');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_system_admin')) {
                $table->dropColumn('is_system_admin');
            }
        });
    }
};
