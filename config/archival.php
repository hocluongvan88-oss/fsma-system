<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Archival Configuration for FSMA 204 Compliance
    |--------------------------------------------------------------------------
    |
    | This configuration defines how old CTE data is moved to cold storage
    | to optimize OpEx while maintaining FDA compliance and audit trail.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Archival Strategy
    |--------------------------------------------------------------------------
    |
    | Supported: "database", "s3_glacier", "local"
    |
    | - database: Move to separate archival tables (archival_cte_events, etc.)
    | - s3_glacier: Move to AWS S3 Glacier for cost-effective long-term storage
    | - local: Move to local storage (not recommended for production)
    |
    */
    'strategy' => env('ARCHIVAL_STRATEGY', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Hot Data Retention Period (Months)
    |--------------------------------------------------------------------------
    |
    | How long data stays in the main production database before archival.
    | FDA requires 2 years minimum, we use 3 years (36 months) for safety.
    |
    */
    'hot_data_months' => env('ARCHIVAL_HOT_DATA_MONTHS', 36),

    /*
    |--------------------------------------------------------------------------
    | Archival Data Types
    |--------------------------------------------------------------------------
    |
    | Data types that should be archived (not deleted) after hot period.
    |
    */
    'archival_data_types' => [
        'cte_events' => [
            'enabled' => true,
            'hot_months' => 36,
            'model' => \App\Models\CTEEvent::class,
            'archival_table' => 'archival_cte_events',
        ],
        'trace_records' => [
            'enabled' => true,
            'hot_months' => 36,
            'model' => \App\Models\TraceRecord::class,
            'archival_table' => 'archival_trace_records',
        ],
        // Relationships will be archived automatically when parent records are archived
        'audit_logs' => [
            'enabled' => true,
            'hot_months' => 36,
            'model' => \App\Models\AuditLog::class,
            'archival_table' => 'archival_audit_logs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS S3 Glacier Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AWS S3 Glacier cold storage.
    |
    */
    's3_glacier' => [
        'bucket' => env('AWS_ARCHIVAL_BUCKET', 'fsma204-archival'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'storage_class' => 'GLACIER', // or DEEP_ARCHIVE for even lower cost
        'prefix' => 'archival/', // S3 key prefix
    ],

    /*
    |--------------------------------------------------------------------------
    | Archival Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for separate archival database.
    |
    */
    'archival_database' => [
        'connection' => env('ARCHIVAL_DB_CONNECTION', 'archival'),
        'enabled' => env('ARCHIVAL_DB_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Archival Batch Size
    |--------------------------------------------------------------------------
    |
    | Number of records to process in each batch during archival.
    | Smaller batches = slower but less memory usage.
    |
    */
    'batch_size' => env('ARCHIVAL_BATCH_SIZE', 1000),

    /*
    |--------------------------------------------------------------------------
    | Archival Schedule
    |--------------------------------------------------------------------------
    |
    | When to run archival process (cron expression).
    | Default: Monthly on the 1st at 3:00 AM
    |
    */
    'schedule' => env('ARCHIVAL_SCHEDULE', '0 3 1 * *'),

    /*
    |--------------------------------------------------------------------------
    | Verify After Archival
    |--------------------------------------------------------------------------
    |
    | Whether to verify data integrity after archival before deleting from hot storage.
    |
    */
    'verify_after_archival' => env('ARCHIVAL_VERIFY', true),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Email notifications for archival operations.
    |
    */
    'notifications' => [
        'enabled' => env('ARCHIVAL_NOTIFICATIONS_ENABLED', true),
        'recipients' => explode(',', env('ARCHIVAL_NOTIFICATION_EMAILS', 'admin@fsma204.com')),
        'on_success' => true,
        'on_failure' => true,
    ],
];
