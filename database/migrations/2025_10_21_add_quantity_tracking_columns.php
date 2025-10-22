<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing columns to cte_events table for quantity tracking
        if (Schema::hasTable('cte_events')) {
            Schema::table('cte_events', function (Blueprint $table) {
                if (!Schema::hasColumn('cte_events', 'output_quantity')) {
                    $table->decimal('output_quantity', 12, 2)->nullable()->after('quantity_received');
                }

                if (!Schema::hasColumn('cte_events', 'harvest_date')) {
                    $table->datetime('harvest_date')->nullable()->after('event_date');
                }

                if (!Schema::hasColumn('cte_events', 'pack_date')) {
                    $table->datetime('pack_date')->nullable()->after('harvest_date');
                }

                if (!Schema::hasColumn('cte_events', 'signature_hash')) {
                    $table->string('signature_hash')->nullable()->after('fda_compliance_notes');
                }

                if (!Schema::hasColumn('cte_events', 'organization_id')) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('created_by');
                    $table->index('organization_id');
                }
            });
        }

        // Create audit_logs table if it doesn't exist
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('action');
                $table->string('table_name');
                $table->unsignedBigInteger('record_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['table_name', 'record_id']);
                $table->index('user_id');
                $table->index('created_at');
            });
        }

        // Create signature_performance_metrics table if it doesn't exist
        if (!Schema::hasTable('signature_performance_metrics')) {
            Schema::create('signature_performance_metrics', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('signature_id');
                $table->string('metric_name');
                $table->decimal('metric_value', 12, 4);
                $table->string('unit')->nullable();
                $table->timestamps();

                $table->foreign('signature_id')->references('id')->on('e_signatures')->onDelete('cascade');
                $table->index(['signature_id', 'metric_name']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cte_events')) {
            Schema::table('cte_events', function (Blueprint $table) {
                $columns = [
                    'output_quantity',
                    'harvest_date',
                    'pack_date',
                    'signature_hash',
                    'organization_id',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('cte_events', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('signature_performance_metrics');
    }
};
