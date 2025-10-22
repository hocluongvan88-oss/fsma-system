@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <!-- Translated 2FA setup page -->
        <h1 class="text-2xl font-bold mb-6">{{ __('messages.enable_two_factor_authentication') }}</h1>

        <div class="mb-6">
            <p class="text-gray-600 mb-4">
                {{ __('messages.scan_qr_code_with_authenticator') }}
            </p>
            <div class="bg-gray-100 p-4 rounded-lg flex justify-center mb-4">
                <img src="{{ $qrCodeUrl }}" alt="{{ __('messages.qr_code') }}" class="w-48 h-48">
            </div>
            <p class="text-sm text-gray-500 mb-4">
                {{ __('messages.or_enter_code_manually') }}
            </p>
            <div class="bg-gray-100 p-3 rounded font-mono text-center text-lg tracking-widest mb-4">
                {{ $secret }}
            </div>
        </div>

        <form action="{{ route('two-fa.enable') }}" method="POST">
            @csrf
            <input type="hidden" name="secret" value="{{ $secret }}">

            <div class="mb-4">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('messages.verification_code') }}
                </label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    placeholder="000000"
                    maxlength="6"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
                @error('code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition"
            >
                {{ __('messages.verify_and_enable_2fa') }}
            </button>
        </form>

        <p class="text-xs text-gray-500 mt-4">
            {{ __('messages.save_backup_codes_warning') }}
        </p>
    </div>
</div>
@endsection
