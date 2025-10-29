<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Chỉnh sửa cột title/message và thêm cột enum cho các loại thông báo tùy chỉnh.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // 1. Cho phép title và message là nullable (Đã fix lỗi trước)
            if (Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable()->change();
            }
            if (Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->nullable()->change();
            }
            
            // 2. Thêm cột 'data' cho Laravel's default notification structure (nếu chưa có)
            if (!Schema::hasColumn('notifications', 'data')) {
                $table->json('data')->nullable()->after('message');
            }
            
            // 3. THAY THẾ logic ENUM của bạn bằng cột MỚI
            // Giữ cột 'type' (string) cho tên class. Thêm 'notification_type' cho ENUM.
            if (!Schema::hasColumn('notifications', 'notification_type')) {
                 $table->enum('notification_type', [
                    'general', // Thêm giá trị mặc định
                    'quota_warning', 
                    'quota_reached', 
                    'upgrade_success', 
                    'feature_locked',
                    'package_changed'
                ])->default('general')->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop cột data và notification_type
            if (Schema::hasColumn('notifications', 'data')) {
                $table->dropColumn('data');
            }
            if (Schema::hasColumn('notifications', 'notification_type')) {
                $table->dropColumn('notification_type');
            }
            
            // Khôi phục title/message về NOT NULL (Chỉ khi cần thiết)
            if (Schema::hasColumn('notifications', 'title')) {
                 $table->string('title')->nullable(false)->change();
            }
            if (Schema::hasColumn('notifications', 'message')) {
                 $table->text('message')->nullable(false)->change();
            }
        });
    }
};
