@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Added Veximglobal branding header -->
        <div class="bg-gradient-to-r from-green-700 to-green-600 rounded-t-lg p-6 text-white text-center">
            <div class="mb-3">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h1 class="text-2xl font-bold mb-2">Veximglobal</h1>
            <p class="text-green-100">2FA Enabled Successfully!</p>
        </div>
        
        <div class="bg-white rounded-b-lg shadow-lg p-8">
            <!-- Enhanced success message -->
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                <div class="flex gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-green-600">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-green-900 mb-1">Two-Factor Authentication is now active</h3>
                        <p class="text-sm text-green-800">Your account is now protected with an additional layer of security.</p>
                    </div>
                </div>
            </div>

            <!-- Critical warning section -->
            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded">
                <div class="flex gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 text-yellow-600">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-yellow-900 mb-1">IMPORTANT: Save Your Backup Codes</h3>
                        <p class="text-sm text-yellow-800 mb-2">These codes are your only way to access your account if you lose your phone or authenticator app.</p>
                        <ul class="text-sm text-yellow-800 space-y-1">
                            <li>Each code can only be used once</li>
                            <li>Store them in a secure location</li>
                            <li>Never share them with anyone</li>
                            <li>You can regenerate new codes anytime in Security Settings</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Improved backup codes display -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Your Backup Codes</h2>
                <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-6">
                    <div class="grid grid-cols-2 gap-3 font-mono text-center">
                        @foreach($backupCodes as $index => $code)
                            <div class="bg-white p-3 rounded border border-gray-200 text-lg tracking-wider font-semibold text-gray-800">
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Enhanced action buttons -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <button
                    onclick="copyToClipboard()"
                    class="flex items-center justify-center gap-2 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition font-semibold shadow-md"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    Copy All Codes
                </button>
                <button
                    onclick="window.print()"
                    class="flex items-center justify-center gap-2 bg-gray-600 text-white py-3 rounded-lg hover:bg-gray-700 transition font-semibold shadow-md"
                >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print Codes
                </button>
            </div>

            <!-- Download as text file option -->
            <div class="mb-6 text-center">
                <button
                    onclick="downloadCodes()"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                    Download as text file
                </button>
            </div>

            <!-- Security tips -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-semibold text-blue-900 mb-2 text-sm">Security Tips:</h3>
                <ul class="text-xs text-blue-800 space-y-1">
                    <li>Store codes in a password manager (recommended)</li>
                    <li>Print and keep in a secure physical location</li>
                    <li>Take a photo and store in an encrypted folder</li>
                    <li>Do NOT store in plain text files or unencrypted cloud storage</li>
                </ul>
            </div>

            <div class="text-center">
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-block bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3 rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold text-lg shadow-lg"
                >
                    Continue to Dashboard
                </a>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('settings.security') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                    Go to Security Settings
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript with download functionality -->
<script>
function copyToClipboard() {
    const codes = @json($backupCodes);
    const codesText = codes.join('\n');
    
    navigator.clipboard.writeText(codesText).then(() => {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Copied!';
        btn.classList.add('bg-green-600');
        btn.classList.remove('bg-blue-600');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-blue-600');
        }, 2000);
    }).catch(err => {
        alert('Failed to copy codes. Please try again or copy manually.');
        console.error('[v0] Copy failed:', err);
    });
}

function downloadCodes() {
    const codes = @json($backupCodes);
    const codesText = 'Veximglobal 2FA Backup Codes\n' +
                      'Generated: ' + new Date().toLocaleString() + '\n' +
                      '=====================================\n\n' +
                      codes.join('\n') + '\n\n' +
                      '=====================================\n' +
                      'IMPORTANT: Keep these codes secure!\n' +
                      'Each code can only be used once.\n';
    
    const blob = new Blob([codesText], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'veximglobal-backup-codes-' + Date.now() + '.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Print styles
const style = document.createElement('style');
style.textContent = `
    @media print {
        body * {
            visibility: hidden;
        }
        .container, .container * {
            visibility: visible;
        }
        .container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        button {
            display: none !important;
        }
        .bg-gradient-to-r {
            background: #16a34a !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
`;
document.head.appendChild(style);
</script>
@endsection
