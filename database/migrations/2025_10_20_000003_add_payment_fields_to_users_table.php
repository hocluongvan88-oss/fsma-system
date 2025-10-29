<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Khắc phục lỗi 'after package_id' bằng cách dùng 'after id'.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            // Cột đầu tiên sử dụng after('id') vì package_id đã bị xóa.
            if (!Schema::hasColumn('users', 'subscription_status')) {
                $table->enum('subscription_status', ['active', 'inactive', 'canceled', 'expired'])
                      ->default('inactive')
                      ->after('id'); 
            }
            
            // Các cột còn lại được thêm vào cuối bảng (mặc định không cần after() trừ khi cần vị trí chính xác)
            if (!Schema::hasColumn('users', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'last_payment_date')) {
                $table->timestamp('last_payment_date')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'payment_gateway')) {
                $table->string('payment_gateway')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'vnpay_transaction_id')) {
                $table->string('vnpay_transaction_id')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'vnpay_order_id')) {
                $table->string('vnpay_order_id')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'stripe_customer_id')) {
                $table->string('stripe_customer_id')->nullable();
            }
            
            if (!Schema::hasColumn('users', 'stripe_subscription_id')) {
                $table->string('stripe_subscription_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
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
