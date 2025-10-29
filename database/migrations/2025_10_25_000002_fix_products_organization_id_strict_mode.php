<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FIX LỖI 1364: Xử lý triệt để vấn đề MySQL Strict Mode
     * Nguyên nhân: Migration trước chỉ thêm DEFAULT nhưng không xóa NOT NULL constraint
     */
    public function up(): void
    {
        DB::statement("SET GLOBAL sql_mode = ''");
        DB::statement("SET SESSION sql_mode = ''");

        Schema::table('products', function (Blueprint $table) {
            // Xóa foreign key cũ nếu tồn tại
            try {
                $table->dropForeign(['organization_id']);
            } catch (\Exception $e) {
                // Foreign key không tồn tại, bỏ qua
            }

            // Tạo lại cột với DEFAULT value rõ ràng
            $table->unsignedBigInteger('organization_id')->default(1)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
        });

        DB::table('organizations')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Default Organization',
                'description' => 'System default organization',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('products')
            ->whereNull('organization_id')
            ->update(['organization_id' => 1]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            try {
                $table->dropForeign(['organization_id']);
            } catch (\Exception $e) {
                // Bỏ qua nếu foreign key không tồn tại
            }

            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
        });
    }
};
