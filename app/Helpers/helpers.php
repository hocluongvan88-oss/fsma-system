<?php

use App\Helpers\TranslationHelper;

if (!function_exists('locale_date')) {
    /**
     * Format date according to current locale
     *
     * @param string|Carbon\Carbon $date
     * @param string|null $format
     * @return string
     */
    function locale_date($date, ?string $format = null): string
    {
        return TranslationHelper::formatDate($date, $format);
    }
}

if (!function_exists('locale_datetime')) {
    /**
     * Format datetime according to current locale
     *
     * @param string|Carbon\Carbon $datetime
     * @param string|null $format
     * @return string
     */
    function locale_datetime($datetime, ?string $format = null): string
    {
        return TranslationHelper::formatDateTime($datetime, $format);
    }
}

if (!function_exists('locale_time')) {
    /**
     * Format time according to current locale
     *
     * @param string|Carbon\Carbon $time
     * @param string|null $format
     * @return string
     */
    function locale_time($time, ?string $format = null): string
    {
        return TranslationHelper::formatTime($time, $format);
    }
}

if (!function_exists('locale_number')) {
    /**
     * Format number according to current locale
     *
     * @param float|int $number
     * @param int $decimals
     * @return string
     */
    function locale_number($number, int $decimals = 0): string
    {
        return TranslationHelper::formatNumber($number, $decimals);
    }
}

if (!function_exists('locale_currency')) {
    /**
     * Format currency according to current locale
     *
     * @param float|int $amount
     * @param string|null $currency
     * @return string
     */
    function locale_currency($amount, ?string $currency = null): string
    {
        return TranslationHelper::formatCurrency($amount, $currency);
    }
}

if (!function_exists('relative_time')) {
    /**
     * Get relative time (e.g., "2 hours ago")
     *
     * @param string|Carbon\Carbon $datetime
     * @return string
     */
    function relative_time($datetime): string
    {
        return TranslationHelper::relativeTime($datetime);
    }
}

if (!function_exists('current_locale_name')) {
    /**
     * Get current locale display name
     *
     * @return string
     */
    function current_locale_name(): string
    {
        return TranslationHelper::getLocaleDisplayName();
    }
}

if (!function_exists('current_locale_flag')) {
    /**
     * Get current locale flag emoji
     *
     * @return string
     */
    function current_locale_flag(): string
    {
        return TranslationHelper::getLocaleFlag();
    }
}

if (!function_exists('available_locales')) {
    /**
     * Get all available locales
     *
     * @return array
     */
    function available_locales(): array
    {
        return TranslationHelper::getAvailableLocales();
    }
}

if (!function_exists('trans_fallback')) {
    /**
     * Get translation with fallback
     *
     * @param string $key
     * @param string $fallback
     * @param array $replace
     * @return string
     */
    function trans_fallback(string $key, string $fallback, array $replace = []): string
    {
        return TranslationHelper::transWithFallback($key, $fallback, $replace);
    }
}
