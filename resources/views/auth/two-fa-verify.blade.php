@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <!-- Added Veximglobal branding header -->
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 rounded-t-lg p-6 text-white text-center">
            <div class="mb-3">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold mb-2">Veximglobal</h1>
            <p class="text-blue-100">{{ __('messages.verify_two_factor_authentication') }}</p>
        </div>
        
        <div class="bg-white rounded-b-lg shadow-lg p-8">
            <!-- Added security message -->
            <div class="mb-6 text-center">
                <p class="text-gray-600">Enter the verification code from your authenticator app to continue.</p>
            </div>

            <form action="{{ route('two-fa.verify') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="method" class="block text-sm font-semibold text-gray-700 mb-3">
                        {{ __('messages.verification_method') }}
                    </label>
                    <select
                        id="method"
                        name="method"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                        onchange="updatePlaceholder()"
                    >
                        <option value="totp">{{ __('messages.authenticator_app') }}</option>
                        <option value="backup_code">{{ __('messages.backup_code') }}</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label for="code" class="block text-sm font-semibold text-gray-700 mb-3">
                        {{ __('messages.code') }}
                    </label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        placeholder="000000"
                        class="w-full px-4 py-3 text-2xl text-center font-mono tracking-widest border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required
                        autofocus
                    >
                    @error('code')
                        <p class="text-red-500 text-sm mt-2 text-center">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold text-lg shadow-lg"
                >
                    {{ __('messages.verify') }}
                </button>
            </form>

            <!-- Added help section -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-800 mb-2 text-sm">Need Help?</h4>
                <ul class="text-xs text-gray-600 space-y-1">
                    <li>Make sure your device time is synchronized</li>
                    <li>Try using a backup code if your authenticator isn't working</li>
                    <li>Contact support if you've lost access to both methods</li>
                </ul>
            </div>

            <div class="mt-6 text-center">
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm">
                        Cancel and logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updatePlaceholder() {
    const method = document.getElementById('method').value;
    const codeInput = document.getElementById('code');
    
    if (method === 'totp') {
        codeInput.placeholder = '000000';
        codeInput.maxLength = 6;
    } else {
        codeInput.placeholder = 'Enter backup code';
        codeInput.maxLength = 10;
    }
    codeInput.value = '';
    codeInput.focus();
}

// Auto-submit when 6 digits entered for TOTP
document.getElementById('code').addEventListener('input', function(e) {
    const method = document.getElementById('method').value;
    if (method === 'totp') {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length === 6) {
            this.form.submit();
        }
    }
});
</script>
@endsection
