<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing Key Data Elements (KDEs) required by FSMA 204
     * to the cte_events table for FDA compliance
     */
    public function up(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            
            // Product information KDEs
            $table->string('product_description', 255)->nullable()->after('reference_doc')
                ->comment('Product name/description for FDA reporting');
            
            // Quantity and unit KDEs
            $table->decimal('quantity_received', 12, 2)->nullable()->after('product_description')
                ->comment('Quantity received in receiving events');
            $table->string('quantity_unit', 50)->nullable()->after('quantity_received')
                ->comment('Unit of measure (kg, lbs, cases, etc.)');
            
            // Location KDEs
            $table->string('receiving_location_gln', 13)->nullable()->after('quantity_unit')
                ->comment('GLN of receiving location');
            $table->string('receiving_location_name', 255)->nullable()->after('receiving_location_gln')
                ->comment('Name of receiving location');
            
            $table->string('shipping_location_gln', 13)->nullable()->after('receiving_location_name')
                ->comment('GLN of shipping location');
            $table->string('shipping_location_name', 255)->nullable()->after('shipping_location_gln')
                ->comment('Name of shipping location');
            
            // Business information KDEs
            $table->string('business_name', 255)->nullable()->after('shipping_location_name')
                ->comment('Name of business performing the event');
            $table->string('business_gln', 13)->nullable()->after('business_name')
                ->comment('GLN of business performing the event');
            $table->string('business_address', 500)->nullable()->after('business_gln')
                ->comment('Address of business performing the event');
            
            // Traceability lot code KDE
            $table->string('traceability_lot_code', 100)->nullable()->after('business_address')
                ->comment('Traceability Lot Code (TLC) assigned to the product');
            
            // Transformation event specific KDEs
            $table->json('output_tlcs')->nullable()->after('traceability_lot_code')
                ->comment('Output TLCs for transformation events');
            $table->text('transformation_description')->nullable()->after('output_tlcs')
                ->comment('Description of transformation process');
            
            // Shipping event specific KDEs
            $table->string('shipping_reference_doc', 100)->nullable()->after('transformation_description')
                ->comment('Shipping document reference (BOL, AWB, etc.)');
            $table->timestamp('shipping_date')->nullable()->after('shipping_reference_doc')
                ->comment('Date of shipment');
            $table->string('receiving_date_expected', 50)->nullable()->after('shipping_date')
                ->comment('Expected receiving date');
            
            // FDA compliance tracking
            $table->boolean('fda_compliant')->default(true)->after('receiving_date_expected')
                ->comment('Whether event meets FSMA 204 requirements');
            $table->text('fda_compliance_notes')->nullable()->after('fda_compliant')
                ->comment('Notes on FDA compliance status');
            
            // Add indexes for FDA export queries
            $table->index('traceability_lot_code');
            $table->index('business_gln');
            $table->index('fda_compliant');
        });
    }

    public function down(): void
    {
        Schema::table('cte_events', function (Blueprint $table) {
            $table->dropIndex(['traceability_lot_code']);
            $table->dropIndex(['business_gln']);
            $table->dropIndex(['fda_compliant']);
            
            $table->dropColumn([
                'product_description',
                'quantity_received',
                'quantity_unit',
                'receiving_location_gln',
                'receiving_location_name',
                'shipping_location_gln',
                'shipping_location_name',
                'business_name',
                'business_gln',
                'business_address',
                'traceability_lot_code',
                'output_tlcs',
                'transformation_description',
                'shipping_reference_doc',
                'shipping_date',
                'receiving_date_expected',
                'fda_compliant',
                'fda_compliance_notes',
            ]);
        });
    }
};
