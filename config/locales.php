<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Available Locales
    |--------------------------------------------------------------------------
    |
    | Define all available locales for the application with their metadata.
    | Each locale includes display name, flag emoji, date/time formats, and timezone.
    |
    */

    'available_locales' => [
        'en' => [
            'code' => 'en',
            'name' => 'English',
            'flag' => '🇺🇸',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'datetime_format' => 'Y-m-d H:i:s',
            'timezone' => 'America/New_York',
        ],
        'vi' => [
            'code' => 'vi',
            'name' => 'Tiếng Việt',
            'flag' => '🇻🇳',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i:s',
            'datetime_format' => 'd/m/Y H:i:s',
            'timezone' => 'Asia/Ho_Chi_Minh',
        ],
        'zh' => [
            'code' => 'zh',
            'name' => '中文',
            'flag' => '🇨🇳',
            'date_format' => 'Y年m月d日',
            'time_format' => 'H:i:s',
            'datetime_format' => 'Y年m月d日 H:i:s',
            'timezone' => 'Asia/Shanghai',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | The default locale that will be used if no locale is set.
    | Changed from 'en' to 'vi' to match config/app.php
    |
    */

    'default' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to any of the locales
    | which will be supported by your application.
    |
    */

    'fallback' => 'vi',
];
