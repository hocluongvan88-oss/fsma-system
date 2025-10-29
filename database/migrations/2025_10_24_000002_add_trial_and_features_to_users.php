<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Khắc phục lỗi 'after package_id' bằng cách dùng 'after created_at'.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            // Xóa tham chiếu 'after package_id' vì cột đã bị xóa
            
            // Thêm cột 'trial_started_at'
            if (!Schema::hasColumn('users', 'trial_started_at')) {
                // Đặt cột này sau created_at
                $table->timestamp('trial_started_at')->nullable()->after('created_at');
            }

            // Thêm cột 'trial_expires_at' (Giả định cột liên quan)
            if (!Schema::hasColumn('users', 'trial_expires_at')) {
                $table->timestamp('trial_expires_at')->nullable();
            }

            // Thêm các cột max_features (Giả định các cột tính năng)
            if (!Schema::hasColumn('users', 'max_cte_records_monthly')) {
                $table->integer('max_cte_records_monthly')->default(0);
            }
            if (!Schema::hasColumn('users', 'max_documents')) {
                $table->integer('max_documents')->default(0);
            }
            if (!Schema::hasColumn('users', 'max_users')) {
                $table->integer('max_users')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'trial_started_at', 
                'trial_expires_at', 
                'max_cte_records_monthly', 
                'max_documents', 
                'max_users'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
