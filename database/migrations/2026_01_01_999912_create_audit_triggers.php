<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_products_insert');

        DB::unprepared("
            CREATE TRIGGER after_products_insert
            AFTER INSERT ON products
            FOR EACH ROW
            BEGIN
                DECLARE v_user_id BIGINT DEFAULT NULL;

                INSERT INTO audit_logs (user_id, action, table_name, record_id, organization_id, created_at)
                VALUES (v_user_id, 'insert', 'products', NEW.id, NEW.organization_id, NOW());
            END;
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS after_products_insert');
    }
};
