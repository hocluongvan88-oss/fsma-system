<?php

/**
 * Security Configuration
 * 
 * Centralized security settings for multi-tenant organization isolation.
 * These settings control how the application enforces data isolation between organizations.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Organization Isolation
    |--------------------------------------------------------------------------
    |
    | Controls how strictly organization isolation is enforced.
    | 'strict' mode prevents any cross-organization data access
    | 'permissive' mode logs violations but allows access (not recommended for production)
    |
    */
    'organization_isolation' => env('SECURITY_ORG_ISOLATION', 'strict'),

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Enable/disable audit logging for security events
    |
    */
    'audit_logging' => [
        'enabled' => env('SECURITY_AUDIT_LOGGING', true),
        'log_cross_org_attempts' => env('SECURITY_LOG_CROSS_ORG', true),
        'log_failed_auth' => env('SECURITY_LOG_FAILED_AUTH', true),
        'log_permission_denied' => env('SECURITY_LOG_PERMISSION_DENIED', true),
        'retention_days' => env('SECURITY_AUDIT_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | System Admin Configuration
    |--------------------------------------------------------------------------
    |
    | Email address of the system administrator
    | System admins bypass organization isolation checks
    |
    */
    'system_admin_email' => env('SYSTEM_ADMIN_EMAIL', 'admin@example.com'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration for sensitive operations
    |
    */
    'rate_limiting' => [
        'failed_login_attempts' => env('SECURITY_FAILED_LOGIN_ATTEMPTS', 5),
        'failed_login_window_minutes' => env('SECURITY_FAILED_LOGIN_WINDOW', 15),
        'api_rate_limit' => env('SECURITY_API_RATE_LIMIT', 60),
        'api_rate_limit_window' => env('SECURITY_API_RATE_LIMIT_WINDOW', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Encryption
    |--------------------------------------------------------------------------
    |
    | Sensitive data encryption settings
    |
    */
    'encryption' => [
        'enabled' => env('SECURITY_ENCRYPTION_ENABLED', true),
        'algorithm' => env('SECURITY_ENCRYPTION_ALGORITHM', 'AES-256-CBC'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelisting
    |--------------------------------------------------------------------------
    |
    | Optional IP whitelisting for admin access
    |
    */
    'ip_whitelist' => [
        'enabled' => env('SECURITY_IP_WHITELIST_ENABLED', false),
        'ips' => explode(',', env('SECURITY_WHITELISTED_IPS', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Session security settings
    |
    */
    'session' => [
        'timeout_minutes' => env('SECURITY_SESSION_TIMEOUT', 60),
        'concurrent_sessions_limit' => env('SECURITY_CONCURRENT_SESSIONS', 3),
        'require_https' => env('SECURITY_REQUIRE_HTTPS', true),
    ],
];
