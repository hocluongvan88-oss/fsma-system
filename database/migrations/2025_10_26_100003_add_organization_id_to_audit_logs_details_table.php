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
        // --- BƯỚC 1: THÊM CỘT organization_id (Nếu chưa tồn tại) ---
        Schema::table('audit_logs_details', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs_details', 'organization_id')) {
                // Thêm cột, thiết lập khóa ngoại
                $table->foreignId('organization_id')
                    ->nullable() // Ban đầu để nullable
                    ->after('audit_log_id')
                    ->constrained('organizations')
                    ->onDelete('cascade');
                
                // foreignId() đã thêm index.
            }
        });

        // --- BƯỚC 2: CẬP NHẬT DỮ LIỆU ---
        
        // 2a. Set organization_id dựa trên tổ chức của audit_log cha
        DB::statement('
            UPDATE audit_logs_details ald
            INNER JOIN audit_logs al ON ald.audit_log_id = al.id
            SET ald.organization_id = al.organization_id
            WHERE ald.organization_id IS NULL AND al.organization_id IS NOT NULL
        ');

        // 2b. Set default organization_id to 1 for any remaining records
        DB::statement('UPDATE audit_logs_details SET organization_id = 1 WHERE organization_id IS NULL');

        // --- BƯỚC 3: ĐẶT CỘT THÀNH BẮT BUỘC (NOT NULL) ---
        Schema::table('audit_logs_details', function (Blueprint $table) {
            // Chỉ thay đổi nếu cột tồn tại
            if (Schema::hasColumn('audit_logs_details', 'organization_id')) {
                // Đặt cột thành NOT NULL
                $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs_details', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs_details', 'organization_id')) {
                // Bỏ Khóa ngoại
                $table->dropConstrainedForeignId('organization_id');
                // Bỏ cột
                $table->dropColumn('organization_id');
            }
        });
    }
};
