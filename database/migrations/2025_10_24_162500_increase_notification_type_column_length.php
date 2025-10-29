<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 💡 LƯU Ý: Đảm bảo đã chạy "composer require doctrine/dbal"
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'type')) {
                // Tăng độ dài cột 'type' lên 500 ký tự để chứa tên class Notification dài
                $table->string('type', 500)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'type')) {
                $table->string('type', 255)->change();
            }
        });
    }
};
