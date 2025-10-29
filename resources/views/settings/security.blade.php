@extends('layouts.app')

@section('title', 'Security Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Created new security settings page for 2FA management -->
    <div class="card">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Two-Factor Authentication</h2>
                <p class="text-gray-600 mt-1">Add an extra layer of security to your account</p>
            </div>
            @if(auth()->user()->two_fa_enabled)
            <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                Enabled
            </span>
            @else
            <span class="px-4 py-2 bg-gray-100 text-gray-600 rounded-full font-semibold">Disabled</span>
            @endif
        </div>

        @if(auth()->user()->two_fa_enabled)
        <!-- 2FA is enabled -->
        <div class="space-y-6">
            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded">
                <p class="text-green-800">Your account is protected with two-factor authentication. You'll need to enter a code from your authenticator app when you sign in.</p>
            </div>

            <div class="border-t pt-6">
                <h3 class="font-semibold text-gray-800 mb-4">Manage 2FA</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="font-semibold text-gray-800">Backup Codes</h4>
                            <p class="text-sm text-gray-600">Regenerate your backup codes if you've used them or lost them</p>
                        </div>
                        <form action="{{ route('two-fa.regenerate-codes') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                Regenerate Codes
                            </button>
                        </form>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h4 class="font-semibold text-gray-800">Disable 2FA</h4>
                            <p class="text-sm text-gray-600">Remove two-factor authentication from your account</p>
                        </div>
                        <button onclick="showDisableModal()" class="btn btn-secondary text-red-600 hover:bg-red-50">
                            Disable 2FA
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- 2FA is disabled -->
        <div class="space-y-6">
            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                <p class="text-yellow-800">Two-factor authentication is not enabled. Your account is less secure without it.</p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-800 mb-3">Benefits of 2FA:</h3>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-start gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-green-600 mt-0.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Protects against unauthorized access even if your password is compromised
                    </li>
                    <li class="flex items-start gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-green-600 mt-0.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Required for FSMA 204 compliance and data security
                    </li>
                    <li class="flex items-start gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-green-600 mt-0.5">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        Provides backup codes for account recovery
                    </li>
                </ul>
            </div>

            <div class="pt-4">
                <a href="{{ route('two-fa.setup') }}" class="btn btn-primary inline-block">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Enable Two-Factor Authentication
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Password Change Section -->
    <div class="card mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Change Password</h2>
        
        <form action="{{ route('settings.password.update') }}" method="POST" class="max-w-md">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" id="new_password" name="new_password" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-input" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>

<!-- Disable 2FA Modal -->
<div id="disableModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-8 max-w-md mx-4">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Disable Two-Factor Authentication?</h3>
        <p class="text-gray-600 mb-6">This will make your account less secure. You'll need to enter your password to confirm.</p>
        
        <form action="{{ route('two-fa.disable') }}" method="POST">
            @csrf
            @method('DELETE')
            
            <div class="form-group">
                <label for="password" class="form-label">Enter Your Password</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>
            
            <div class="flex gap-4">
                <button type="button" onclick="hideDisableModal()" class="flex-1 btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn btn-primary bg-red-600 hover:bg-red-700">
                    Disable 2FA
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showDisableModal() {
    document.getElementById('disableModal').classList.remove('hidden');
    document.getElementById('disableModal').classList.add('flex');
}

function hideDisableModal() {
    document.getElementById('disableModal').classList.add('hidden');
    document.getElementById('disableModal').classList.remove('flex');
}
</script>
@endsection
