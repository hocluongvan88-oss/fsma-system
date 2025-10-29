<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Thêm cột tổ chức và Index trước
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->index('organization_id');
            }
        });

        // Vô hiệu hóa kiểm tra khóa ngoại để cho phép UPDATE an toàn
        Schema::disableForeignKeyConstraints();

        // 2. Populate organization_id từ user relationship
        DB::statement('
            UPDATE audit_logs al
            SET organization_id = (
                SELECT u.organization_id FROM users u WHERE u.id = al.user_id
            )
            WHERE al.organization_id IS NULL AND al.user_id IS NOT NULL
        ');

        // 3a. DỌN DẸP DỮ LIỆU BẮT BUỘC (CRITICAL FIX):
        // Xóa bản ghi có organization_id không hợp lệ (Orphan Records)
        DB::statement('
            DELETE al FROM audit_logs al
            LEFT JOIN organizations o ON al.organization_id = o.id
            WHERE al.organization_id IS NOT NULL AND o.id IS NULL;
        ');
        
        // 3b. Xóa bất kỳ Audit Logs nào mà sau khi UPDATE vẫn còn organization_id là NULL.
        DB::statement('
            DELETE FROM audit_logs WHERE organization_id IS NULL;
        ');
        
        // 4. Thêm Khóa ngoại VÀ đặt cột NOT NULL
        Schema::table('audit_logs', function (Blueprint $table) {
            
            // Đặt cột NOT NULL trước khi thêm Khóa ngoại
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            
            // Thêm Khóa ngoại: Đây là phương thức chuẩn. 
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
        });

        // Kích hoạt lại kiểm tra khóa ngoại
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            Schema::disableForeignKeyConstraints();
            
            // Dựa vào try/catch là cách an toàn nhất để xóa khóa ngoại nếu nó có thể tồn tại
            try {
                $table->dropForeign(['organization_id']);
            } catch (\Exception $e) {
                // Ignore lỗi nếu khóa ngoại không tồn tại
            }

            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
            
            Schema::enableForeignKeyConstraints();
        });
    }
};
