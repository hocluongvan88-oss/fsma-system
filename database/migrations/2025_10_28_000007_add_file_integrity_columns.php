<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileIntegrityColumns extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'file_hash')) {
                $table->string('file_hash', 64)->nullable()->after('file_size');
            }

            if (!Schema::hasColumn('documents', 'metadata_hash')) {
                $table->string('metadata_hash', 64)->nullable()->after('metadata');
            }

            if (!Schema::hasColumn('documents', 'file_integrity_verified_at')) {
                $table->timestamp('file_integrity_verified_at')->nullable()->after('metadata_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['file_hash', 'metadata_hash', 'file_integrity_verified_at']);
        });
    }
}
