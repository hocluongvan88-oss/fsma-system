<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;
use App\Models\Location;
use App\Models\Partner;
use App\Models\Organization;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement("SET GLOBAL sql_mode = ''");
        DB::statement("SET SESSION sql_mode = ''");
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Xóa dữ liệu cũ theo thứ tự (từ bảng con đến bảng cha)
        DB::table('e_signatures')->truncate();
        DB::table('audit_logs')->truncate();
        DB::table('cte_events')->truncate();
        DB::table('trace_relationships')->truncate();
        DB::table('trace_records')->truncate();
        DB::table('partners')->truncate();
        DB::table('locations')->truncate();
        DB::table('products')->truncate();
        DB::table('users')->truncate();
        DB::table('organizations')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Tạo Tổ chức Mặc định (Organization ID 1) - cho user Admin
        $defaultOrganization = Organization::create([
            'id' => 1,
            'name' => 'Default Organization',
            'description' => 'System default organization for administration and super users.',
            'is_active' => true,
        ]);

        // Tạo Tổ chức Mẫu (Organization ID 2) - VEXIM Global
        $demoOrganization = Organization::create([
            'id' => 2,
            'name' => 'VEXIM Global (Demo)',
            'description' => 'Tổ chức mẫu cho mục đích demo FSMA 204. VEXIM Global',
            'is_active' => true,
        ]);

        $demoOrganizationId = $demoOrganization->id;
        $timestamp = now();

        // Create Users
        // SỬA LỖI: Loại bỏ package_id và các cột quota cũ khỏi User Seeder
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@fsma204.com',
                'password' => Hash::make('admin123'),
                'full_name' => 'System Administrator',
                'role' => 'admin',
                // CÁC CỘT BỊ XÓA (package_id, max_cte_records_monthly, max_documents, max_users) ĐÃ ĐƯỢC LOẠI BỎ
                'is_active' => true,
                'organization_id' => 1,
            ]
        );

        User::updateOrCreate(
            ['username' => 'manager'],
            [
                'email' => 'manager@fsma204.com',
                'password' => Hash::make('manager123'),
                'full_name' => 'Warehouse Manager',
                'role' => 'manager',
                // CÁC CỘT BỊ XÓA ĐÃ ĐƯỢC LOẠI BỎ
                'is_active' => true,
                'organization_id' => $demoOrganizationId,
            ]
        );

        User::updateOrCreate(
            ['username' => 'operator'],
            [
                'email' => 'operator@fsma204.com',
                'password' => Hash::make('operator123'),
                'full_name' => 'Floor Operator',
                'role' => 'operator',
                // CÁC CỘT BỊ XÓA ĐÃ ĐƯỢC LOẠI BỎ
                'is_active' => true,
                'organization_id' => $demoOrganizationId,
            ]
        );

        // Sample Products
        DB::table('products')->insert([
            [
                'organization_id' => $demoOrganizationId,
                'sku' => 'TOM-001',
                'product_name' => 'Fresh Tomatoes',
                'description' => 'Organic Roma Tomatoes',
                'is_ftl' => true,
                'category' => 'Vegetables',
                'unit_of_measure' => 'kg',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'organization_id' => $demoOrganizationId,
                'sku' => 'LET-001',
                'product_name' => 'Romaine Lettuce',
                'description' => 'Fresh Romaine Lettuce',
                'is_ftl' => true,
                'category' => 'Leafy Greens',
                'unit_of_measure' => 'kg',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'organization_id' => $demoOrganizationId,
                'sku' => 'STR-001',
                'product_name' => 'Fresh Strawberries',
                'description' => 'Organic Strawberries',
                'is_ftl' => true,
                'category' => 'Berries',
                'unit_of_measure' => 'kg',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);

        // Sample Locations
        DB::table('locations')->insert([
            [
                'organization_id' => $demoOrganizationId,
                'location_name' => 'Main Warehouse',
                'gln' => '0614141000001',
                'address' => '123 Industrial Blvd',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip_code' => '90001',
                'country' => 'USA',
                'location_type' => 'warehouse',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'organization_id' => $demoOrganizationId,
                'location_name' => 'Processing Facility',
                'gln' => '0614141000002',
                'address' => '456 Factory Road',
                'city' => 'Fresno',
                'state' => 'CA',
                'zip_code' => '93650',
                'country' => 'USA',
                'location_type' => 'processing',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'organization_id' => $demoOrganizationId,
                'location_name' => 'Green Valley Farm',
                'ffrn' => '3012345678',
                'address' => '789 Farm Lane',
                'city' => 'Salinas',
                'state' => 'CA',
                'zip_code' => '93901',
                'country' => 'USA',
                'location_type' => 'farm',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);

        // Sample Partners
        DB::table('partners')->insert([
            [
                'organization_id' => $demoOrganizationId,
                'partner_name' => 'Fresh Farms Co.',
                'partner_type' => 'supplier',
                'contact_person' => 'John Smith',
                'email' => 'john@freshfarms.com',
                'phone' => '555-0101',
                'address' => '100 Farm Road, Salinas, CA 93901',
                'gln' => '0614141000010',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'organization_id' => $demoOrganizationId,
                'partner_name' => 'Retail Supermarket Chain',
                'partner_type' => 'customer',
                'contact_person' => 'Jane Doe',
                'email' => 'jane@retailchain.com',
                'phone' => '555-0202',
                'address' => '200 Market St, San Francisco, CA 94102',
                'gln' => '0614141000020',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'organization_id' => $demoOrganizationId,
                'partner_name' => 'Food Distributor Inc.',
                'partner_type' => 'both',
                'contact_person' => 'Bob Johnson',
                'email' => 'bob@fooddist.com',
                'phone' => '555-0303',
                'address' => '300 Distribution Way, Oakland, CA 94601',
                'gln' => '0614141000030',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }
}
