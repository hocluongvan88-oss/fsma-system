<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Khắc phục lỗi 'after package_id' bằng cách dùng 'after id'.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            // Xóa tham chiếu 'after package_id' vì cột đã bị xóa
            
            // Cột 'package_changed_at'
            if (!Schema::hasColumn('users', 'package_changed_at')) {
                // Đặt cột này sau cột ID an toàn
                $table->timestamp('package_changed_at')->nullable()->after('id');
            }

            // Cột 'package_changed_by' (Giả định cột liên quan)
            if (!Schema::hasColumn('users', 'package_changed_by')) {
                // Thêm sau cột vừa tạo
                $table->foreignId('package_changed_by')->nullable()->after('package_changed_at')->constrained('users')->onDelete('set null');
            }

            // Cột 'trial_used' (Giả định cột liên quan)
            if (!Schema::hasColumn('users', 'trial_used')) {
                $table->tinyInteger('trial_used')->default(0)->after('package_changed_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'package_changed_by')) {
                $table->dropForeign(['package_changed_by']);
                $table->dropColumn('package_changed_by');
            }
            if (Schema::hasColumn('users', 'package_changed_at')) {
                $table->dropColumn('package_changed_at');
            }
            if (Schema::hasColumn('users', 'trial_used')) {
                $table->dropColumn('trial_used');
            }
        });
    }
};
