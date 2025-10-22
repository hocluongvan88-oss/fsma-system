<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'secret' => env('MAILGUN_SECRET'),
        'domain' => env('MAILGUN_DOMAIN'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'crm' => [
        'auto_sync' => env('CRM_AUTO_SYNC', false),
    ],

    'hubspot' => [
        'enabled' => env('HUBSPOT_ENABLED', false),
        'api_key' => env('HUBSPOT_API_KEY'),
    ],

    'salesforce' => [
        'enabled' => env('SALESFORCE_ENABLED', false),
        'client_id' => env('SALESFORCE_CLIENT_ID'),
        'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
        'username' => env('SALESFORCE_USERNAME'),
        'password' => env('SALESFORCE_PASSWORD'),
        'instance_url' => env('SALESFORCE_INSTANCE_URL'),
    ],

    'pipedrive' => [
        'enabled' => env('PIPEDRIVE_ENABLED', false),
        'api_token' => env('PIPEDRIVE_API_TOKEN'),
    ],

    'zapier' => [
        'enabled' => env('ZAPIER_ENABLED', false),
        'webhook_url' => env('ZAPIER_WEBHOOK_URL'),
    ],

];
