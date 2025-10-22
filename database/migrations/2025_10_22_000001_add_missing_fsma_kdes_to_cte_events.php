<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FSMA 204 Compliance: Add 4 missing KDEs to cte_events table
     * 
     * Missing KDEs being added:
     * 1. product_lot_code (KDE #12) - Original lot code from supplier
     * 2. harvest_location_gln (KDE #8) - Harvest location GLN
     * 3. harvest_location_name (KDE #8) - Harvest location name
     * 4. cooling_date (KDE #14) - Cooling date for fresh produce
     * 5. reference_doc_type (KDE #17) - Type of reference document
     */
    public function up(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            // KDE #12: Product Lot Code (original from supplier, different from TLC)
            $table->string('product_lot_code', 100)
                ->nullable()
                ->after('traceability_lot_code')
                ->comment('Original product lot code from supplier (KDE #12)');
            
            // KDE #8: Harvest Location (specific farm/field location)
            $table->string('harvest_location_gln', 13)
                ->nullable()
                ->after('receiving_location_name')
                ->comment('Harvest location GLN - 13 digits (KDE #8)');
            
            $table->string('harvest_location_name', 255)
                ->nullable()
                ->after('harvest_location_gln')
                ->comment('Harvest location name (farm/field) (KDE #8)');
            
            // KDE #14: Cooling Date (for fresh produce requiring cooling)
            $table->timestamp('cooling_date')
                ->nullable()
                ->after('pack_date')
                ->comment('Initial cooling date for fresh produce (KDE #14)');
            
            // KDE #17: Reference Document Type
            $table->enum('reference_doc_type', ['PO', 'Invoice', 'BOL', 'AWB', 'Other'])
                ->nullable()
                ->after('reference_doc')
                ->comment('Type of reference document (KDE #17)');
            
            // Add indexes for performance
            $table->index('product_lot_code');
            $table->index('harvest_location_gln');
            $table->index('cooling_date');
        });
    }

    public function down(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            $table->dropIndex(['product_lot_code']);
            $table->dropIndex(['harvest_location_gln']);
            $table->dropIndex(['cooling_date']);
            
            $table->dropColumn([
                'product_lot_code',
                'harvest_location_gln',
                'harvest_location_name',
                'cooling_date',
                'reference_doc_type',
            ]);
        });
    }
};
