<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing packages
        Package::truncate();

        // Free Plan
        Package::create([
            'id' => 'free',
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Perfect for getting started',
            'max_cte_records_monthly' => 100,
            'max_documents' => 10,
            'max_users' => 1,
            'monthly_list_price' => 0,
            'monthly_selling_price' => 0,
            'yearly_list_price' => 0,
            'yearly_selling_price' => 0,
            'currency' => 'USD',
            'show_promotion' => false,
            'promotion_text' => null,
            'features' => [
                'Basic Traceability',
                'Document Management',
                'Email Support',
            ],
            'is_popular' => false,
            'is_highlighted' => false,
            'is_visible' => true,
            'is_selectable' => true,
            'sort_order' => 1,
            'has_traceability' => true,
            'has_document_management' => true,
            'has_e_signatures' => false,
            'has_certificates' => false,
            'has_data_retention' => false,
            'has_archival' => false,
            'has_compliance_report' => false,
            'support_level' => 'email',
        ]);

        // Starter Plan
        Package::create([
            'id' => 'starter',
            'name' => 'Starter',
            'slug' => 'starter',
            'description' => 'For small businesses',
            'max_cte_records_monthly' => 1000,
            'max_documents' => 100,
            'max_users' => 5,
            'monthly_list_price' => 99,
            'monthly_selling_price' => 79,
            'yearly_list_price' => 1188,
            'yearly_selling_price' => 790,
            'currency' => 'USD',
            'show_promotion' => false,
            'promotion_text' => null,
            'features' => [
                'Full Traceability',
                'Document Management',
                'Up to 5 Users',
                'Email Support',
                'Monthly Reports',
            ],
            'is_popular' => false,
            'is_highlighted' => false,
            'is_visible' => true,
            'is_selectable' => true,
            'sort_order' => 2,
            'has_traceability' => true,
            'has_document_management' => true,
            'has_e_signatures' => false,
            'has_certificates' => false,
            'has_data_retention' => true,
            'has_archival' => false,
            'has_compliance_report' => false,
            'support_level' => 'email',
        ]);

        // Professional Plan
        Package::create([
            'id' => 'professional',
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'For growing organizations',
            'max_cte_records_monthly' => 10000,
            'max_documents' => 1000,
            'max_users' => 20,
            'monthly_list_price' => 299,
            'monthly_selling_price' => 249,
            'yearly_list_price' => 3588,
            'yearly_selling_price' => 2490,
            'currency' => 'USD',
            'show_promotion' => true,
            'promotion_text' => 'Save 30% with yearly billing',
            'features' => [
                'Advanced Traceability',
                'Document Management',
                'Up to 20 Users',
                'Digital Signatures',
                'Priority Email Support',
                'Advanced Reports',
                'Data Retention',
                'API Access',
            ],
            'is_popular' => true,
            'is_highlighted' => true,
            'is_visible' => true,
            'is_selectable' => true,
            'sort_order' => 3,
            'has_traceability' => true,
            'has_document_management' => true,
            'has_e_signatures' => true,
            'has_certificates' => true,
            'has_data_retention' => true,
            'has_archival' => false,
            'has_compliance_report' => true,
            'support_level' => 'priority',
        ]);

        // Enterprise Plan
        Package::create([
            'id' => 'enterprise',
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'description' => 'For large organizations',
            'max_cte_records_monthly' => 0, // Unlimited
            'max_documents' => 0, // Unlimited
            'max_users' => 0, // Unlimited
            'monthly_list_price' => 0,
            'monthly_selling_price' => 0,
            'yearly_list_price' => 0,
            'yearly_selling_price' => 0,
            'currency' => 'USD',
            'show_promotion' => false,
            'promotion_text' => null,
            'features' => [
                'Unlimited Traceability',
                'Unlimited Documents',
                'Unlimited Users',
                'Digital Signatures',
                'Certificates',
                'Data Retention & Archival',
                'Compliance Reports',
                'API Access',
                '24/7 Phone Support',
                'Dedicated Account Manager',
                'Custom Integrations',
            ],
            'is_popular' => false,
            'is_highlighted' => false,
            'is_visible' => true,
            'is_selectable' => false,
            'sort_order' => 4,
            'has_traceability' => true,
            'has_document_management' => true,
            'has_e_signatures' => true,
            'has_certificates' => true,
            'has_data_retention' => true,
            'has_archival' => true,
            'has_compliance_report' => true,
            'support_level' => '24/7',
        ]);
    }
}
