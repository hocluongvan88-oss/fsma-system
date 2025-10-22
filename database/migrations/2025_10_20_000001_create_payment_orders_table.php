<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('order_id')->unique(); // FSMA204_userid_timestamp
            $table->string('package_id'); // basic, premium, enterprise
            $table->string('billing_period'); // monthly, yearly
            $table->decimal('amount', 15, 2); // Amount in VND
            $table->enum('status', ['pending', 'completed', 'failed', 'expired'])->default('pending');
            $table->string('payment_gateway')->nullable(); // vnpay, stripe
            $table->string('transaction_id')->nullable(); // VNPay transaction ID
            $table->string('idempotency_key')->nullable(); // Prevent duplicate payments
            $table->json('metadata')->nullable(); // Store package details
            $table->json('response_data')->nullable(); // Store gateway response
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('expires_at')->nullable(); // Order expires after 15 minutes
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('order_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
