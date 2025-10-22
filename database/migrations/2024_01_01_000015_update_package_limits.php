<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update package limits to match new pricing structure
        DB::table('users')->where('package_id', 'lite')->update([
            'max_cte_records_monthly' => 500,
            'max_documents' => 10,
            'max_users' => 1
        ]);
        
        DB::table('users')->where('package_id', 'pro')->update([
            'max_cte_records_monthly' => 2500,
            'max_documents' => 0, // 0 = unlimited
            'max_users' => 3
        ]);
        
        DB::table('users')->where('package_id', 'enterprise')->update([
            'max_cte_records_monthly' => 0, // 0 = unlimited
            'max_documents' => 0,
            'max_users' => 0
        ]);
        
        // Add free tier package
        DB::statement("ALTER TABLE users MODIFY COLUMN package_id ENUM('free', 'lite', 'pro', 'enterprise') DEFAULT 'free'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN package_id ENUM('lite', 'pro', 'enterprise') DEFAULT 'lite'");
    }
};
