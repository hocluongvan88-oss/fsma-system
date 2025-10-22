<?php

namespace App\Helpers;

use Carbon\Carbon;
use NumberFormatter;

class LocaleFormattingHelper
{

    /**
     * Format currency based on locale
     */
    public static function formatCurrency($amount, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        $currencyMap = [
            'en' => ['code' => 'USD', 'symbol' => '$'],
            'vi' => ['code' => 'VND', 'symbol' => '₫'],
            'zh' => ['code' => 'CNY', 'symbol' => '¥'],
        ];

        $currency = $currencyMap[$locale] ?? $currencyMap['en'];
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        
        return $formatter->formatCurrency($amount, $currency['code']);
    }

    /**
     * Format number based on locale
     */
    public static function formatNumber($number, $decimals = 2, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        
        return $formatter->format($number);
    }

    /**
     * Format date based on locale
     */
    public static function formatDate($date, $format = null, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        $dateFormats = [
            'en' => 'M d, Y',
            'vi' => 'd/m/Y',
            'zh' => 'Y年m月d日',
        ];

        $format = $format ?? $dateFormats[$locale] ?? $dateFormats['en'];
        
        return $date->format($format);
    }

    /**
     * Format datetime based on locale
     */
    public static function formatDateTime($datetime, $format = null, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        if (is_string($datetime)) {
            $datetime = Carbon::parse($datetime);
        }

        $dateTimeFormats = [
            'en' => 'M d, Y H:i:s',
            'vi' => 'd/m/Y H:i:s',
            'zh' => 'Y年m月d日 H:i:s',
        ];

        $format = $format ?? $dateTimeFormats[$locale] ?? $dateTimeFormats['en'];
        
        return $datetime->format($format);
    }

    /**
     * Format time based on locale
     */
    public static function formatTime($time, $format = null, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        if (is_string($time)) {
            $time = Carbon::parse($time);
        }

        $timeFormats = [
            'en' => 'H:i:s',
            'vi' => 'H:i:s',
            'zh' => 'H:i:s',
        ];

        $format = $format ?? $timeFormats[$locale] ?? $timeFormats['en'];
        
        return $time->format($format);
    }

    /**
     * Get currency symbol for locale
     */
    public static function getCurrencySymbol($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        $symbols = [
            'en' => '$',
            'vi' => '₫',
            'zh' => '¥',
        ];

        return $symbols[$locale] ?? '$';
    }

    /**
     * Get currency code for locale
     */
    public static function getCurrencyCode($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        
        $codes = [
            'en' => 'USD',
            'vi' => 'VND',
            'zh' => 'CNY',
        ];

        return $codes[$locale] ?? 'USD';
    }

    /**
     * Format percentage based on locale
     */
    public static function formatPercentage($number, $decimals = 2, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        $formatter = new NumberFormatter($locale, NumberFormatter::PERCENT);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        
        return $formatter->format($number / 100);
    }
}
