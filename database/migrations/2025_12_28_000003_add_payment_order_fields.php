<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_orders', 'organization_id')) {
                $table->string('organization_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('payment_orders', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('payment_orders', 'stripe_invoice_id')) {
                $table->string('stripe_invoice_id')->nullable()->after('stripe_session_id');
            }
            if (!Schema::hasColumn('payment_orders', 'error_message')) {
                $table->string('error_message')->nullable()->after('response_data');
            }
            if (!Schema::hasColumn('payment_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('updated_at');
            }
        });
        
        $indexes = DB::select("SHOW INDEX FROM payment_orders WHERE Key_name = 'payment_orders_idempotency_key_unique'");
        if (empty($indexes)) {
            Schema::table('payment_orders', function (Blueprint $table) {
                $table->unique('idempotency_key');
            });
        }
        
        $existingIndexes = collect(DB::select("SHOW INDEX FROM payment_orders"))->pluck('Key_name')->toArray();
        
        Schema::table('payment_orders', function (Blueprint $table) use ($existingIndexes) {
            if (!in_array('payment_orders_organization_id_index', $existingIndexes)) {
                $table->index('organization_id');
            }
            if (!in_array('payment_orders_stripe_session_id_index', $existingIndexes)) {
                $table->index('stripe_session_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $indexes = DB::select("SHOW INDEX FROM payment_orders WHERE Key_name = 'payment_orders_idempotency_key_unique'");
            if (!empty($indexes)) {
                $table->dropUnique(['idempotency_key']);
            }
            
            $existingIndexes = collect(DB::select("SHOW INDEX FROM payment_orders"))->pluck('Key_name')->toArray();
            
            if (in_array('payment_orders_organization_id_index', $existingIndexes)) {
                $table->dropIndex(['organization_id']);
            }
            if (in_array('payment_orders_stripe_session_id_index', $existingIndexes)) {
                $table->dropIndex(['stripe_session_id']);
            }
            
            if (Schema::hasColumn('payment_orders', 'organization_id')) {
                $table->dropColumn('organization_id');
            }
            if (Schema::hasColumn('payment_orders', 'stripe_session_id')) {
                $table->dropColumn('stripe_session_id');
            }
            if (Schema::hasColumn('payment_orders', 'stripe_invoice_id')) {
                $table->dropColumn('stripe_invoice_id');
            }
            if (Schema::hasColumn('payment_orders', 'error_message')) {
                $table->dropColumn('error_message');
            }
            if (Schema::hasColumn('payment_orders', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};
