<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFulltextIndexToDocuments extends Migration
{
    public function up()
    {
        // Add full-text index for MySQL full-text search
        DB::statement('ALTER TABLE documents ADD FULLTEXT INDEX documents_fulltext_idx (doc_number, title, description)');
    }

    public function down()
    {
        DB::statement('ALTER TABLE documents DROP INDEX documents_fulltext_idx');
    }
}
