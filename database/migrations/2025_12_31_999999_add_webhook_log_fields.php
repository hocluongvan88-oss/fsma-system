<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Không cần dùng DB::select để kiểm tra index trong migration thông thường
// vì hàm dropUnique sẽ tự động bỏ qua nếu index không tồn tại.

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // === 1. THÊM TẤT CẢ CỘT CẦN THIẾT (bao gồm gateway và event_id bị thiếu) ===
        // Bắt buộc phải thêm các cột này để unique index ở bước 2 không bị lỗi 1072.
        Schema::table('webhook_logs', function (Blueprint $table) {
            
            // Cột cần cho Unique Index (BỊ THIẾU trong code trước đó)
            $table->string('gateway', 50)->nullable()->after('ip_address');
            $table->string('event_id', 255)->nullable()->after('gateway');
            
            // Cột cần cho logic Retry
            $table->integer('attempt_count')->default(1)->after('event_id');
            $table->timestamp('last_attempt_at')->nullable()->after('attempt_count');
        });
        
        // === 2. THÊM UNIQUE INDEX ===
        // Thao tác này phải được đặt SAU khi các cột đã được thêm.
        Schema::table('webhook_logs', function (Blueprint $table) {
            // Lệnh này gây lỗi 1072 ở lần chạy trước vì cột gateway và event_id chưa tồn tại.
            // Bây giờ nó sẽ chạy đúng.
            $table->unique(['gateway', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. XÓA UNIQUE INDEX TRƯỚC
        Schema::table('webhook_logs', function (Blueprint $table) {
            // Bỏ qua logic kiểm tra sự tồn tại, dropUnique() có thể tự xử lý
            $table->dropUnique(['gateway', 'event_id']); 
        });
        
        // 2. XÓA CÁC CỘT ĐÃ THÊM
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'event_id', 'attempt_count', 'last_attempt_at']);
        });
    }
};
