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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            
            // Thông tin cơ bản
            $table->string('location_name');
            // Đã fix lỗi Data truncated bằng cách thêm 'processing' và 'distribution'
            // và thêm nullable() để linh hoạt
            $table->enum('location_type', ['warehouse', 'farm', 'processing', 'distribution'])->nullable(); 
            
            // Thông tin đăng ký (GACC/FDA)
            $table->string('gln', 13)->nullable()->unique(); // Global Location Number
            $table->string('ffrn')->nullable(); // Food Facility Registration Number (hoặc tương tự)
            
            // Địa chỉ
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('zip_code', 20)->nullable();
            $table->string('country', 100)->default('VN'); // Mặc định là Việt Nam
            
            // Liên kết tổ chức
            $table->unsignedBigInteger('organization_id')->nullable(); 
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            $table->timestamps();
            
            // Khóa Ngoại (FOREIGN KEY) - Đảm bảo tính toàn vẹn dữ liệu
            // Liên kết với bảng 'organizations'
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->onDelete('cascade'); // Khi organization bị xóa, các location liên quan cũng bị xóa
            
            // Indexes (Tối ưu hóa tốc độ truy vấn)
            $table->index('location_type');
            $table->index('gln');
            $table->index('organization_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};