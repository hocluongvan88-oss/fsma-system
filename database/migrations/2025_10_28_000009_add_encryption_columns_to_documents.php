<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEncryptionColumnsToDocuments extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'is_encrypted')) {
                $table->boolean('is_encrypted')->default(false)->after('file_size');
            }

            if (!Schema::hasColumn('documents', 'encrypted_at')) {
                $table->timestamp('encrypted_at')->nullable()->after('is_encrypted');
            }
        });

        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'is_encrypted')) {
                $table->boolean('is_encrypted')->default(false)->after('file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['is_encrypted', 'encrypted_at']);
        });

        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropColumn(['is_encrypted']);
        });
    }
}
