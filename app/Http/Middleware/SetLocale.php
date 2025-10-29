<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class SetLocale
{
    /**
     * Handle an incoming request.
     * 
     * Updated priority order: Flash > Query param > Session > User preference > Default
     * This ensures explicit user choices are respected immediately
     */
    public function handle(Request $request, Closure $next)
    {
        $availableLocales = config('locales.available_locales', []);
        $locale = null;
        
        // Priority 1: Flash data from redirect (highest priority - explicit action)
        if (Session::has('_locale')) {
            $locale = Session::get('_locale');
            if (is_string($locale) && array_key_exists($locale, $availableLocales)) {
                Session::put('locale', $locale);
                App::setLocale($locale);
                Session::forget('_locale');
            } else {
                $locale = null;
            }
        }
        
        // Priority 2: Query parameter (explicit user choice in current request)
        if (!$locale && $request->has('lang')) {
            $locale = $request->get('lang');
            if (!is_string($locale) || !array_key_exists($locale, $availableLocales)) {
                $locale = null;
            } else {
                Session::put('locale', $locale);
                App::setLocale($locale);
            }
        }
        
        // Priority 3: Session locale (current session preference)
        if (!$locale && Session::has('locale')) {
            $locale = Session::get('locale');
            
            if (!is_string($locale) || !array_key_exists($locale, $availableLocales)) {
                Session::forget('locale');
                $locale = null;
            } else {
                App::setLocale($locale);
            }
        }
        
        // Priority 4: User preference from database (persistent preference)
        if (!$locale && auth()->check() && auth()->user()->preferred_language) {
            $locale = auth()->user()->preferred_language;
            
            if (!is_string($locale) || !array_key_exists($locale, $availableLocales)) {
                $locale = null;
            } else {
                App::setLocale($locale);
                Session::put('locale', $locale);
            }
        }
        
        // Priority 5: Default locale (fallback)
        if (!$locale) {
            $locale = config('app.locale', 'en');
            App::setLocale($locale);
        }

        return $next($request);
    }
}
