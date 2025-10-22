<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('record_type', 50)->comment('products, cte_events, etc.');
            $table->unsignedBigInteger('record_id');
            $table->string('action', 100)->comment('create, update, delete, approve');
            $table->text('reason')->nullable();
            $table->string('signature_hash')->comment('Hash of username + password + timestamp');
            $table->string('ip_address', 45);
            $table->timestamp('signed_at');
            $table->timestamps();
            
            $table->index(['record_type', 'record_id']);
            $table->index('user_id');
            $table->index('signed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_signatures');
    }
};
