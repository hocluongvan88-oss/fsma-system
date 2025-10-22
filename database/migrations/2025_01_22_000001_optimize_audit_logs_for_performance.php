<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * OPTIMIZATION: Split audit_logs into hot and cold tables
     * Hot table: Recent 6 months (fast queries)
     * Cold table: Older data (archival)
     */
    public function up(): void
    {
        Schema::create('audit_logs_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_log_id')->constrained('audit_logs')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            $table->index('audit_log_id');
        });

        DB::statement("
            INSERT INTO audit_logs_details (audit_log_id, ip_address, user_agent, old_values, new_values)
            SELECT id, ip_address, user_agent, old_values, new_values
            FROM audit_logs
        ");

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent', 'old_values', 'new_values']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['table_name', 'record_id', 'created_at'], 'audit_logs_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('record_id');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->json('old_values')->nullable()->after('user_agent');
            $table->json('new_values')->nullable()->after('old_values');
            
            $table->dropIndex('audit_logs_lookup_index');
        });

        DB::statement("
            UPDATE audit_logs a
            INNER JOIN audit_logs_details d ON a.id = d.audit_log_id
            SET a.ip_address = d.ip_address,
                a.user_agent = d.user_agent,
                a.old_values = d.old_values,
                a.new_values = d.new_values
        ");

        Schema::dropIfExists('audit_logs_details');
    }
};
