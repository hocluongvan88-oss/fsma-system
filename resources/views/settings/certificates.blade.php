@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white mb-1">{{ __('messages.digital_certificates') }}</h1>
        <p class="text-gray-400 text-sm">{{ __('messages.manage_digital_certificates_desc') }}</p>
    </div>

    <!-- Warning Alert -->
    @if($warning)
        <div class="bg-amber-900 bg-opacity-20 border-l-4 border-amber-500 rounded-lg p-4 mb-4 flex items-start gap-3">
            <!-- Đảm bảo kích thước icon được kiểm soát tốt -->
            <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <p class="text-amber-200 text-sm">{{ $warning }}</p>
        </div>
    @endif

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-900 bg-opacity-20 border-l-4 border-green-500 rounded-lg p-4 mb-4 flex items-start gap-3">
            <!-- Đảm bảo kích thước icon được kiểm soát tốt -->
            <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-green-200 text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-900 bg-opacity-20 border-l-4 border-red-500 rounded-lg p-4 mb-4">
            <div class="flex items-start gap-3">
                <!-- Đảm bảo kích thước icon được kiểm soát tốt -->
                <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    @foreach($errors->all() as $error)
                        <p class="text-red-200 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Two-column balanced layout: Form on left (2/5), Table on right (3/5) with proper constraints -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Left Column: Certificate Generation Form (2 columns) -->
        <div class="lg:col-span-2">
            <!-- Card component often used in your system -->
            <div class="card h-fit">
                <div class="mb-6 pb-4 border-b" style="border-color: var(--border-color);">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <!-- Đảm bảo kích thước icon H2 là w-5 h-5 -->
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('messages.generate_new_certificate') }}
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">{{ __('messages.create_new_digital_certificate') }}</p>
                </div>

                <form action="{{ route('certificates.generate') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Replaced Select Box with Radio Buttons for Key Size -->
                    <div class="form-group">
                        <label class="form-label">
                            {{ __('messages.key_size') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-opacity-10 hover:bg-blue-500 transition-all" style="border-color: var(--border-color);">
                                <input type="radio" name="key_size" value="2048" checked class="w-4 h-4 text-blue-600">
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-white text-sm">2048 bits</span>
                                        <span class="badge badge-info text-xs">{{ __('messages.standard') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ __('messages.recommended_for_most_use_cases') }}</p>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-opacity-10 hover:bg-blue-500 transition-all" style="border-color: var(--border-color);">
                                <input type="radio" name="key_size" value="4096" class="w-4 h-4 text-blue-600">
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-semibold text-white text-sm">4096 bits</span>
                                        <span class="badge badge-success text-xs">{{ __('messages.high_security') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ __('messages.maximum_security_slower_performance') }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Valid Days -->
                    <div class="form-group">
                        <label for="valid_days" class="form-label">
                            {{ __('messages.valid_for_days') }} <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="valid_days"
                            name="valid_days"
                            value="365"
                            min="30"
                            max="3650"
                            class="form-input"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">{{ __('messages.certificate_validity_period') }}</p>
                    </div>

                    <!-- Certificate Usage -->
                    <div class="form-group">
                        <label for="certificate_usage" class="form-label">
                            {{ __('messages.certificate_usage') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="certificate_usage" name="certificate_usage" class="form-select" required>
                            <option value="signing">{{ __('messages.signing_only') }}</option>
                            <option value="encryption">{{ __('messages.encryption_only') }}</option>
                            <option value="both">{{ __('messages.both_signing_encryption') }}</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">{{ __('messages.define_certificate_usage') }}</p>
                    </div>

                    <!-- CRL URL -->
                    <div class="form-group">
                        <label for="crl_url" class="form-label">
                            CRL URL <span class="text-gray-500 text-xs">(Optional)</span>
                        </label>
                        <input
                            type="url"
                            id="crl_url"
                            name="crl_url"
                            value="{{ old('crl_url') }}"
                            placeholder="https://example.com/crl"
                            class="form-input"
                        >
                        <p class="text-xs text-gray-500 mt-1">Certificate Revocation List URL</p>
                    </div>

                    <!-- OCSP URL -->
                    <div class="form-group">
                        <label for="ocsp_url" class="form-label">
                            OCSP URL <span class="text-gray-500 text-xs">(Optional)</span>
                        </label>
                        <input
                            type="url"
                            id="ocsp_url"
                            name="ocsp_url"
                            value="{{ old('ocsp_url') }}"
                            placeholder="https://example.com/ocsp"
                            class="form-input"
                        >
                        <p class="text-xs text-gray-500 mt-1">Online Certificate Status Protocol URL</p>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            Password (for confirmation) <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Enter your account password"
                            required
                        >
                        <p class="text-xs text-gray-500 mt-1">Enter your account password to confirm</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <!-- Đảm bảo kích thước icon button là w-4 h-4 -->
                        <button type="submit" class="btn btn-primary w-full flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            {{ __('messages.generate_certificate') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Certificate Management Table (3 columns) -->
        <div class="lg:col-span-3">
            <div class="card">
                <div class="mb-6 pb-4 border-b" style="border-color: var(--border-color);">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <!-- Đảm bảo kích thước icon H2 là w-5 h-5 -->
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('messages.certificate_management') }}
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">{{ __('messages.view_manage_certificates') }}</p>
                </div>

                @if($certificates && $certificates->count() > 0)
                    <!-- Search and Filter Bar -->
                    <div class="mb-4 flex flex-col sm:flex-row gap-3">
                        <div class="flex-1 relative">
                            <input
                                type="text"
                                id="searchCertificates"
                                placeholder="{{ __('messages.search_certificates') }}"
                                class="form-input pl-10"
                            >
                            <!-- Đảm bảo kích thước icon input là w-4 h-4 -->
                            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <select id="filterStatus" class="form-select sm:w-40">
                            <option value="">{{ __('messages.all_status') }}</option>
                            <option value="active">{{ __('messages.active') }}</option>
                            <option value="expired">{{ __('messages.expired') }}</option>
                            <option value="revoked">{{ __('messages.revoked') }}</option>
                        </select>
                    </div>

                    <!-- Data Table -->
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('messages.id') }}</th>
                                    <th>{{ __('messages.issued_to') }}</th>
                                    <th>{{ __('messages.key_size') }}</th>
                                    <th>{{ __('messages.valid_until') }}</th>
                                    <th>{{ __('messages.usage') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="certificatesTableBody">
                                @foreach($certificates as $cert)
                                    <tr class="certificate-row" 
                                        data-status="{{ $cert->is_revoked ? 'revoked' : ($cert->expires_at < now() ? 'expired' : 'active') }}"
                                        data-search="{{ strtolower($cert->subject . ' ' . $cert->certificate_usage) }}">
                                        <td class="text-sm font-mono">#{{ $cert->id }}</td>
                                        <td class="text-sm">
                                            <div class="font-medium text-white">{{ $cert->subject }}</div>
                                            <div class="text-xs text-gray-400">{{ $cert->issuer }}</div>
                                        </td>
                                        <td class="text-sm">
                                            <span class="font-semibold">{{ $cert->key_size }}</span> bits
                                        </td>
                                        <td class="text-sm">
                                            <div class="text-white">{{ $cert->expires_at->format('M d, Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $cert->expires_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="text-sm">
                                            @if($cert->certificate_usage === 'signing')
                                                <span class="badge badge-info">{{ __('messages.signing') }}</span>
                                            @elseif($cert->certificate_usage === 'encryption')
                                                <span class="badge badge-success">{{ __('messages.encryption') }}</span>
                                            @else
                                                <span class="badge badge-info">{{ __('messages.both') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-sm">
                                            @if($cert->is_revoked)
                                                <span class="badge badge-error">{{ __('messages.revoked') }}</span>
                                            @elseif($cert->expires_at < now())
                                                <span class="badge badge-warning">{{ __('messages.expired') }}</span>
                                            @else
                                                <span class="badge badge-success">{{ __('messages.active') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-sm">
                                            <div class="flex items-center gap-2">
                                                @if(!$cert->is_revoked && $cert->expires_at > now())
                                                    <button
                                                        onclick="openRevokeModal({{ $cert->id }})"
                                                        class="btn btn-secondary text-xs py-1 px-2"
                                                        title="{{ __('messages.revoke_certificate') }}"
                                                    >
                                                        {{ __('messages.revoke') }}
                                                    </button>
                                                @endif
                                                <a
                                                    href="{{ route('certificates.download-public-key', $cert->id) }}"
                                                    class="btn btn-primary text-xs py-1 px-2"
                                                    title="{{ __('messages.download_public_key') }}"
                                                >
                                                    {{ __('messages.download') }}
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State for Filtered Results -->
                    <div id="noResults" class="hidden text-center py-12">
                        <p class="text-gray-400">{{ __('messages.no_certificates_found') }}</p>
                        <p class="text-gray-500 text-sm mt-1">{{ __('messages.try_adjusting_search_filter') }}</p>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <!-- Đảm bảo kích thước icon empty state là w-16 h-16 -->
                        <div class="text-gray-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">{{ __('messages.no_certificates_yet') }}</h3>
                        <p class="text-gray-400 text-sm">{{ __('messages.generate_first_certificate') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Revoke Modal -->
<div id="revokeModal" class="hidden fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 p-4">
    <div class="card max-w-md w-full">
        <div class="mb-6 pb-4 border-b" style="border-color: var(--border-color);">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <!-- Đảm bảo kích thước icon modal heading là w-4 h-4 -->
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                {{ __('messages.revoke_certificate') }}
            </h3>
            <p class="text-gray-400 text-sm mt-1">{{ __('messages.this_action_cannot_be_undone') }}</p>
        </div>

        <form action="{{ route('certificates.revoke') }}" method="POST">
            @csrf
            <input type="hidden" id="revokeCertificateId" name="certificate_id">

            <div class="form-group">
                <label for="revoke_reason" class="form-label">
                    {{ __('messages.reason_for_revocation') }} <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="revoke_reason"
                    name="reason"
                    rows="4"
                    class="form-textarea"
                    placeholder="Please provide a detailed reason..."
                    required
                ></textarea>
            </div>

            <div class="form-group">
                <label for="revoke_password" class="form-label">
                    Password <span class="text-red-500">*</span>
                </label>
                <input
                    type="password"
                    id="revoke_password"
                    name="password"
                    class="form-input"
                    placeholder="Enter your password to confirm"
                    required
                >
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary flex-1">
                    {{ __('messages.revoke_certificate') }}
                </button>
                <button type="button" onclick="closeRevokeModal()" class="btn btn-secondary flex-1">
                    {{ __('messages.cancel') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Search functionality
document.getElementById('searchCertificates')?.addEventListener('input', filterCertificates);
document.getElementById('filterStatus')?.addEventListener('change', filterCertificates);

function filterCertificates() {
    const searchTerm = document.getElementById('searchCertificates')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('filterStatus')?.value || '';
    const rows = document.querySelectorAll('.certificate-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const searchData = row.getAttribute('data-search') || '';
        const status = row.getAttribute('data-status') || '';
        
        const matchesSearch = searchData.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            row.classList.remove('hidden');
            visibleCount++;
        } else {
            row.classList.add('hidden');
        }
    });

    const noResults = document.getElementById('noResults');
    if (noResults) {
        noResults.classList.toggle('hidden', visibleCount > 0);
    }
}

// Modal functions
function openRevokeModal(certificateId) {
    document.getElementById('revokeCertificateId').value = certificateId;
    document.getElementById('revokeModal').classList.remove('hidden');
}

function closeRevokeModal() {
    document.getElementById('revokeModal').classList.add('hidden');
    document.getElementById('revoke_reason').value = '';
    document.getElementById('revoke_password').value = '';
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRevokeModal();
});

// Close modal on backdrop click
document.getElementById('revokeModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeRevokeModal();
});
</script>
@endsection
