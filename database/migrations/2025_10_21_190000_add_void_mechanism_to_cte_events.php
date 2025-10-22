<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            // Change event_type enum to include VOID and ADJUSTMENT
            DB::statement("ALTER TABLE cte_events MODIFY COLUMN event_type ENUM('receiving', 'transformation', 'shipping', 'VOID', 'ADJUSTMENT') NOT NULL");
            
            // Add status column to track VOIDED events
            $table->enum('status', ['active', 'voided'])->default('active')->after('event_type');
            
            // Add voided_by and voided_at for audit trail
            $table->foreignId('voided_by')->nullable()->constrained('users')->onDelete('set null')->after('created_by');
            $table->timestamp('voided_at')->nullable()->after('voided_by');
            
            // Add reference to original event for VOID events
            $table->foreignId('voids_event_id')->nullable()->constrained('cte_events')->onDelete('cascade')->after('voided_at')
                ->comment('For VOID events: references the original event being voided');
            
            // Add index for status queries
            $table->index('status');
            $table->index('voids_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropForeign(['voids_event_id']);
            $table->dropColumn(['status', 'voided_by', 'voided_at', 'voids_event_id']);
            
            DB::statement("ALTER TABLE cte_events MODIFY COLUMN event_type ENUM('receiving', 'transformation', 'shipping') NOT NULL");
        });
    }
};
