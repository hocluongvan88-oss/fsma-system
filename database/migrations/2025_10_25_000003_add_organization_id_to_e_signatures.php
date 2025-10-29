<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add organization_id column to e_signatures table if it doesn't exist
        if (!Schema::hasColumn('e_signatures', 'organization_id')) {
            Schema::table('e_signatures', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('user_id');
                $table->index('organization_id');
            });

            // Populate organization_id from users table
            DB::statement('
                UPDATE e_signatures es
                INNER JOIN users u ON es.user_id = u.id
                SET es.organization_id = u.organization_id
                WHERE es.organization_id IS NULL
            ');

            // Add foreign key constraint
            Schema::table('e_signatures', function (Blueprint $table) {
                $table->foreign('organization_id')
                    ->references('id')
                    ->on('organizations')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('e_signatures', 'organization_id')) {
            Schema::table('e_signatures', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropIndex(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }
    }
};
