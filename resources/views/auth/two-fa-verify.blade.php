@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <!-- Translated 2FA verification page -->
        <h1 class="text-2xl font-bold mb-6">{{ __('messages.verify_two_factor_authentication') }}</h1>

        <form action="{{ route('two-fa.verify') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="method" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('messages.verification_method') }}
                </label>
                <select
                    id="method"
                    name="method"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
                    <option value="totp">{{ __('messages.authenticator_app') }}</option>
                    <option value="backup_code">{{ __('messages.backup_code') }}</option>
                </select>
            </div>

            <div class="mb-4">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('messages.code') }}
                </label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    placeholder="000000"
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
                {{ __('messages.verify') }}
            </button>
        </form>
    </div>
</div>
@endsection
