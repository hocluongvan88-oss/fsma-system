<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSemanticVersioningToDocumentVersions extends Migration
{
    /**
     * P2 FIX: Thêm semantic versioning support cho document versions
     * 
     * Thêm change_type để track major/minor/patch changes
     * Note: file_hash đã được thêm trong migration 2025_10_28_000003
     */
    public function up()
    {
        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'change_type')) {
                $table->enum('change_type', ['major', 'minor', 'patch'])->default('patch')->after('version');
            }
        });
        
        // Update existing versions to have default change_type
        DB::table('document_versions')
            ->whereNull('change_type')
            ->update(['change_type' => 'patch']);
    }

    public function down()
    {
        Schema::table('document_versions', function (Blueprint $table) {
            if (Schema::hasColumn('document_versions', 'change_type')) {
                $table->dropColumn('change_type');
            }
        });
    }
}
