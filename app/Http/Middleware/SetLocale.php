<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $availableLocales = config('locales.available_locales', []);
        $locale = null;
        
        // Priority 1: URL parameter (cache-busting parameter from language switch)
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if (!is_string($locale) || !array_key_exists($locale, $availableLocales)) {
                $locale = null;
            } else {
                Session::put('locale', $locale);
                App::setLocale($locale);
            }
        }
        
        // Priority 2: Session
        if (!$locale && Session::has('locale')) {
            $locale = Session::get('locale');
            
            if (!is_string($locale) || !array_key_exists($locale, $availableLocales)) {
                Session::forget('locale');
                $locale = null;
            } else {
                App::setLocale($locale);
            }
        }
        
        // Priority 3: User preference (if authenticated)
        if (!$locale && auth()->check() && auth()->user()->preferred_language) {
            $locale = auth()->user()->preferred_language;
            
            if (!is_string($locale) || !array_key_exists($locale, $availableLocales)) {
                $locale = null;
            } else {
                App::setLocale($locale);
                Session::put('locale', $locale);
            }
        }
        
        // Priority 4: Default from config
        if (!$locale) {
            $locale = config('app.locale', 'vi');
            App::setLocale($locale);
        }

        return $next($request);
    }
}
