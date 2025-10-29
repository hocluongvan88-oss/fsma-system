<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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
            return back()->withErrors([
                'email' => 'Invalid email or password.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Update last login
            Auth::user()->update(['last_login' => now()]);
            
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

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $locale = Session::get('locale') ?? app()->getLocale();
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Session::put('locale', $locale);

        return redirect()->route('login');
    }
}
