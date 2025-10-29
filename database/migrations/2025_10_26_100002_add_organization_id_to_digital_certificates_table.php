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
        // --- BƯỚC 1: THÊM CỘT organization_id (Nếu chưa tồn tại) VÀ KHÓA NGOẠI ---
        Schema::table('digital_certificates', function (Blueprint $table) {
            // Khóa ngoại cần phải được xóa trước khi chạy lại migration, 
            // nhưng chúng ta thêm logic để kiểm tra tránh lỗi trùng lặp cột/index.
            
            if (!Schema::hasColumn('digital_certificates', 'organization_id')) {
                // Thêm cột, thiết lập khóa ngoại, ban đầu để nullable
                $table->foreignId('organization_id')
                    ->nullable() 
                    ->after('user_id')
                    ->constrained('organizations')
                    ->onDelete('cascade');
            }
        });

        // --- BƯỚC 2: ĐẶT GIÁ TRỊ CŨ VÀ GIÁ TRỊ MẶC ĐỊNH ---
        
        // 2a. Set organization_id dựa trên user's organization (chỉ cập nhật các bản ghi chưa có giá trị)
        DB::statement('
            UPDATE digital_certificates dc
            INNER JOIN users u ON dc.user_id = u.id
            SET dc.organization_id = u.organization_id
            WHERE dc.organization_id IS NULL AND u.organization_id IS NOT NULL
        ');

        // 2b. Set default organization_id to 1 (giả sử ID=1 luôn tồn tại)
        // Đây là bước quan trọng để đảm bảo không có giá trị NULL trước khi đặt NOT NULL.
        DB::statement('UPDATE digital_certificates SET organization_id = 1 WHERE organization_id IS NULL');

        // --- BƯỚC 3: ĐẶT CỘT THÀNH BẮT BUỘC (NOT NULL) ---
        Schema::table('digital_certificates', function (Blueprint $table) {
            if (Schema::hasColumn('digital_certificates', 'organization_id')) {
                // Đặt cột thành NOT NULL (sử dụng change() để thay đổi thuộc tính)
                $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digital_certificates', function (Blueprint $table) {
            // Khắc phục lỗi 1091: Can't DROP FOREIGN KEY.
            // Phương pháp tốt nhất là sử dụng dropConstrainedForeignId() 
            // vì Laravel sẽ xử lý việc xóa khóa ngoại và cột tương ứng.

            if (Schema::hasColumn('digital_certificates', 'organization_id')) {
                // Xóa Khóa ngoại và Cột tương ứng.
                // Lệnh này an toàn và hiệu quả hơn so với dropForeign(['organization_id'])
                // vì nó tự động xác định tên khóa ngoại do foreignId() tạo ra.
                $table->dropConstrainedForeignId('organization_id');
            }
        });
    }
};
