<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trace_records', function (Blueprint $table) {
            if (!Schema::hasColumn('trace_records', 'available_quantity')) {
                $table->decimal('available_quantity', 10, 2)->default(0)->after('quantity');
            }
            
            if (!Schema::hasColumn('trace_records', 'consumed_quantity')) {
                $table->decimal('consumed_quantity', 10, 2)->default(0)->after('available_quantity');
            }
            
            if (!Schema::hasColumn('trace_records', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
                $table->index('organization_id');
            }
        });

        if (Schema::hasColumn('trace_records', 'available_quantity')) {
            // Set available_quantity = quantity for active records
            DB::statement('
                UPDATE trace_records 
                SET available_quantity = COALESCE(available_quantity, quantity),
                    consumed_quantity = COALESCE(consumed_quantity, 0)
                WHERE status = "active" AND available_quantity = 0
            ');
            
            // Set consumed_quantity = quantity for consumed records
            DB::statement('
                UPDATE trace_records 
                SET available_quantity = COALESCE(available_quantity, 0),
                    consumed_quantity = COALESCE(consumed_quantity, quantity)
                WHERE status IN ("consumed", "shipped", "destroyed") AND consumed_quantity = 0
            ');
        }
    }

    public function down(): void
    {
        Schema::table('trace_records', function (Blueprint $table) {
            if (Schema::hasColumn('trace_records', 'organization_id')) {
                $table->dropForeign(['organization_id']);
            }
            
            $columnsToDrop = [];
            if (Schema::hasColumn('trace_records', 'available_quantity')) {
                $columnsToDrop[] = 'available_quantity';
            }
            if (Schema::hasColumn('trace_records', 'consumed_quantity')) {
                $columnsToDrop[] = 'consumed_quantity';
            }
            if (Schema::hasColumn('trace_records', 'organization_id')) {
                $columnsToDrop[] = 'organization_id';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
