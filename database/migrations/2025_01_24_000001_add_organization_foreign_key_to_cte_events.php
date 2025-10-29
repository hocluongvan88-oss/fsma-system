<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. VÔ HIỆU HÓA kiểm tra khóa ngoại tạm thời
        Schema::disableForeignKeyConstraints();

        // BƯỚC A: THÊM CỘT organization_id
        Schema::table('cte_events', function (Blueprint $table) {
            // Thêm cột sau partner_id, cho phép tạm thời null.
            if (!Schema::hasColumn('cte_events', 'organization_id')) {
                 $table->unsignedBigInteger('organization_id')->nullable()->after('partner_id');
            }
        });

        // BƯỚC B: CẬP NHẬT VÀ DỌN DẸP DỮ LIỆU CŨ

        // 2. Populate organization_id từ trace_records cho dữ liệu hiện có
        DB::statement('
            UPDATE cte_events ce
            INNER JOIN trace_records tr ON ce.trace_record_id = tr.id
            SET ce.organization_id = tr.organization_id
            -- Chỉ update những bản ghi chưa có ID hoặc ID bị NULL
            WHERE ce.organization_id IS NULL
        ');
        
        // 3a. DỌN DẸP DỮ LIỆU BẮT BUỘC (CRITICAL FIX):
        // Xóa các bản ghi có organization_id nhưng ID đó không tồn tại trong bảng organizations (Orphan Records).
        DB::statement('
            DELETE ce FROM cte_events ce
            LEFT JOIN organizations o ON ce.organization_id = o.id
            WHERE ce.organization_id IS NOT NULL AND o.id IS NULL;
        ');

        // 3b. Xóa TẤT CẢ CTE Events mà organization_id vẫn còn NULL sau khi update.
        // Đây là dữ liệu không gán được và sẽ vi phạm NOT NULL sau này.
        DB::statement('
            DELETE FROM cte_events WHERE organization_id IS NULL;
        ');
        
        // BƯỚC C: HOÀN THIỆN FOREIGN KEY VÀ CONSTRAINT
        Schema::table('cte_events', function (Blueprint $table) {
            // 4. Đặt organization_id là NOT NULL sau khi đã dọn dẹp dữ liệu
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            
            // 5. Thêm foreign key constraint
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade')
                ->comment('Organization that owns this CTE event');
        });
        
        // 6. KÍCH HOẠT LẠI kiểm tra khóa ngoại
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            // Vô hiệu hóa tạm thời để drop an toàn hơn
            Schema::disableForeignKeyConstraints();
            
            // Xóa khóa ngoại trước khi thay đổi thuộc tính cột
            $table->dropForeign(['organization_id']);
            
            // Nếu bạn muốn giữ lại cột sau khi rollback, đặt lại nullable()
            $table->unsignedBigInteger('organization_id')->nullable()->change();
            
            // Nếu muốn xóa cột: $table->dropColumn('organization_id');
            
            Schema::enableForeignKeyConstraints();
        });
    }
};
