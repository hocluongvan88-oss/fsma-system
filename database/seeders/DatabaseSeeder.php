<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Product;
use App\Models\Location;
use App\Models\Partner;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user with Enterprise package
        User::create([
            'username' => 'admin',
            'email' => 'admin@fsma204.com',
            'password' => Hash::make('admin123'),
            'full_name' => 'System Administrator',
            'role' => 'admin',
            'package_id' => 'enterprise',
            'max_cte_records_monthly' => 99999999,
            'max_documents' => 999999,
            'max_users' => 999999,
            'is_active' => true,
            'organization_id' => 1, // System admin organization
        ]);

        // Create manager user with Premium package
        User::create([
            'username' => 'manager',
            'email' => 'manager@fsma204.com',
            'password' => Hash::make('manager123'),
            'full_name' => 'Warehouse Manager',
            'role' => 'manager',
            'package_id' => 'premium',
            'max_cte_records_monthly' => 2500,
            'max_documents' => 999999,
            'max_users' => 3,
            'is_active' => true,
            'organization_id' => 2, // Demo organization
        ]);

        // Create operator user with Basic package
        User::create([
            'username' => 'operator',
            'email' => 'operator@fsma204.com',
            'password' => Hash::make('operator123'),
            'full_name' => 'Floor Operator',
            'role' => 'operator',
            'package_id' => 'basic',
            'max_cte_records_monthly' => 500,
            'max_documents' => 10,
            'max_users' => 1,
            'is_active' => true,
            'organization_id' => 2, // Same demo organization as manager
        ]);

        // Sample Products (FTL items)
        Product::create([
            'sku' => 'TOM-001',
            'product_name' => 'Fresh Tomatoes',
            'description' => 'Organic Roma Tomatoes',
            'is_ftl' => true,
            'category' => 'Vegetables',
            'unit_of_measure' => 'kg',
        ]);

        Product::create([
            'sku' => 'LET-001',
            'product_name' => 'Romaine Lettuce',
            'description' => 'Fresh Romaine Lettuce',
            'is_ftl' => true,
            'category' => 'Leafy Greens',
            'unit_of_measure' => 'kg',
        ]);

        Product::create([
            'sku' => 'STR-001',
            'product_name' => 'Fresh Strawberries',
            'description' => 'Organic Strawberries',
            'is_ftl' => true,
            'category' => 'Berries',
            'unit_of_measure' => 'kg',
        ]);

        // Sample Locations
        Location::create([
            'location_name' => 'Main Warehouse',
            'gln' => '0614141000001',
            'address' => '123 Industrial Blvd',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip_code' => '90001',
            'country' => 'USA',
            'location_type' => 'warehouse',
        ]);

        Location::create([
            'location_name' => 'Processing Facility',
            'gln' => '0614141000002',
            'address' => '456 Factory Road',
            'city' => 'Fresno',
            'state' => 'CA',
            'zip_code' => '93650',
            'country' => 'USA',
            'location_type' => 'processing',
        ]);

        Location::create([
            'location_name' => 'Green Valley Farm',
            'ffrn' => '3012345678',
            'address' => '789 Farm Lane',
            'city' => 'Salinas',
            'state' => 'CA',
            'zip_code' => '93901',
            'country' => 'USA',
            'location_type' => 'farm',
        ]);

        // Sample Partners
        Partner::create([
            'partner_name' => 'Fresh Farms Co.',
            'partner_type' => 'supplier',
            'contact_person' => 'John Smith',
            'email' => 'john@freshfarms.com',
            'phone' => '555-0101',
            'address' => '100 Farm Road, Salinas, CA 93901',
            'gln' => '0614141000010',
        ]);

        Partner::create([
            'partner_name' => 'Retail Supermarket Chain',
            'partner_type' => 'customer',
            'contact_person' => 'Jane Doe',
            'email' => 'jane@retailchain.com',
            'phone' => '555-0202',
            'address' => '200 Market St, San Francisco, CA 94102',
            'gln' => '0614141000020',
        ]);

        Partner::create([
            'partner_name' => 'Food Distributor Inc.',
            'partner_type' => 'both',
            'contact_person' => 'Bob Johnson',
            'email' => 'bob@fooddist.com',
            'phone' => '555-0303',
            'address' => '300 Distribution Way, Oakland, CA 94601',
            'gln' => '0614141000030',
        ]);
    }
}
