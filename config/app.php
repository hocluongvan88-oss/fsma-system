<?php

return [
    'name' => env('APP_NAME', 'FSMA 204 Traceability System'),
    'company' => [
        'name' => 'VEXIM GLOBAL COMPANY LIMITED',
        'short_name' => 'VEXIM',
        'email' => 'contact@veximglobal.com',
        'phone' => '+84 373 685 634',
        'address' => '25/6/51 Ngọa Long, Tây Tựu, Hà Nội, Vietnam',
        'website' => 'https://veximglobal.com',
    ],
    'timezone' => 'Asia/Ho_Chi_Minh',
    'data_retention' => [
        'months' => 27,
        'description' => '2 years 3 months (3 months extra compared to FDA regulations)',
    ],
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'locale' => 'en',
    'fallback_locale' => 'vi',
    'faker_locale' => 'en_US',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
