<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper method to check if a foreign key exists in the database schema.
     */
    private function foreignKeyExists(string $table, string $fkName): bool
    {
        $database = DB::connection()->getDatabaseName();
        // Laravel's default FK name structure: table_column_foreign
        // We use the full constraint name here.
        $result = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
        ", [$database, $table, $fkName]);
        
        return count($result) > 0;
    }

    /**
     * Run the migrations.
     * Thêm tất cả các Khóa ngoại organization_id vào các bảng đã có cột.
     */
    public function up(): void
    {
        // Tắt kiểm tra khóa ngoại để cho phép thêm nhiều FK cùng lúc
        Schema::disableForeignKeyConstraints();

        $tables = [
            'trace_records',
            'products',
            'locations',
            'partners',
            'users',
            'documents',
            'cte_events',
            'audit_logs',
            'retention_policies',
            'trace_relationships',
            'e_signatures',
        ];

        foreach ($tables as $tableName) {
            // Chỉ cố gắng thêm FK nếu bảng tồn tại VÀ cột organization_id đã có
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'organization_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Tên FK chuẩn của Laravel: {tableName}_{columnName}_foreign
                    $fkName = $tableName . '_organization_id_foreign';

                    // 1. Kiểm tra sự tồn tại của FK trước khi thêm
                    if (!$this->foreignKeyExists($tableName, $fkName)) {
                         $table->foreign('organization_id', $fkName)
                             ->references('id')
                             ->on('organizations')
                             ->onDelete('cascade');
                    }
                    // Bỏ try/catch cũ vì hàm kiểm tra đã xử lý việc trùng lặp
                });
            }
        }

        // Kích hoạt lại kiểm tra khóa ngoại
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            'e_signatures',
            'trace_relationships',
            'retention_policies',
            'audit_logs',
            'cte_events',
            'documents',
            'users',
            'partners',
            'locations',
            'products',
            'trace_records',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'organization_id')) {
                 Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    // Tên FK chuẩn của Laravel: {tableName}_{columnName}_foreign
                    $fkName = $tableName . '_organization_id_foreign';
                    
                    // Kiểm tra sự tồn tại của FK trước khi xóa
                    if ($this->foreignKeyExists($tableName, $fkName)) {
                        try {
                            $table->dropForeign($fkName);
                        } catch (\Exception $e) {
                            // Bỏ qua lỗi nếu việc drop không thành công (ví dụ: tên FK không khớp chính xác)
                        }
                    }
                 });
            }
        }

        Schema::enableForeignKeyConstraints();
    }
};
