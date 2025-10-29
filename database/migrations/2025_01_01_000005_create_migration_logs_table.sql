-- Helper table to track migration execution
-- This table helps monitor which migrations have been executed

CREATE TABLE IF NOT EXISTS migration_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration_name VARCHAR(255) NOT NULL UNIQUE,
    status VARCHAR(50) DEFAULT 'pending',
    error_message TEXT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_executed_at (executed_at)
);
