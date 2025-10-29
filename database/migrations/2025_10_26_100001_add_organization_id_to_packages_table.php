<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fixed: Removed problematic DB::statement that tries to set organization_id = 1
     * This migration should only add the column, not modify data
     */
    public function up(): void
    {
        // --- BƯỚC 1: THÊM CỘT organization_id (Nếu chưa tồn tại) ---
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'organization_id')) {
                // Thêm cột, thiết lập khóa ngoại và chỉ mục.
                $table->unsignedBigInteger('organization_id')
                    ->nullable()
                    ->after('id');
                
                // Add foreign key constraint
                $table->foreign('organization_id')
                    ->references('id')
                    ->on('organizations')
                    ->onDelete('cascade');
                
                $table->index('organization_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'organization_id')) {
                $table->dropForeign(['organization_id']);
                $table->dropIndex(['organization_id']);
                $table->dropColumn('organization_id');
            }
        });
    }
};
