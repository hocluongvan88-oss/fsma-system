<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;

class AuthController extends BaseController
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $key = 'login.' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            
            return back()->withErrors([
                'email' => __('messages.too_many_login_attempts', ['minutes' => $minutes]),
            ])->onlyInput('email');
        }

        $credentials = $this->validateWithLocale($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            if (config('app.debug')) {
                Log::warning('[AUTH] User not found', [
                    'email' => $credentials['email'],
                ]);
            }
            
            RateLimiter::hit($key, 300); // Lock for 5 minutes after 5 attempts
            
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        }
        
        $passwordCheck = Hash::check($credentials['password'], $user->password);
        
        if (config('app.debug')) {
            Log::info('[AUTH] Login attempt', [
                'email' => $credentials['email'],
                'user_found' => true,
                'password_check_result' => $passwordCheck,
            ]);
        }
        
        if (!$passwordCheck) {
            RateLimiter::hit($key, 300);
            
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Update last login
            Auth::user()->update(['last_login' => now()]);
            
            RateLimiter::clear($key);
            
            if (config('app.debug')) {
                Log::info('[AUTH] Login successful', ['email' => $credentials['email']]);
            }

            return redirect()->intended(route('dashboard'));
        }

        if (config('app.debug')) {
            Log::error('[AUTH] Auth::attempt failed despite password check passing', [
                'email' => $credentials['email']
            ]);
        }
        
        RateLimiter::hit($key, 300);

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
