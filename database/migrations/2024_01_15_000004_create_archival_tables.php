<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates archival tables that EXACTLY MIRROR the structure of hot tables
     * for long-term cold storage of FSMA 204 compliance data.
     * 
     * IMPORTANT: These tables must match the original table schemas 100%
     * plus additional archival metadata (original_id, archived_at)
     */
    public function up(): void
    {
        Schema::create('archival_cte_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id')->index()->comment('Original ID from cte_events table');
            
            // Mirror all columns from cte_events table
            $table->enum('event_type', ['receiving', 'transformation', 'shipping']);
            $table->unsignedBigInteger('trace_record_id');
            $table->timestamp('event_date');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->json('input_tlcs')->nullable()->comment('For transformation events');
            $table->string('reference_doc', 100)->nullable()->comment('PO, Invoice, BOL number');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            
            // Archival metadata
            $table->timestamp('archived_at')->index()->comment('When this record was moved to archival');
            $table->unsignedBigInteger('archived_by')->nullable()->comment('User who triggered archival');
            $table->timestamps(); // created_at, updated_at from original
            
            // Indexes matching original table
            $table->index('event_type');
            $table->index('event_date');
            $table->index('trace_record_id');
            $table->index(['original_id', 'event_type']);
        });

        Schema::create('archival_trace_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id')->index()->comment('Original ID from trace_records table');
            
            // Mirror all columns from trace_records table
            $table->string('tlc')->index();
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 10, 2);
            $table->decimal('available_quantity', 10, 2)->default(0);
            $table->decimal('consumed_quantity', 10, 2)->default(0);
            $table->string('unit', 20)->default('kg');
            $table->string('lot_code')->nullable();
            $table->date('harvest_date')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->enum('status', ['active', 'consumed', 'shipped', 'destroyed', 'voided'])->default('active');
            $table->text('path')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('materialized_path', 500)->nullable();
            
            // Archival metadata
            $table->timestamp('archived_at')->index()->comment('When this record was moved to archival');
            $table->unsignedBigInteger('archived_by')->nullable()->comment('User who triggered archival');
            $table->timestamps(); // created_at, updated_at from original
            
            // Indexes matching original table
            $table->index('lot_code');
            $table->index('status');
            $table->index('materialized_path');
            $table->index('organization_id');
            $table->index(['tlc', 'status']);
        });

        Schema::create('archival_trace_relationships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id')->index()->comment('Original ID from trace_relationships table');
            
            // Mirror all columns from trace_relationships table
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('child_id')->nullable();
            $table->enum('relationship_type', ['INPUT', 'OUTPUT', 'VOID', 'transformation', 'aggregation', 'disaggregation']);
            $table->unsignedBigInteger('cte_event_id')->nullable();
            
            // Archival metadata
            $table->timestamp('archived_at')->index()->comment('When this record was moved to archival');
            $table->unsignedBigInteger('archived_by')->nullable()->comment('User who triggered archival');
            $table->timestamps(); // created_at, updated_at from original
            
            // Indexes matching original table
            $table->index(['parent_id', 'child_id']);
            $table->index('relationship_type');
            $table->index('cte_event_id');
        });

        Schema::create('archival_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id')->index()->comment('Original ID from audit_logs table');
            
            // Common audit log structure
            $table->string('event_type', 50);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Archival metadata
            $table->timestamp('archived_at')->index()->comment('When this record was moved to archival');
            $table->unsignedBigInteger('archived_by')->nullable()->comment('User who triggered archival');
            $table->timestamps(); // created_at, updated_at from original
            
            // Indexes
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('event_type');
            $table->index('user_id');
        });
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
