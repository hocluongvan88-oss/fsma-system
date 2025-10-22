<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN package_id ENUM('free', 'basic', 'premium', 'enterprise') DEFAULT 'free'");
        
        DB::table('users')->where('package_id', 'lite')->update(['package_id' => 'basic']);
        DB::table('users')->where('package_id', 'pro')->update(['package_id' => 'premium']);
        
        DB::table('users')->where('package_id', 'free')->update([
            'max_cte_records_monthly' => 50,
            'max_documents' => 1,
            'max_users' => 1
        ]);
        
        DB::table('users')->where('package_id', 'basic')->update([
            'max_cte_records_monthly' => 500,
            'max_documents' => 10,
            'max_users' => 1
        ]);
        
        DB::table('users')->where('package_id', 'premium')->update([
            'max_cte_records_monthly' => 2500,
            'max_documents' => 0, // 0 = unlimited
            'max_users' => 3
        ]);
        
        DB::table('users')->where('package_id', 'enterprise')->update([
            'max_cte_records_monthly' => 0, // 0 = unlimited
            'max_documents' => 0,
            'max_users' => 0
        ]);
    }

    public function down(): void
    {
        DB::table('users')->where('package_id', 'basic')->update(['package_id' => 'lite']);
        DB::table('users')->where('package_id', 'premium')->update(['package_id' => 'pro']);
        DB::statement("ALTER TABLE users MODIFY COLUMN package_id ENUM('free', 'lite', 'pro', 'enterprise') DEFAULT 'free'");
    }
};
