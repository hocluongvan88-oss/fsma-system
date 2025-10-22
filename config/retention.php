<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Data Retention Policy - FSMA 204 Compliant
    |--------------------------------------------------------------------------
    |
    | CRITICAL: CTE Events, Trace Records, and Audit Logs are IMMUTABLE
    | per FSMA 204 requirements. They must NEVER be deleted, only archived.
    |
    | FDA Requirement: Records must be kept for at least 2 years.
    | This system keeps them INDEFINITELY to maintain complete audit trail.
    |
    */

    'default_retention_months' => 0, // 0 = Keep indefinitely (FSMA 204 compliance)

    'retention_policies' => [
        // These records must be preserved forever for FDA compliance and audit trail
        'trace_records' => 0,        // NEVER delete - Core traceability data
        'cte_events' => 0,           // NEVER delete - Immutable per FSMA 204
        'trace_relationships' => 0,  // NEVER delete - Audit trail
        'audit_logs' => 0,           // NEVER delete - Compliance requirement
        'e_signatures' => 0,         // NEVER delete - Legal requirement (21 CFR Part 11)

        // Error logs - 6 months (operational data, not compliance-related)
        'error_logs' => 6,

        // Notifications - 3 months (user notifications, not compliance-related)
        'notifications' => 3,

        // Master data - Keep indefinitely (reference data)
        'products' => 0,
        'locations' => 0,
        'partners' => 0,
        'documents' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Archival Configuration
    |--------------------------------------------------------------------------
    |
    | For data that should be archived but not deleted (CTE events, trace records).
    | Archival moves old data to cold storage while keeping it accessible.
    |
    */
    'archival' => [
        'enabled' => true,
        
        // Archive data older than this (months) - for performance optimization
        // Archived data is moved to separate tables but remains accessible
        'archive_after_months' => 36, // 3 years
        
        // Archive destination
        'archive_tables' => [
            'trace_records' => 'trace_records_archive',
            'cte_events' => 'cte_events_archive',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IMMUTABILITY & VOID MECHANISM
    |--------------------------------------------------------------------------
    |
    | CTE Events are immutable. Corrections are made via VOID mechanism:
    | 1. Original event is marked as 'voided' (NOT deleted)
    | 2. VOID event is created to document the correction
    | 3. New corrected event is created
    |
    | All three events are preserved forever for complete audit trail.
    |
    */
    'immutability' => [
        'protected_tables' => [
            'cte_events',
            'trace_records',
            'trace_relationships',
            'audit_logs',
            'e_signatures',
        ],
        
        // Prevent accidental deletion
        'deletion_protection' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention Schedule
    |--------------------------------------------------------------------------
    |
    | When to run the data retention cleanup job.
    | Only affects non-critical data (error_logs, notifications).
    |
    */
    'schedule' => 'daily',

    /*
    |--------------------------------------------------------------------------
    | Backup Before Deletion
    |--------------------------------------------------------------------------
    |
    | Whether to create a backup before deleting old data.
    | Backups are stored in storage/backups/retention/
    |
    */
    'backup_before_deletion' => true,

    /*
    |--------------------------------------------------------------------------
    | Notification Email
    |--------------------------------------------------------------------------
    |
    | Email address to notify when data retention cleanup is completed.
    |
    */
    'notification_email' => env('RETENTION_NOTIFICATION_EMAIL', 'admin@fsma204.com'),
];
