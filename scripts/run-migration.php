<?php
/**
 * Migration Runner Script
 * Run this script to execute the pending migration that adds organization_id to users table
 * 
 * Usage: php scripts/run-migration.php
 */

// Set up Laravel environment
require __DIR__ . '/../laravel/bootstrap/app.php';

$app = require_once __DIR__ . '/../laravel/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

// Run the migration
$status = $kernel->call('migrate', [
    '--path' => 'database/migrations/2024_01_01_000019_add_organization_id_to_users_table.php',
    '--force' => true,
]);

if ($status === 0) {
    echo "✓ Migration executed successfully!\n";
    echo "The organization_id column has been added to the users table.\n";
} else {
    echo "✗ Migration failed. Please check the error above.\n";
}

exit($status);
