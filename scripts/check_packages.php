<?php
// Script to check packages status in database
// Run: php scripts/check_packages.php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n=== PACKAGES DIAGNOSTIC ===\n\n";

// 1. Check total packages
$totalPackages = DB::table('packages')->count();
echo "1. Total packages in database: $totalPackages\n";

// 2. Check packages with is_visible = true
$visiblePackages = DB::table('packages')->where('is_visible', true)->count();
echo "2. Packages with is_visible = true: $visiblePackages\n";

// 3. Check packages organization_id values
$packageOrgIds = DB::table('packages')->select('id', 'name', 'organization_id', 'is_visible')->get();
echo "\n3. Package organization_id values:\n";
foreach ($packageOrgIds as $pkg) {
    $orgId = $pkg->organization_id ?? 'NULL';
    $visible = $pkg->is_visible ? 'YES' : 'NO';
    echo "   - {$pkg->id} ({$pkg->name}): organization_id=$orgId, is_visible=$visible\n";
}

// 4. Check if migration 2025_10_28_000003 was run
$migrations = DB::table('migrations')
    ->where('migration', 'like', '%2025_10_28_000003%')
    ->get();
echo "\n4. Migration 2025_10_28_000003 status:\n";
if ($migrations->count() > 0) {
    echo "   ✓ Migration HAS BEEN RUN\n";
} else {
    echo "   ✗ Migration HAS NOT BEEN RUN - This is the problem!\n";
}

// 5. Check all migrations
$allMigrations = DB::table('migrations')->orderBy('batch')->get();
echo "\n5. All migrations (last 10):\n";
$allMigrations->slice(-10)->each(function ($m) {
    echo "   - {$m->migration} (batch: {$m->batch})\n";
});

echo "\n=== END DIAGNOSTIC ===\n\n";
