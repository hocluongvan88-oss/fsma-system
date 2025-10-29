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
        // Hàm này sẽ chạy khi bạn thực hiện php artisan migrate
        Schema::table('error_logs', function (Blueprint $table) {
            // Thêm cột 'organization_id' vào bảng 'error_logs'
            // unsignedBigInteger: Kiểu dữ liệu phù hợp cho ID khóa ngoại
            // after('user_id'): Đặt cột mới sau cột 'user_id' để dễ quản lý (tùy chọn)
            // nullable(): Cho phép cột này có giá trị NULL (tùy chọn, tùy vào yêu cầu nghiệp vụ)
            $table->unsignedBigInteger('organization_id')->after('user_id')->nullable(); 

            // (Tùy chọn) Khai báo Khóa ngoại
            // Điều này đảm bảo tính toàn vẹn dữ liệu, liên kết với bảng 'organizations'
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('cascade'); // Khi xóa organization, các log liên quan sẽ bị xóa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hàm này sẽ chạy khi bạn thực hiện php artisan migrate:rollback
        Schema::table('error_logs', function (Blueprint $table) {
            // (Quan trọng) Phải xóa Khóa ngoại trước khi xóa cột
            $table->dropForeign(['organization_id']);
            
            // Xóa cột 'organization_id'
            $table->dropColumn('organization_id');
        });
    }
};