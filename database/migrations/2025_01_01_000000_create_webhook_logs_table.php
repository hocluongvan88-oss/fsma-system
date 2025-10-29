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
        // Đây là file TẠO bảng bị thiếu (gây ra lỗi 1146)
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint'); // Địa chỉ webhook nhận
            $table->string('event_type')->nullable(); // Loại sự kiện (e.g., payment_success)
            $table->text('request_payload'); // Dữ liệu gửi đến (JSON)
            $table->text('response')->nullable(); // Phản hồi của hệ thống
            $table->integer('status_code')->nullable(); // Mã HTTP trả về
            $table->string('ip_address')->nullable(); // Địa chỉ IP gửi webhook
            $table->boolean('is_successful')->default(false); // Trạng thái xử lý
            $table->timestamps();
            
            // Indexes
            $table->index('event_type');
            $table->index('is_successful');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
