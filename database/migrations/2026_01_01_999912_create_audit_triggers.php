<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Xóa trigger cũ nếu tồn tại để tránh lỗi "trigger already exists"
        DB::unprepared('DROP TRIGGER IF EXISTS after_products_insert');

        // Tạo trigger mới, đảm bảo cú pháp hợp lệ
        DB::unprepared("
            CREATE TRIGGER after_products_insert
            AFTER INSERT ON products
            FOR EACH ROW
            BEGIN
                DECLARE v_user_id BIGINT DEFAULT NULL;

                INSERT INTO audit_logs (user_id, action, table_name, record_id, created_at)
                VALUES (v_user_id, 'insert', 'products', NEW.id, NOW());
            END;
        ");
    }

    public function down(): void
    {
        // Xóa trigger khi rollback
        DB::unprepared('DROP TRIGGER IF EXISTS after_products_insert');
    }
};
