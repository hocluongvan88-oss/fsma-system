<?php

/**
 * Script untuk memperbaiki error migration organization_id
 * 
 * Langkah-langkah:
 * 1. Rollback migration yang gagal
 * 2. Hapus file migration yang bermasalah
 * 3. Jalankan migration baru yang aman
 */

echo "=== Memperbaiki Error Migration ===\n\n";

// Step 1: Rollback migration yang gagal
echo "Step 1: Rollback migration yang gagal...\n";
echo "Jalankan: php artisan migrate:rollback --step=1\n\n";

// Step 2: Hapus file migration yang bermasalah
echo "Step 2: Hapus file migration yang bermasalah...\n";
echo "Hapus file: database/migrations/2025_01_24_000001_add_organization_foreign_key_to_cte_events.php\n";
echo "Hapus file: database/migrations/2025_01_24_000002_add_organization_id_to_audit_logs.php\n";
echo "Hapus file: database/migrations/2025_01_24_000003_add_organization_id_to_retention_logs.php\n";
echo "Hapus file: database/migrations/2025_01_24_000004_add_organization_id_to_retention_policies.php\n";
echo "Hapus file: database/migrations/2025_01_24_000005_fix_cte_events_organization_id_constraint.php\n\n";

// Step 3: Jalankan migration baru
echo "Step 3: Jalankan migration baru yang aman...\n";
echo "Jalankan: php artisan migrate\n\n";

echo "=== Selesai ===\n";
echo "\nJika masih ada error, jalankan:\n";
echo "1. php artisan migrate:reset\n";
echo "2. php artisan migrate\n";
?>
