<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntegrityHashToDocuments extends Migration
{
    public function up()
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
    }

    public function down()
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
    }
}
