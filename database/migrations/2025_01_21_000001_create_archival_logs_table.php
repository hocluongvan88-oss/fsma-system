<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('archival_logs')) {
            Schema::create('archival_logs', function (Blueprint $table) {
                $table->id();
                $table->string('data_type', 50)->index();
                $table->string('strategy', 20); // database, s3_glacier, local
                $table->integer('records_archived')->default(0);
                $table->integer('records_verified')->default(0);
                $table->integer('records_deleted_from_hot')->default(0);
                $table->text('archival_location')->nullable();
                $table->timestamp('executed_at')->index();
                $table->string('executed_by', 100);
                $table->enum('status', ['success', 'failed', 'partial'])->default('success');
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['data_type', 'status']);
                $table->index(['executed_at', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('archival_logs');
    }
};
