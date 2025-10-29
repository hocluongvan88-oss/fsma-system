<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Added localized validation method that automatically uses current locale
     * Validates request data with localized error messages
     */
    protected function validateWithLocale(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $locale = app()->getLocale();
        
        // Load locale-specific validation messages
        $localeMessages = __('validation', [], $locale);
        
        // Merge custom messages with locale messages
        $messages = array_merge($localeMessages, $messages);
        
        return $request->validate($rules, $messages, $customAttributes);
    }

    /**
     * Helper method to get localized error message
     */
    protected function getLocalizedErrorMessage($key, $params = [])
    {
        return __("messages.{$key}", $params);
    }

    /**
     * Helper method to get localized success message
     */
    protected function getLocalizedSuccessMessage($key, $params = [])
    {
        return __("messages.{$key}", $params);
    }
}
