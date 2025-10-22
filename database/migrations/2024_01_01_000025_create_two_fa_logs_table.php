<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('two_fa_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('method')->comment('totp, sms, backup_code');
            $table->boolean('success')->default(false);
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('attempted_at');
            $table->index('success');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('two_fa_logs');
    }
};
