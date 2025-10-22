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
        $failingForeignKeyName = 'audit_logs_user_id_foreign';

        // Tắt kiểm tra Khóa Ngoại tạm thời
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // BƯỚC 1: Dọn dẹp Khóa Ngoại cũ (an toàn)
        try {
            // Xóa Khóa Ngoại cũ đang gây lỗi bằng SQL thô (chắc chắn nhất)
            DB::statement("ALTER TABLE audit_logs DROP FOREIGN KEY IF EXISTS `$failingForeignKeyName`");

            // Thử xóa bằng tên cột theo quy ước Laravel
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropForeign(['user_id']); 
            });
        } catch (\Exception $e) {
            // Bỏ qua lỗi trong quá trình dọn dẹp Khóa Ngoại
            logger()->info('Safe drop of problematic foreign keys failed during UP: ' . $e->getMessage());
        }

        // Bật lại kiểm tra Khóa Ngoại
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        
        
        // BƯỚC 2: Áp dụng các thay đổi chính
        Schema::table('audit_logs', function (Blueprint $table) {
            
            // Step 2: Make user_id nullable to support ON DELETE SET NULL
            $table->unsignedBigInteger('user_id')->nullable()->change();
            
            // Step 3: Add integrity_hash column if it doesn't exist
            if (!Schema::hasColumn('audit_logs', 'integrity_hash')) {
                $table->string('integrity_hash', 64)->nullable()->after('user_agent');
            }
            
            // Step 4: Recreate foreign key with correct constraint
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            
            // Step 5: Add missing indexes for performance
            if (!Schema::hasIndex('audit_logs', 'audit_logs_action_index')) {
                $table->index('action', 'audit_logs_action_index');
            }
            if (Schema::hasColumn('audit_logs', 'integrity_hash') && !Schema::hasIndex('audit_logs', 'audit_logs_integrity_hash_index')) {
                 $table->index('integrity_hash', 'audit_logs_integrity_hash_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $failingForeignKeyName = 'audit_logs_user_id_foreign';

        // 1. RAW SQL DROP (GỠ LỖI 1091 - BẮT BUỘC)
        // Lệnh này không phụ thuộc vào trạng thái của Laravel Schema Builder.
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement("ALTER TABLE audit_logs DROP FOREIGN KEY IF EXISTS `$failingForeignKeyName`");

        // 2. LARAVEL SCHEMA CHANGES (Hoàn tác các thay đổi khác)
        Schema::table('audit_logs', function (Blueprint $table) {
            
            // Xóa Khóa Ngoại MỚI (ON DELETE SET NULL) vừa tạo trong up(). 
            // Khóa Ngoại cũ (audit_logs_user_id_foreign) đã được xóa ở trên.
            try {
                $table->dropForeign(['user_id']); 
            } catch (\Exception $e) {
                // Bỏ qua lỗi, có thể nó đã được xóa rồi
            }
            
            // Hoàn tác các cột và Index
            if (Schema::hasColumn('audit_logs', 'integrity_hash')) {
                $table->dropColumn('integrity_hash');
            }
            if (Schema::hasIndex('audit_logs', 'audit_logs_action_index')) {
                $table->dropIndex('audit_logs_action_index');
            }
            if (Schema::hasIndex('audit_logs', 'audit_logs_integrity_hash_index')) {
                $table->dropIndex('audit_logs_integrity_hash_index');
            }

            // Hoàn tác user_id về NOT NULL và tạo lại Khóa Ngoại gốc
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
        
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
};
