<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Thêm organization_id vào retention_policies và populate dữ liệu từ created_by (User)
     */
    public function up(): void
    {
        Schema::table('retention_policies', function (Blueprint $table) {
            if (!Schema::hasColumn('retention_policies', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->index('organization_id');
            }
        });

        // 1. Vô hiệu hóa kiểm tra khóa ngoại để cho phép UPDATE an toàn
        Schema::disableForeignKeyConstraints();

        // 2. Populate organization_id từ user tạo ra Policy (giả định cột created_by là user_id)
        DB::statement('
            UPDATE retention_policies rp
            SET organization_id = (
                SELECT u.organization_id FROM users u WHERE u.id = rp.created_by
            )
            WHERE rp.organization_id IS NULL AND rp.created_by IS NOT NULL
        ');
        
        // 3. DỌN DẸP BẮT BUỘC: Xóa các policy không có organization_id hợp lệ
        DB::statement('
            DELETE FROM retention_policies WHERE organization_id IS NULL;
        ');
        
        // 4. Thêm Khóa ngoại VÀ đặt cột NOT NULL
        Schema::table('retention_policies', function (Blueprint $table) {
            
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('retention_policies', function (Blueprint $table) {
            Schema::disableForeignKeyConstraints();
            
            try {
                $table->dropForeign(['organization_id']);
            } catch (\Exception $e) {
                // Bỏ qua lỗi nếu khóa ngoại không tồn tại
            }

            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
            
            Schema::enableForeignKeyConstraints();
        });
    }
};
