<?php

/**
 * Script to run payment security migrations
 * Run this from the project root: php scripts/run-payment-migrations.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\Artisan;

echo "========================================\n";
echo "Running Payment Security Migrations\n";
echo "========================================\n\n";

try {
    // Run migrations
    echo "1. Creating payment_orders table...\n";
    Artisan::call('migrate', ['--path' => 'database/migrations/2025_10_20_000001_create_payment_orders_table.php']);
    echo "   ✓ payment_orders table created\n\n";
    
    echo "2. Creating webhook_logs table...\n";
    Artisan::call('migrate', ['--path' => 'database/migrations/2025_10_20_000002_create_webhook_logs_table.php']);
    echo "   ✓ webhook_logs table created\n\n";
    
    echo "3. Adding payment fields to users table...\n";
    Artisan::call('migrate', ['--path' => 'database/migrations/2025_10_20_000003_add_payment_fields_to_users_table.php']);
    echo "   ✓ Payment fields added to users table\n\n";
    
    echo "========================================\n";
    echo "✓ All migrations completed successfully!\n";
    echo "========================================\n\n";
    
    echo "Next steps:\n";
    echo "1. Update your .env file with VNPAY and STRIPE credentials\n";
    echo "2. Test the payment flow in your application\n";
    echo "3. Monitor logs for any payment-related errors\n";
    
} catch (\Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
