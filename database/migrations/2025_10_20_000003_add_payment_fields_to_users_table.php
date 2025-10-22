<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'subscription_status')) {
                $table->enum('subscription_status', ['active', 'inactive', 'canceled', 'expired'])->default('inactive')->after('package_id');
            }
            
            if (!Schema::hasColumn('users', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('subscription_status');
            }
            
            if (!Schema::hasColumn('users', 'last_payment_date')) {
                $table->timestamp('last_payment_date')->nullable()->after('subscription_ends_at');
            }
            
            if (!Schema::hasColumn('users', 'payment_gateway')) {
                $table->string('payment_gateway')->nullable()->after('last_payment_date');
            }
            
            if (!Schema::hasColumn('users', 'vnpay_transaction_id')) {
                $table->string('vnpay_transaction_id')->nullable()->after('payment_gateway');
            }
            
            if (!Schema::hasColumn('users', 'vnpay_order_id')) {
                $table->string('vnpay_order_id')->nullable()->after('vnpay_transaction_id');
            }
            
            if (!Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable()->after('vnpay_order_id');
            }
            
            if (!Schema::hasColumn('users', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['subscription_status', 'subscription_ends_at', 'last_payment_date', 'payment_gateway', 
                       'vnpay_transaction_id', 'vnpay_order_id', 'stripe_customer_id', 'stripe_subscription_id'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
