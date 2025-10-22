<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class TranslationHelper
{
    /**
     * Get the current locale configuration
     *
     * @return array
     */
    public static function getCurrentLocaleConfig(): array
    {
        $locale = App::getLocale();
        $locales = config('locales.available_locales', []);
        
        return $locales[$locale] ?? $locales['en'];
    }

    /**
     * Format a date according to the current locale
     *
     * @param string|Carbon $date
     * @param string|null $format
     * @return string
     */
    public static function formatDate($date, ?string $format = null): string
    {
        if (!$date) {
            return '';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        $localeConfig = self::getCurrentLocaleConfig();
        
        $format = $format ?? $localeConfig['date_format'];
        
        return $carbon->format($format);
    }

    /**
     * Format a datetime according to the current locale
     *
     * @param string|Carbon $datetime
     * @param string|null $format
     * @return string
     */
    public static function formatDateTime($datetime, ?string $format = null): string
    {
        if (!$datetime) {
            return '';
        }

        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        $localeConfig = self::getCurrentLocaleConfig();
        
        $format = $format ?? $localeConfig['datetime_format'];
        
        return $carbon->format($format);
    }

    /**
     * Format a time according to the current locale
     *
     * @param string|Carbon $time
     * @param string|null $format
     * @return string
     */
    public static function formatTime($time, ?string $format = null): string
    {
        if (!$time) {
            return '';
        }

        $carbon = $time instanceof Carbon ? $time : Carbon::parse($time);
        $localeConfig = self::getCurrentLocaleConfig();
        
        $format = $format ?? $localeConfig['time_format'];
        
        return $carbon->format($format);
    }

    /**
     * Format a number according to the current locale
     *
     * @param float|int $number
     * @param int $decimals
     * @return string
     */
    public static function formatNumber($number, int $decimals = 0): string
    {
        $locale = App::getLocale();
        
        // Define decimal and thousands separators per locale
        $separators = [
            'en' => ['decimal' => '.', 'thousands' => ','],
            'vi' => ['decimal' => ',', 'thousands' => '.'],
            'zh' => ['decimal' => '.', 'thousands' => ','],
            'ja' => ['decimal' => '.', 'thousands' => ','],
        ];
        
        $sep = $separators[$locale] ?? $separators['en'];
        
        return number_format($number, $decimals, $sep['decimal'], $sep['thousands']);
    }

    /**
     * Format currency according to the current locale
     *
     * @param float|int $amount
     * @param string|null $currency
     * @return string
     */
    public static function formatCurrency($amount, ?string $currency = null): string
    {
        $locale = App::getLocale();
        $currency = $currency ?? 'USD';
        
        // Define currency formats per locale
        $formats = [
            'en' => ['symbol' => '$', 'position' => 'before', 'space' => false],
            'vi' => ['symbol' => '₫', 'position' => 'after', 'space' => true],
            'zh' => ['symbol' => '¥', 'position' => 'before', 'space' => false],
            'ja' => ['symbol' => '¥', 'position' => 'before', 'space' => false],
        ];
        
        $format = $formats[$locale] ?? $formats['en'];
        $formattedAmount = self::formatNumber($amount, 2);
        
        if ($format['position'] === 'before') {
            return $format['symbol'] . ($format['space'] ? ' ' : '') . $formattedAmount;
        } else {
            return $formattedAmount . ($format['space'] ? ' ' : '') . $format['symbol'];
        }
    }

    /**
     * Get relative time (e.g., "2 hours ago")
     *
     * @param string|Carbon $datetime
     * @return string
     */
    public static function relativeTime($datetime): string
    {
        if (!$datetime) {
            return '';
        }

        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        
        return $carbon->diffForHumans();
    }

    /**
     * Translate with pluralization support
     *
     * @param string $key
     * @param int $count
     * @param array $replace
     * @return string
     */
    public static function transChoice(string $key, int $count, array $replace = []): string
    {
        return trans_choice($key, $count, array_merge(['count' => $count], $replace));
    }

    /**
     * Get all available locales
     *
     * @return array
     */
    public static function getAvailableLocales(): array
    {
        return config('locales.available_locales', []);
    }

    /**
     * Get locale display name
     *
     * @param string|null $locale
     * @return string
     */
    public static function getLocaleDisplayName(?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $locales = self::getAvailableLocales();
        
        return $locales[$locale]['name'] ?? $locale;
    }

    /**
     * Get locale flag emoji
     *
     * @param string|null $locale
     * @return string
     */
    public static function getLocaleFlag(?string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $locales = self::getAvailableLocales();
        
        return $locales[$locale]['flag'] ?? '🌐';
    }

    /**
     * Check if a translation key exists
     *
     * @param string $key
     * @param string|null $locale
     * @return bool
     */
    public static function hasTranslation(string $key, ?string $locale = null): bool
    {
        $locale = $locale ?? App::getLocale();
        return trans($key, [], $locale) !== $key;
    }

    /**
     * Get translation with fallback
     *
     * @param string $key
     * @param string $fallback
     * @param array $replace
     * @return string
     */
    public static function transWithFallback(string $key, string $fallback, array $replace = []): string
    {
        $translation = trans($key, $replace);
        
        return $translation !== $key ? $translation : $fallback;
    }
}
