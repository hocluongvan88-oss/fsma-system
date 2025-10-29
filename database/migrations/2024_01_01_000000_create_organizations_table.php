<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            // Sử dụng id() để có cột ID tự động tăng
            $table->id(); 

            // Thông tin cơ bản
            $table->string('name')->unique();
            $table->text('description')->nullable(); // Cột này cần thiết cho Seeder

            // Cấu hình tài khoản và gói dịch vụ
            $table->boolean('is_active')->default(true);
            $table->string('package_id', 50)->default('basic')->comment('Gói dịch vụ: basic, premium, enterprise');
            
            // Giới hạn dữ liệu
            $table->integer('max_users')->default(1)->comment('Số lượng user tối đa');
            $table->integer('max_cte_records_monthly')->default(500)->comment('Số lượng CTE tối đa mỗi tháng');
            $table->integer('max_documents')->default(10)->comment('Số lượng tài liệu lưu trữ tối đa');
            
            // Thời hạn đăng ký
            $table->timestamp('subscription_start_date')->nullable();
            $table->timestamp('subscription_end_date')->nullable();
            
            // Các cột thời gian chuẩn của Laravel
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
