<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddOrganizationIdToDocumentVersions extends Migration
{
    public function up()
    {
        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('document_id');
                $table->index('organization_id');
            }
        });

        DB::statement('UPDATE document_versions dv SET organization_id = (SELECT d.organization_id FROM documents d WHERE d.id = dv.document_id) WHERE dv.organization_id IS NULL');

        Schema::table('document_versions', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('document_versions', function (Blueprint $table) {
            try {
                $table->dropForeign(['organization_id']);
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
}
