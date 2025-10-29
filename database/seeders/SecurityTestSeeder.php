<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\Product;
use App\Models\TraceRecord;

/**
 * SecurityTestSeeder
 * 
 * Creates test data for organization isolation testing.
 * Creates multiple organizations with users and resources to test multi-tenant isolation.
 */
class SecurityTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Organization 1
        $org1 = Organization::create([
            'name' => 'Organization Alpha',
            'slug' => 'org-alpha',
            'email' => 'admin@org-alpha.com',
            'is_active' => true,
        ]);

        // Create Organization 2
        $org2 = Organization::create([
            'name' => 'Organization Beta',
            'slug' => 'org-beta',
            'email' => 'admin@org-beta.com',
            'is_active' => true,
        ]);

        // Create users for Organization 1
        $user1_org1 = User::create([
            'username' => 'user1_org1',
            'email' => 'user1@org-alpha.com',
            'password' => bcrypt('password123'),
            'full_name' => 'User One Org Alpha',
            'organization_id' => $org1->id,
            'role' => 'admin',
            'is_active' => true,
            'package_id' => 'premium',
        ]);

        $user2_org1 = User::create([
            'username' => 'user2_org1',
            'email' => 'user2@org-alpha.com',
            'password' => bcrypt('password123'),
            'full_name' => 'User Two Org Alpha',
            'organization_id' => $org1->id,
            'role' => 'user',
            'is_active' => true,
            'package_id' => 'basic',
        ]);

        // Create users for Organization 2
        $user1_org2 = User::create([
            'username' => 'user1_org2',
            'email' => 'user1@org-beta.com',
            'password' => bcrypt('password123'),
            'full_name' => 'User One Org Beta',
            'organization_id' => $org2->id,
            'role' => 'admin',
            'is_active' => true,
            'package_id' => 'premium',
        ]);

        $user2_org2 = User::create([
            'username' => 'user2_org2',
            'email' => 'user2@org-beta.com',
            'password' => bcrypt('password123'),
            'full_name' => 'User Two Org Beta',
            'organization_id' => $org2->id,
            'role' => 'user',
            'is_active' => true,
            'package_id' => 'basic',
        ]);

        // Create products for Organization 1
        Product::create([
            'name' => 'Product Alpha 1',
            'sku' => 'PROD-ALPHA-001',
            'organization_id' => $org1->id,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Product Alpha 2',
            'sku' => 'PROD-ALPHA-002',
            'organization_id' => $org1->id,
            'is_active' => true,
        ]);

        // Create products for Organization 2
        Product::create([
            'name' => 'Product Beta 1',
            'sku' => 'PROD-BETA-001',
            'organization_id' => $org2->id,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Product Beta 2',
            'sku' => 'PROD-BETA-002',
            'organization_id' => $org2->id,
            'is_active' => true,
        ]);

        $this->command->info('Security test data seeded successfully!');
        $this->command->info('Organization 1 (Alpha): ' . $org1->id);
        $this->command->info('Organization 2 (Beta): ' . $org2->id);
    }
}
