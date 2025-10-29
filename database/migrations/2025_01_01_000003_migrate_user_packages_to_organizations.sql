-- Migration 3: Migrate existing user packages to organizations
-- This migration copies package_id from users to their organizations

UPDATE organizations o
SET o.package_id = (
    SELECT DISTINCT u.package_id 
    FROM users u 
    WHERE u.organization_id = o.id 
    LIMIT 1
)
WHERE o.package_id = 'free' AND EXISTS (
    SELECT 1 FROM users u WHERE u.organization_id = o.id AND u.package_id != 'free'
);

-- Initialize organization quotas based on package limits
INSERT INTO organization_quotas (organization_id, feature_name, used_count, limit_count, reset_date)
SELECT 
    o.id,
    'cte_records_monthly',
    0,
    COALESCE(p.max_cte_records_monthly, 0),
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
FROM organizations o
LEFT JOIN packages p ON o.package_id = p.id
WHERE NOT EXISTS (
    SELECT 1 FROM organization_quotas oq 
    WHERE oq.organization_id = o.id AND oq.feature_name = 'cte_records_monthly'
);

INSERT INTO organization_quotas (organization_id, feature_name, used_count, limit_count, reset_date)
SELECT 
    o.id,
    'documents',
    0,
    COALESCE(p.max_documents, 0),
    NULL
FROM organizations o
LEFT JOIN packages p ON o.package_id = p.id
WHERE NOT EXISTS (
    SELECT 1 FROM organization_quotas oq 
    WHERE oq.organization_id = o.id AND oq.feature_name = 'documents'
);

INSERT INTO organization_quotas (organization_id, feature_name, used_count, limit_count, reset_date)
SELECT 
    o.id,
    'users',
    (SELECT COUNT(*) FROM users u WHERE u.organization_id = o.id AND u.is_active = 1),
    COALESCE(p.max_users, 0),
    NULL
FROM organizations o
LEFT JOIN packages p ON o.package_id = p.id
WHERE NOT EXISTS (
    SELECT 1 FROM organization_quotas oq 
    WHERE oq.organization_id = o.id AND oq.feature_name = 'users'
);

-- Log the migration
INSERT INTO migration_logs (migration_name, status, executed_at) 
VALUES ('migrate_user_packages_to_organizations', 'completed', NOW());
