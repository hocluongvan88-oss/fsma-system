<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fixed: Use string foreign key instead of foreignId since packages.id is string
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Kiểm tra cột 'package_id' đã tồn tại chưa trước khi thêm
            if (!Schema::hasColumn('organizations', 'package_id')) {
                $table->string('package_id', 50)
                    ->nullable()
                    ->after('is_active')
                    ->constrained('packages', 'id')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Kiểm tra cột tồn tại trước khi xóa (an toàn hơn)
            if (Schema::hasColumn('organizations', 'package_id')) {
                $table->dropForeign(['package_id']);
                $table->dropColumn('package_id');
            }
        });
    }
};
