<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * P0 CRITICAL FIX: Thêm integrity hash cho document metadata
     * 
     * Cơ chế xác minh tính toàn vẹn của document metadata
     * Sử dụng SHA-256 hash để detect unauthorized changes
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'file_hash')) {
                $table->string('file_hash', 64)->nullable()->after('file_size');
                $table->index('file_hash');
            }

            if (!Schema::hasColumn('documents', 'metadata_hash')) {
                $table->string('metadata_hash', 64)->nullable()->after('metadata');
            }
        });

        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'file_hash')) {
                $table->string('file_hash', 64)->nullable()->after('file_path');
                $table->index('file_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'file_hash')) {
                $table->dropIndex(['file_hash']);
                $table->dropColumn('file_hash');
            }
            if (Schema::hasColumn('documents', 'metadata_hash')) {
                $table->dropColumn('metadata_hash');
            }
        });

        Schema::table('document_versions', function (Blueprint $table) {
            if (Schema::hasColumn('document_versions', 'file_hash')) {
                $table->dropIndex(['file_hash']);
                $table->dropColumn('file_hash');
            }
        });
    }
};
