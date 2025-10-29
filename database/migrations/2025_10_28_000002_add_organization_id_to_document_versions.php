<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * P0 CRITICAL FIX: Thêm organization_id vào document_versions
     * 
     * Đảm bảo organization isolation cho document versions
     * Populate organization_id từ document parent
     */
    public function up(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('document_id');
                $table->index('organization_id');
            }
        });

        Schema::disableForeignKeyConstraints();
        
        DB::statement('
            UPDATE document_versions dv
            SET organization_id = (
                SELECT d.organization_id FROM documents d WHERE d.id = dv.document_id
            )
            WHERE dv.organization_id IS NULL
        ');

        Schema::table('document_versions', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            Schema::disableForeignKeyConstraints();
            
            try {
                $table->dropForeign(['organization_id']);
            } catch (\Exception $e) {
                // Bỏ qua lỗi nếu khóa ngoại không tồn tại
            }

            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
            
            Schema::enableForeignKeyConstraints();
        });
    }
};
