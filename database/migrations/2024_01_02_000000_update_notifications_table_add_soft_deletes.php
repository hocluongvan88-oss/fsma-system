<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Thêm các cột mới chỉ khi chúng chưa tồn tại
            if (!Schema::hasColumn('notifications', 'deleted_at')) {
                 // Add soft delete support for audit trail
                $table->softDeletes()->after('read_at');
            }

            if (!Schema::hasColumn('notifications', 'priority')) {
                // Add priority field for notification ordering
                $table->integer('priority')->default(0)->after('is_blocking');
            }

            if (!Schema::hasColumn('notifications', 'expires_at')) {
                // Add expiration field for auto-cleanup
                $table->timestamp('expires_at')->nullable()->after('priority');
            }

            if (!Schema::hasColumn('notifications', 'metadata')) {
                // Add metadata JSON field for flexible data storage
                $table->json('metadata')->nullable()->after('expires_at');
            }

            if (!Schema::hasColumn('notifications', 'notification_group')) {
                // Add notification group for deduplication
                $table->string('notification_group')->nullable()->after('metadata');
            }
            
            // --- FIX LỖI 1061: DUPLICATE KEY NAME ---
            // Lệnh này đang gây lỗi vì chỉ mục đã tồn tại. 
            // Chúng ta VÔ HIỆU HÓA nó để migration không cố gắng tạo lại chỉ mục.
            // $table->index(['user_id', 'is_read']); 

            // Nếu các chỉ mục dưới đây cũng gây lỗi, bạn cũng nên vô hiệu hóa chúng.
            $table->index(['user_id', 'is_blocking']);
            $table->index(['organization_id', 'created_at']);
            $table->index('notification_group');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Thêm kiểm tra tồn tại cột trước khi xóa
            if (Schema::hasColumn('notifications', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            $columnsToDrop = [];
            if (Schema::hasColumn('notifications', 'priority')) { $columnsToDrop[] = 'priority'; }
            if (Schema::hasColumn('notifications', 'expires_at')) { $columnsToDrop[] = 'expires_at'; }
            if (Schema::hasColumn('notifications', 'metadata')) { $columnsToDrop[] = 'metadata'; }
            if (Schema::hasColumn('notifications', 'notification_group')) { $columnsToDrop[] = 'notification_group'; }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Xóa Index an toàn (kiểm tra tên index nếu cần thiết, nhưng thường là an toàn)
            // LƯU Ý: Tên index mặc định của Laravel là 'tablename_column1_column2_index'
            // Việc dropIndex() chỉ hoạt động nếu index đó thực sự được tạo bởi migration này
            
            try {
                // Chỉ xóa index nếu nó tồn tại để tránh lỗi
                $table->dropIndex(['user_id', 'is_read']);
            } catch (\Throwable $e) { /* Index không tồn tại, bỏ qua */ }

            try {
                $table->dropIndex(['user_id', 'is_blocking']);
            } catch (\Throwable $e) { /* Index không tồn tại, bỏ qua */ }

            try {
                $table->dropIndex(['organization_id', 'created_at']);
            } catch (\Throwable $e) { /* Index không tồn tại, bỏ qua */ }
            
            try {
                $table->dropIndex('notification_group');
            } catch (\Throwable $e) { /* Index không tồn tại, bỏ qua */ }
            
            try {
                $table->dropIndex('expires_at');
            } catch (\Throwable $e) { /* Index không tồn tại, bỏ qua */ }
        });
    }
};
