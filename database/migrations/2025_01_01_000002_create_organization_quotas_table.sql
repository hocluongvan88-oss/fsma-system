-- Migration 2: Create organization_quotas table
-- This table tracks usage per organization for quota enforcement

CREATE TABLE IF NOT EXISTS organization_quotas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NOT NULL,
    feature_name VARCHAR(255) NOT NULL,
    used_count INT UNSIGNED DEFAULT 0,
    limit_count INT UNSIGNED DEFAULT 0,
    reset_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE KEY unique_org_feature (organization_id, feature_name),
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE ON UPDATE CASCADE,
    
    -- Indexes
    INDEX idx_organization_id (organization_id),
    INDEX idx_feature_name (feature_name),
    INDEX idx_reset_date (reset_date)
);

-- Log the migration
INSERT INTO migration_logs (migration_name, status, executed_at) 
VALUES ('create_organization_quotas_table', 'completed', NOW());
