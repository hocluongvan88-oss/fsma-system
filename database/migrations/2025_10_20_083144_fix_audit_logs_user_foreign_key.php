<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Nếu foreign key đã tồn tại thì xóa, nhưng tránh lỗi nếu chưa từng tồn tại
            try {
                DB::statement('ALTER TABLE audit_logs DROP FOREIGN KEY audit_logs_user_id_foreign');
            } catch (\Exception $e) {
                // Bỏ qua lỗi nếu foreign key không tồn tại
            }

            // Sau đó, đảm bảo thêm lại foreign key đúng chuẩn
            if (Schema::hasColumn('audit_logs', 'user_id')) {
                $table->foreign('user_id')
                      ->references('id')->on('users')
                      ->onDelete('set null'); // hoặc 'cascade' tùy nhu cầu
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {
                // Bỏ qua nếu không tồn tại
            }
        });
    }
};
