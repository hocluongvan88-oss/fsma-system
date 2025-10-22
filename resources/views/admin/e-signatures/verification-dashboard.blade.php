@extends('layouts.app')

@section('title', __('messages.signature_verification_dashboard'))

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('messages.signature_verification_dashboard') }}</h1>
    <p style="color: var(--text-secondary);">{{ __('messages.comprehensive_signature_verification_status') }}</p>
</div>

<!-- Signature Statistics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.total_signatures') }}</div>
        <div style="font-size: 2rem; font-weight: 700;">{{ $stats['total_signatures'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.verified_signatures') }}</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--success);">{{ $stats['verified_signatures'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.with_timestamp') }}</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--info);">{{ $stats['timestamped_signatures'] }}</div>
    </div>
    
    <div class="card">
        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 0.5rem;">{{ __('messages.ltv_enabled') }}</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">{{ $stats['ltv_signatures'] }}</div>
    </div>
</div>

<!-- Verification Status Breakdown -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.verification_status') }}</h2>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.valid_signatures') }}</span>
                <span class="badge badge-success">{{ $stats['valid_signatures'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.invalid_signatures') }}</span>
                <span class="badge badge-danger">{{ $stats['invalid_signatures'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.pending_verification') }}</span>
                <span class="badge badge-warning">{{ $stats['pending_signatures'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.revoked_signatures') }}</span>
                <span class="badge badge-secondary">{{ $stats['revoked_signatures'] }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.signature_attributes') }}</h2>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.xades_compliant') }}</span>
                <span class="badge badge-success">{{ $stats['xades_compliant'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.ltv_capable') }}</span>
                <span class="badge badge-info">{{ $stats['ltv_capable'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.timestamp_verified') }}</span>
                <span class="badge badge-success">{{ $stats['timestamp_verified'] }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ __('messages.certificate_valid') }}</span>
                <span class="badge badge-success">{{ $stats['certificate_valid'] }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Signatures with Verification Details -->
<div class="card">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.recent_signatures') }}</h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.signed_at') }}</th>
                    <th>{{ __('messages.user') }}</th>
                    <th>{{ __('messages.record_type') }}</th>
                    <th>{{ __('messages.verification_status') }}</th>
                    <th>{{ __('messages.xades') }}</th>
                    <th>{{ __('messages.timestamp') }}</th>
                    <th>{{ __('messages.ltv') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentSignatures as $signature)
                <tr>
                    <td style="font-size: 0.875rem;">{{ $signature->signed_at->format('Y-m-d H:i:s') }}</td>
                    <td>
                        <strong>{{ $signature->user->full_name }}</strong>
                        <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ $signature->user->email }}</div>
                    </td>
                    <td style="font-size: 0.875rem;">{{ $signature->record_type }}</td>
                    <td>
                        <span class="badge {{ $signature->is_valid ? 'badge-success' : 'badge-danger' }}">
                            {{ $signature->is_valid ? __('messages.valid') : __('messages.invalid') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $signature->is_xades_compliant ? 'badge-success' : 'badge-warning' }}">
                            {{ $signature->is_xades_compliant ? __('messages.yes') : __('messages.no') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $signature->timestamp_verified_at ? 'badge-success' : 'badge-secondary' }}">
                            {{ $signature->timestamp_verified_at ? __('messages.verified') : __('messages.none') }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $signature->ltv_enabled ? 'badge-success' : 'badge-secondary' }}">
                            {{ $signature->ltv_enabled ? __('messages.enabled') : __('messages.disabled') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.e-signatures.show', $signature) }}" style="color: var(--primary); text-decoration: none;">
                            {{ __('messages.view') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                        {{ __('messages.no_signatures_found') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
