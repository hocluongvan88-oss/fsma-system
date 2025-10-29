-- Migration 4: Remove package-related fields from users table
-- These fields are now managed at the organization level

-- First, backup the data (optional but recommended)
CREATE TABLE IF NOT EXISTS users_package_backup AS
SELECT id, package_id, max_cte_records_monthly, max_documents, max_users 
FROM users;

-- Remove the columns
ALTER TABLE users DROP COLUMN IF EXISTS package_id;
ALTER TABLE users DROP COLUMN IF EXISTS max_cte_records_monthly;
ALTER TABLE users DROP COLUMN IF EXISTS max_documents;
ALTER TABLE users DROP COLUMN IF EXISTS max_users;

-- Log the migration
INSERT INTO migration_logs (migration_name, status, executed_at) 
VALUES ('remove_package_fields_from_users', 'completed', NOW());
