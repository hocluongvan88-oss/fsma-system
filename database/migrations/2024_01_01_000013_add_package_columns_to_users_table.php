<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('package_id')->default('lite')->after('role');
            $table->integer('max_cte_records_monthly')->default(5000)->after('package_id');
            $table->integer('max_documents')->default(20)->after('max_cte_records_monthly');
            $table->integer('max_users')->default(3)->after('max_documents');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['package_id', 'max_cte_records_monthly', 'max_documents', 'max_users']);
        });
    }
};
