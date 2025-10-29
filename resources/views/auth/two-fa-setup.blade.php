@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Added Veximglobal branding header -->
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 rounded-t-lg p-6 text-white">
            <div class="flex items-center justify-between mb-2">
                <h1 class="text-3xl font-bold">Veximglobal</h1>
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>
            <p class="text-blue-100">{{ __('messages.enable_two_factor_authentication') }}</p>
        </div>
        
        <div class="bg-white rounded-b-lg shadow-lg p-8">
            <!-- Added security benefits section -->
            <div class="mb-8 p-4 bg-blue-50 border-l-4 border-blue-600 rounded">
                <h3 class="font-semibold text-blue-900 mb-2">Why Enable 2FA?</h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>Protect your account from unauthorized access</li>
                    <li>Add an extra layer of security beyond passwords</li>
                    <li>Required for FSMA 204 compliance</li>
                    <li>Secure your sensitive traceability data</li>
                </ul>
            </div>

            <!-- Improved step-by-step instructions -->
            <div class="mb-8">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Setup Instructions</h2>
                
                <div class="space-y-6">
                    <!-- Step 1 -->
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 mb-2">Download an Authenticator App</h3>
                            <p class="text-gray-600 text-sm mb-2">Install one of these apps on your mobile device:</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-3 py-1 bg-gray-100 rounded-full text-sm">Google Authenticator</span>
                                <span class="px-3 py-1 bg-gray-100 rounded-full text-sm">Microsoft Authenticator</span>
                                <span class="px-3 py-1 bg-gray-100 rounded-full text-sm">Authy</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 mb-2">Scan QR Code</h3>
                            <p class="text-gray-600 text-sm mb-3">Open your authenticator app and scan this QR code:</p>
                            <div class="bg-white p-6 rounded-lg border-2 border-gray-200 inline-block">
                                <img src="{{ $qrCodeUrl }}" alt="{{ __('messages.qr_code') }}" class="w-48 h-48">
                            </div>
                            
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-2">{{ __('messages.or_enter_code_manually') }}</p>
                                <div class="bg-gray-100 p-4 rounded-lg border border-gray-300">
                                    <p class="text-xs text-gray-500 mb-1">Secret Key:</p>
                                    <div class="font-mono text-lg tracking-widest text-center text-gray-800 select-all">
                                        {{ $secret }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">3</div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-800 mb-2">Enter Verification Code</h3>
                            <p class="text-gray-600 text-sm mb-3">Enter the 6-digit code from your authenticator app:</p>
                            
                            <form action="{{ route('two-fa.enable') }}" method="POST">
                                @csrf
                                <input type="hidden" name="secret" value="{{ $secret }}">

                                <div class="mb-6">
                                    <input
                                        type="text"
                                        id="code"
                                        name="code"
                                        placeholder="000000"
                                        maxlength="6"
                                        class="w-full px-4 py-3 text-2xl text-center font-mono tracking-widest border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                        autofocus
                                    >
                                    @error('code')
                                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button
                                    type="submit"
                                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold text-lg shadow-lg"
                                >
                                    {{ __('messages.verify_and_enable_2fa') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Added important security notice -->
            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                <div class="flex gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-yellow-600">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <h4 class="font-semibold text-yellow-900 mb-1">Important: Save Your Backup Codes</h4>
                        <p class="text-sm text-yellow-800">After enabling 2FA, you'll receive backup codes. Save them in a secure location. You'll need them if you lose access to your authenticator app.</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    Cancel and return to dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-focus and auto-submit when 6 digits entered
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length === 6) {
        this.form.submit();
    }
});
</script>
@endsection
