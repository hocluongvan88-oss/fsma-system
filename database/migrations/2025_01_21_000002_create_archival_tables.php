<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create archival tables for cold storage (database strategy)
     * These tables mirror the structure of hot tables but are optimized for long-term storage
     */
    public function up(): void
    {
        // Archival CTE Events
        if (!Schema::hasTable('archival_cte_events')) {
            Schema::create('archival_cte_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_id')->index(); // Original ID from hot table
                $table->timestamp('archived_at')->index();
                $table->unsignedBigInteger('archived_by')->nullable();
                
                // All original columns from cte_events table (matching 2024_01_01_000007_create_cte_events_table.php)
                $table->enum('event_type', ['receiving', 'transformation', 'shipping']);
                $table->unsignedBigInteger('trace_record_id')->nullable()->index();
                $table->unsignedBigInteger('location_id')->nullable()->index();
                $table->unsignedBigInteger('partner_id')->nullable()->index();
                
                // RECEIVING event fields
                $table->decimal('quantity_received', 15, 3)->nullable();
                $table->string('unit_of_measure', 50)->nullable();
                $table->string('lot_code', 100)->nullable();
                $table->date('harvest_date')->nullable();
                $table->date('pack_date')->nullable();
                $table->string('supplier_name', 255)->nullable();
                $table->string('supplier_location', 255)->nullable();
                
                // TRANSFORMATION event fields
                $table->text('input_tlcs')->nullable(); // JSON array of input TLCs
                $table->string('output_tlc', 100)->nullable();
                $table->text('transformation_description')->nullable();
                $table->decimal('yield_percentage', 5, 2)->nullable();
                
                // SHIPPING event fields
                $table->decimal('quantity_shipped', 15, 3)->nullable();
                $table->string('destination_name', 255)->nullable();
                $table->string('destination_location', 255)->nullable();
                $table->date('ship_date')->nullable();
                $table->string('carrier', 100)->nullable();
                $table->string('tracking_number', 100)->nullable();
                
                // Common fields
                $table->string('reference_doc', 255)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                
                // Indexes for archival queries
                $table->index('event_type');
                $table->index('created_at');
            });
        }

        // Archival Trace Records
        if (!Schema::hasTable('archival_trace_records')) {
            Schema::create('archival_trace_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_id')->index();
                $table->timestamp('archived_at')->index();
                $table->unsignedBigInteger('archived_by')->nullable();
                
                // All original columns from trace_records table
                $table->string('tlc', 100)->unique();
                $table->unsignedBigInteger('product_id')->index();
                $table->decimal('quantity', 15, 3);
                $table->decimal('available_quantity', 15, 3)->default(0);
                $table->decimal('consumed_quantity', 15, 3)->default(0);
                $table->string('unit', 50);
                $table->string('lot_code', 100)->nullable();
                $table->date('harvest_date')->nullable();
                $table->unsignedBigInteger('location_id')->nullable()->index();
                $table->enum('status', ['active', 'consumed', 'shipped', 'destroyed', 'voided'])->default('active')->index();
                $table->string('path', 500)->nullable();
                $table->unsignedBigInteger('organization_id')->nullable()->index();
                $table->string('materialized_path', 1000)->nullable()->index();
                $table->timestamps();
                
                $table->index('lot_code');
                $table->index('status');
            });
        }

        // Archival Trace Relationships
        if (!Schema::hasTable('archival_trace_relationships')) {
            Schema::create('archival_trace_relationships', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_id')->index();
                $table->timestamp('archived_at')->index();
                $table->unsignedBigInteger('archived_by')->nullable();
                
                // All original columns from trace_relationships table
                $table->unsignedBigInteger('parent_id')->index();
                $table->unsignedBigInteger('child_id')->nullable()->index();
                $table->enum('relationship_type', [
                    'INPUT',
                    'OUTPUT',
                    'VOID',
                    'transformation',
                    'aggregation',
                    'disaggregation'
                ]);
                $table->unsignedBigInteger('cte_event_id')->nullable()->index();
                $table->timestamps();
                
                $table->index(['parent_id', 'child_id']);
                $table->index('relationship_type');
            });
        }

        // Archival Audit Logs
        if (!Schema::hasTable('archival_audit_logs')) {
            Schema::create('archival_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('original_id')->index();
                $table->timestamp('archived_at')->index();
                $table->unsignedBigInteger('archived_by')->nullable();
                
                // All original columns from audit_logs table
                $table->string('auditable_type');
                $table->unsignedBigInteger('auditable_id');
                $table->string('event');
                $table->text('old_values')->nullable();
                $table->text('new_values')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent')->nullable();
                $table->timestamps();
                
                $table->index(['auditable_type', 'auditable_id']);
                $table->index('event');
                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archival_audit_logs');
        Schema::dropIfExists('archival_trace_relationships');
        Schema::dropIfExists('archival_trace_records');
        Schema::dropIfExists('archival_cte_events');
    }
};
