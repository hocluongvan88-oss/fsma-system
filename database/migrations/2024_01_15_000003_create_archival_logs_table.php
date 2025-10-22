<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archival_logs', function (Blueprint $table) {
            $table->id();
            $table->string('data_type', 50)->index();
            $table->enum('strategy', ['database', 's3_glacier', 'local'])->default('database');
            $table->integer('records_archived')->default(0);
            $table->integer('records_verified')->default(0);
            $table->integer('records_deleted_from_hot')->default(0);
            $table->text('archival_location')->nullable();
            $table->timestamp('executed_at')->index();
            $table->unsignedBigInteger('executed_by')->nullable();
            $table->enum('status', ['success', 'failed', 'partial'])->default('success')->index();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('executed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archival_logs');
    }
};
