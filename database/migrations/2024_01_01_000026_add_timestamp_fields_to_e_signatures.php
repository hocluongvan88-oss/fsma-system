<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('e_signatures', function (Blueprint $table) {
            // LỖI: Dòng này đã bị xóa vì cột 'timestamp_token' đã tồn tại.
            // Nếu bạn muốn chỉnh sửa nó, bạn phải dùng ->change(), không phải tạo lại.
            // $table->longText('timestamp_token')->nullable()->after('timestamp_token'); 

            // Kiểm tra xem cột 'timestamp_token' đã tồn tại chưa để thêm 2 cột mới sau nó
            // Nếu cột 'timestamp_token' đã tồn tại:
            if (Schema::hasColumn('e_signatures', 'timestamp_token')) {
                $table->string('timestamp_provider')->nullable()->comment('freetsa, digicert, sectigo')->after('timestamp_token');
            } else {
                // Nếu cột 'timestamp_token' chưa tồn tại (tạo luôn trong migration này):
                $table->longText('timestamp_token')->nullable()->after('certificate_reference'); // Giả định vị trí sau 'certificate_reference'
                $table->string('timestamp_provider')->nullable()->comment('freetsa, digicert, sectigo')->after('timestamp_token');
            }
            
            $table->timestamp('timestamp_verified_at')->nullable()->after('timestamp_provider');
            
            // Thêm index cho các truy vấn kiểm tra timestamp
            $table->index('timestamp_verified_at');
        });
    }

    public function down(): void
    {
        // Kiểm tra cột trước khi drop (để tránh lỗi nếu migration thất bại)
        Schema::table('e_signatures', function (Blueprint $table) {
            
            // Xóa index trước
            if (Schema::hasIndex('e_signatures', ['timestamp_verified_at'])) {
                $table->dropIndex(['timestamp_verified_at']);
            }
            
            // Xóa các cột.
            $table->dropColumn(['timestamp_token', 'timestamp_provider', 'timestamp_verified_at']);
        });
    }
};
