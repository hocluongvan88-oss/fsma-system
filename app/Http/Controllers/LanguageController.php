<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switch(Request $request, $locale)
    {
        Log::info("[v0] Language switch START", [
            'requested_locale' => $locale,
            'current_app_locale' => App::getLocale(),
            'current_session_locale' => Session::get('locale'),
            'user_id' => auth()->id() ?? 'guest'
        ]);
        
        $availableLocales = config('locales.available_locales', []);
        
        if (empty($availableLocales)) {
            Log::warning('[Locale] Language switch failed: No available locales configured');
            return redirect()->back()->with('error', __('messages.language_config_error'));
        }

        if (!array_key_exists($locale, $availableLocales)) {
            Log::warning("[Locale] Language switch failed: Invalid locale '{$locale}' requested", [
                'requested_locale' => $locale,
                'available_locales' => array_keys($availableLocales),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return redirect()->back()->with('error', __('messages.invalid_language'));
        }

        App::setLocale($locale);
        Log::info("[v0] App::setLocale() called", ['locale' => $locale, 'app_locale_after' => App::getLocale()]);
        
        Session::put('locale', $locale);
        Session::save();
        Log::info("[v0] Session::put() called", ['locale' => $locale, 'session_locale_after' => Session::get('locale')]);

        if (auth()->check()) {
            try {
                auth()->user()->update(['preferred_language' => $locale]);
                Log::info("[v0] User language preference updated", [
                    'user_id' => auth()->id(),
                    'locale' => $locale
                ]);
            } catch (\Exception $e) {
                Log::error("[v0] Failed to update user language preference", [
                    'user_id' => auth()->id(),
                    'locale' => $locale,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->clearLanguageCaches();

        Log::info("[v0] Language switched successfully - BEFORE REDIRECT", [
            'locale' => $locale,
            'user_id' => auth()->id() ?? 'guest',
            'session_locale' => Session::get('locale'),
            'app_locale' => App::getLocale()
        ]);

        return redirect()->back()
            ->with('success', trans('messages.language_changed', [], $locale))
            ->with('_locale', $locale)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Get translations for a specific locale via API
     * Called by JavaScript to load translations dynamically
     */
    public function getTranslations(Request $request, $locale)
    {
        Log::info("[v0] getTranslations API called", [
            'locale' => $locale,
            'user_id' => auth()->id() ?? 'guest'
        ]);

        $availableLocales = config('locales.available_locales', []);

        if (!array_key_exists($locale, $availableLocales)) {
            Log::warning("[v0] getTranslations: Invalid locale requested", [
                'requested_locale' => $locale,
                'available_locales' => array_keys($availableLocales)
            ]);
            return response()->json([
                'error' => 'Invalid locale',
                'available_locales' => array_keys($availableLocales)
            ], 400);
        }

        $translationPath = resource_path("lang/{$locale}");
        
        if (!is_dir($translationPath)) {
            Log::warning("[v0] getTranslations: Translation directory not found", [
                'locale' => $locale,
                'path' => $translationPath
            ]);
            return response()->json([
                'error' => 'Translation files not found for locale: ' . $locale
            ], 404);
        }

        $translations = [];
        $files = glob($translationPath . '/*.php');

        foreach ($files as $file) {
            $filename = basename($file, '.php');
            $translations[$filename] = require $file;
        }

        Log::info("[v0] getTranslations: Successfully loaded translations", [
            'locale' => $locale,
            'files_loaded' => count($files),
            'translation_keys' => array_keys($translations)
        ]);

        $timestamp = time();
        
        return response()->json([
            'locale' => $locale,
            'timestamp' => $timestamp,
            'translations' => $translations
        ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0')
            ->header('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Clear all language-related caches
     */
    private function clearLanguageCaches()
    {
        try {
            Cache::forget('config');
            Cache::forget('views');
            Cache::forget('translations');
            
            foreach (array_keys(config('locales.available_locales', [])) as $loc) {
                Cache::forget("translations.{$loc}");
                Cache::forget("locale.{$loc}");
            }
            
            if (method_exists(Cache::class, 'tags')) {
                try {
                    Cache::tags(['traceability', 'api', 'locale'])->flush();
                } catch (\Exception $e) {
                    // Silently fail if cache driver doesn't support tags
                }
            }
            
            Log::info("[v0] Language caches cleared successfully");
        } catch (\Exception $e) {
            Log::error("[v0] Failed to clear language caches", [
                'error' => $e->getMessage()
            ]);
        }
    }
}
