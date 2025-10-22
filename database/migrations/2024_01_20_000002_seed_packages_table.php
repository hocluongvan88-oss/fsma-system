<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('packages')->insert([
            [
                'id' => 'free',
                'name' => 'Free Tier',
                'description' => 'Perfect for getting started with FSMA 204 compliance',
                'max_cte_records_monthly' => 50,
                'max_documents' => 1,
                'max_users' => 1,
                'monthly_list_price' => null,
                'monthly_selling_price' => 0,
                'yearly_list_price' => null,
                'yearly_selling_price' => 0,
                'show_promotion' => false,
                'promotion_text' => null,
                'features' => json_encode([
                    '50 CTE records/month (permanent)',
                    '1 document',
                    '1 user',
                    'Basic traceability',
                    'Community support'
                ]),
                'is_visible' => true,
                'is_selectable' => false, // Cannot be selected, default for new users
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'basic',
                'name' => 'Basic',
                'description' => 'For small businesses starting their compliance journey',
                'max_cte_records_monthly' => 500,
                'max_documents' => 10,
                'max_users' => 1,
                'monthly_list_price' => 59.00,
                'monthly_selling_price' => 49.00,
                'yearly_list_price' => 588.00,
                'yearly_selling_price' => 470.00,
                'show_promotion' => true,
                'promotion_text' => 'Save 20% on annual billing',
                'features' => json_encode([
                    '500 CTE records/month',
                    '10 documents',
                    '1 user',
                    'Full traceability',
                    'Export reports',
                    'Email support (48h)'
                ]),
                'is_visible' => true,
                'is_selectable' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'premium',
                'name' => 'Premium',
                'description' => 'For growing businesses with advanced needs',
                'max_cte_records_monthly' => 2500,
                'max_documents' => 999999, // Unlimited
                'max_users' => 3,
                'monthly_list_price' => 249.00,
                'monthly_selling_price' => 199.00,
                'yearly_list_price' => 2388.00,
                'yearly_selling_price' => 1910.00,
                'show_promotion' => true,
                'promotion_text' => 'Save 20% on annual billing',
                'features' => json_encode([
                    '2,500 CTE records/month',
                    'Unlimited documents',
                    '3 users',
                    'Advanced reports',
                    'API access',
                    'Priority support (24h)',
                    'Phone support'
                ]),
                'is_visible' => true,
                'is_selectable' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'For large organizations with custom requirements',
                'max_cte_records_monthly' => 5000,
                'max_documents' => 999999, // Unlimited
                'max_users' => 5,
                'monthly_list_price' => null, // Custom pricing
                'monthly_selling_price' => 499.00,
                'yearly_list_price' => null,
                'yearly_selling_price' => 4990.00,
                'show_promotion' => false,
                'promotion_text' => 'Custom pricing available',
                'features' => json_encode([
                    'Starting from 5,000 CTE records/month',
                    'Unlimited documents',
                    '5+ users',
                    'E-Signatures',
                    'Compliance reports',
                    'Custom integrations',
                    'Dedicated account manager',
                    '24/7 support',
                    'SLA 99.9% uptime'
                ]),
                'is_visible' => true,
                'is_selectable' => false, // Contact sales
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('packages')->whereIn('id', ['free', 'basic', 'premium', 'enterprise'])->delete();
    }
};
