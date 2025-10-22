<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('export_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('export_id', 50)->unique(); // EX-XXXXXXXXXX format
            $table->enum('file_type', ['json', 'xml', 'csv']);
            $table->enum('export_scope', ['all', 'product', 'tlc']); // all events, by product, or by TLC
            $table->string('scope_value')->nullable(); // product_id or tlc value
            $table->longText('content_hash'); // SHA-256 hash of exported content
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->unsignedInteger('record_count'); // number of records exported
            $table->string('start_record_id')->nullable(); // first record ID in export
            $table->string('end_record_id')->nullable(); // last record ID in export
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_verified')->default(false); // whether export has been verified
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('export_id');
            $table->index('file_type');
            $table->index('export_scope');
            $table->index('created_at');
            $table->index('content_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_logs');
    }
};
