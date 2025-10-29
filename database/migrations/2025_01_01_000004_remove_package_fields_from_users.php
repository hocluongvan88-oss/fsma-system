<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 

return new class extends Migration
{
    /**
     * Run the migrations.
     * Xóa các cột liên quan đến Package khỏi bảng 'users', chuyển trách nhiệm cho 'organizations'.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            // 1. Xóa cột package_id AN TOÀN
            if (Schema::hasColumn('users', 'package_id')) {
                
                // *** GIẢI PHÁP CUỐI CÙNG: Bỏ qua dropForeign() gây lỗi 1091. ***
                // Nếu cột tồn tại và có khóa ngoại, database sẽ tự xóa khóa ngoại khi cột bị xóa.
                // Nếu khóa ngoại không tồn tại, database sẽ không ném lỗi 1091.

                $table->dropColumn('package_id'); 
            }

            // 2. Xóa các cột Quota cũ (chuyển sang organization_quotas)
            // Cần kiểm tra tồn tại của cột trước khi xóa
            if (Schema::hasColumn('users', 'max_cte_records_monthly')) {
                $table->dropColumn('max_cte_records_monthly');
            }
            if (Schema::hasColumn('users', 'max_documents')) {
                $table->dropColumn('max_documents');
            }
            if (Schema::hasColumn('users', 'max_users')) {
                $table->dropColumn('max_users');
            }
        });
    }

    /**
     * Reverse the migrations.
     * Khôi phục các cột cũ về bảng 'users' (chỉ nên dùng cho rollback).
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Khôi phục cột package_id
            if (!Schema::hasColumn('users', 'package_id')) {
                $table->foreignId('package_id')
                    ->nullable()
                    ->constrained('packages')
                    ->onDelete('set null');
            }

            // Khôi phục các cột Quota
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
};
