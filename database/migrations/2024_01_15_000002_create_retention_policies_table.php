<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name')->unique();
            $table->string('data_type'); // trace_records, cte_events, audit_logs, e_signatures, error_logs, notifications
            $table->integer('retention_months')->default(27);
            $table->boolean('backup_before_deletion')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('data_type');
            $table->index('is_active');
        });

        Schema::create('retention_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retention_policy_id')->constrained('retention_policies')->onDelete('cascade');
            $table->string('data_type');
            $table->integer('records_deleted')->default(0);
            $table->integer('records_backed_up')->default(0);
            $table->string('backup_file_path')->nullable();
            $table->dateTime('executed_at');
            $table->string('executed_by')->nullable();
            $table->string('status'); // success, failed, partial
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index('data_type');
            $table->index('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_logs');
        Schema::dropIfExists('retention_policies');
    }
};
