<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * P0 CRITICAL FIX: Prevent cascade delete của document_versions
     * 
     * Sử dụng soft delete thay vì cascade delete
     * Đảm bảo document versions không bị xóa vô tình khi document bị xóa
     */
    public function up(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            if (Schema::hasColumn('document_versions', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
