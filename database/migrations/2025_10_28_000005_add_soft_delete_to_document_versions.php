<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteToDocumentVersions extends Migration
{
    public function up()
    {
        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    public function down()
    {
        Schema::table('document_versions', function (Blueprint $table) {
            if (Schema::hasColumn('document_versions', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
}
