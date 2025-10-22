<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Xóa khóa ngoại nếu tồn tại
        DB::statement("
            ALTER TABLE audit_logs
            DROP FOREIGN KEY IF EXISTS audit_logs_user_id_foreign
        ");

        Schema::table('audit_logs', function (Blueprint $table) {
            // Đảm bảo cột user_id cho phép null
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        // Thêm lại khóa ngoại nếu bảng users tồn tại
        if (Schema::hasTable('users')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
