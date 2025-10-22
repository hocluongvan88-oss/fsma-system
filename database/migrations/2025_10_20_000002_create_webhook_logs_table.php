<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway'); // stripe, vnpay
            $table->string('event_id')->unique(); // Stripe event ID or VNPay transaction ID
            $table->string('event_type'); // customer.subscription.created, invoice.payment_succeeded, etc.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->json('payload'); // Full webhook payload
            $table->json('response')->nullable(); // Our response to webhook
            $table->text('error_message')->nullable(); // Error details if failed
            $table->string('ip_address')->nullable();
            $table->integer('attempt_count')->default(1);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('event_id');
            $table->index('gateway');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
