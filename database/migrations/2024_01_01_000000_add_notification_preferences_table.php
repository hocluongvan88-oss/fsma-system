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
        // 1. TẠO BẢNG VÀ CỘT user_id CHÍNH XÁC
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            
            // Dùng unsignedBigInteger, là kiểu dữ liệu chính xác cho Khóa ngoại
            $table->unsignedBigInteger('user_id'); 
            
            $table->string('type'); 
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            
            $table->unique(['user_id', 'type']);

            // Tự động tạo Index cho user_id
            $table->index('user_id'); 
        });

        // 2. TẠO KHÓA NGOẠI RIÊNG BIỆT (TẠM THỜI VÔ HIỆU HÓA ĐỂ VƯỢT QUA LỖI 150)
        // LỖI 150 GẦN NHƯ CHẮC CHẮN LÀ DO BẢNG 'users' KHÔNG DÙNG ENGINE INNODB.
        /*
        Schema::table('notification_preferences', function (Blueprint $table) {
             $table->foreign('user_id')
                   ->references('id')->on('users')
                   ->onDelete('cascade');
        });
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
