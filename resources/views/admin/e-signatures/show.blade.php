@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header with breadcrumb -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <!-- Fixed route name from e-signatures.index to admin.e-signatures.index -->
                <a href="{{ route('admin.e-signatures.index') }}" style="color: var(--accent-primary); text-decoration: none;">{{ __('messages.e_signatures') }}</a>
                <span style="color: var(--text-muted); margin: 0 0.5rem;">/</span>
                <span style="color: var(--text-primary);">{{ __('messages.signature_details') }}</span>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                @if(!$signature->is_revoked && (auth()->user()->id === $signature->user_id || auth()->user()->is_admin))
                    <button onclick="openEnhancedRevokeModal()" class="btn btn-danger">{{ __('messages.revoke') }}</button>
                @endif
            </div>
        </div>

        <!-- Status Banner -->
        <div style="margin-bottom: 2rem;">
            @if($signature->is_revoked)
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 0.5rem; padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 1.5rem;">⚠️</span>
                    <div>
                        <p style="font-weight: 600; color: var(--error); margin: 0;">{{ __('messages.signature_revoked') }}</p>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.25rem 0 0 0;">
                            @if($signature->revoked_at)
                                {{ __('messages.revoked_on') }} {{ $signature->revoked_at->format('Y-m-d H:i:s') }}
                            @endif
                            @if($signature->revocation_reason)
                                - {{ $signature->revocation_reason }}
                            @endif
                        </p>
                    </div>
                </div>
            @elseif($signature->expiration_status === 'expired')
                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 0.5rem; padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 1.5rem;">⏰</span>
                    <div>
                        <p style="font-weight: 600; color: var(--warning); margin: 0;">{{ __('messages.signature_expired') }}</p>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.25rem 0 0 0;">
                            @if($signature->signature_expires_at)
                                {{ __('messages.expired_on') }} {{ $signature->signature_expires_at->format('Y-m-d H:i:s') }}
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 0.5rem; padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 1.5rem;">✓</span>
                    <div>
                        <p style="font-weight: 600; color: var(--success); margin: 0;">{{ __('messages.valid_signature') }}</p>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.25rem 0 0 0;">
                            @if($signature->signature_valid_until)
                                {{ __('messages.valid_until') }} {{ $signature->signature_valid_until->format('Y-m-d H:i:s') }}
                            @endif
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Main Content Grid -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- Left Column: Signature Details -->
            <div>
                <!-- Basic Information Card -->
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">{{ __('messages.basic_information') }}</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.signed_by') }}</p>
                            <p style="font-weight: 600; margin: 0;">{{ $signature->user->full_name ?? $signature->user->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.signed_at') }}</p>
                            <p style="font-family: monospace; font-size: 0.875rem; margin: 0;">
                                @if($signature->signed_at)
                                    {{ $signature->signed_at->format('Y-m-d H:i:s') }}
                                @else
                                    <span style="color: var(--text-muted);">{{ __('messages.not_signed_yet') }}</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.record_type') }}</p>
                            <p style="font-family: monospace; font-size: 0.875rem; margin: 0;">{{ $signature->record_type ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.record_id') }}</p>
                            <p style="font-family: monospace; font-size: 0.875rem; margin: 0;">{{ $signature->record_id ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.action') }}</p>
                            <p style="font-weight: 600; margin: 0;">{{ $signature->action ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.algorithm') }}</p>
                            <p style="font-family: monospace; font-size: 0.875rem; margin: 0;">{{ $signature->signature_algorithm ?? 'SHA512' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Meaning of Signature Card -->
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">{{ __('messages.meaning_of_signature') }}</h3>
                    <p style="background: var(--bg-tertiary); padding: 1rem; border-radius: 0.5rem; margin: 0; color: var(--text-primary);">{{ $signature->meaning_of_signature }}</p>
                </div>

                @if($signature->reason)
                    <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">{{ __('messages.reason') }}</h3>
                        <p style="background: var(--bg-tertiary); padding: 1rem; border-radius: 0.5rem; margin: 0; color: var(--text-primary);">{{ $signature->reason }}</p>
                    </div>
                @endif

                <!-- Security Information Card -->
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">{{ __('messages.security_information') }}</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.ip_address') }}</p>
                            <p style="font-family: monospace; font-size: 0.875rem; margin: 0;">{{ $signature->ip_address }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.mfa_method') }}</p>
                            <p style="font-weight: 600; margin: 0;">
                                @if($signature->mfa_method)
                                    <span style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem;">{{ strtoupper($signature->mfa_method) }}</span>
                                @else
                                    <span style="color: var(--text-muted);">{{ __('messages.none') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Validity Period Card -->
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">{{ __('messages.validity_period') }}</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.valid_from') }}</p>
                            <p style="font-family: monospace; font-size: 0.875rem; margin: 0;">
                                @if($signature->signature_valid_from)
                                    {{ $signature->signature_valid_from->format('Y-m-d H:i:s') }}
                                @else
                                    <span style="color: var(--text-muted);">N/A</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.valid_until') }}</p>
                            <p style="font-family: monospace; font-size: 0.875rem; margin: 0;">
                                @if($signature->signature_valid_until)
                                    {{ $signature->signature_valid_until->format('Y-m-d H:i:s') }}
                                @else
                                    <span style="color: var(--text-muted);">N/A</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.validity_period') }}</p>
                            <p style="font-weight: 600; margin: 0;">{{ $signature->signature_validity_period_days ?? 'N/A' }} {{ __('messages.days') }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin: 0 0 0.5rem 0;">{{ __('messages.expires_at') }}</p>
                            <p style="font-family: monospace; font-size: 0.875rem; margin: 0;">
                                @if($signature->signature_expires_at)
                                    {{ $signature->signature_expires_at->format('Y-m-d H:i:s') }}
                                @else
                                    <span style="color: var(--text-muted);">N/A</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Summary & Quick Info -->
            <div>
                <!-- Summary Card -->
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem; position: sticky; top: 1rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">{{ __('messages.summary') }}</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.status') }}</span>
                            <span style="font-weight: 600;">
                                @if($signature->is_revoked)
                                    <span style="background: rgba(239, 68, 68, 0.1); color: var(--error); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem;">{{ __('messages.revoked') }}</span>
                                @elseif($signature->expiration_status === 'expired')
                                    <span style="background: rgba(245, 158, 11, 0.1); color: var(--warning); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem;">{{ __('messages.expired') }}</span>
                                @else
                                    <span style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem;">{{ __('messages.active') }}</span>
                                @endif
                            </span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.timestamp') }}</span>
                            <span style="font-weight: 600;">
                                @if($signature->timestamp_verified_at)
                                    <span style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem;">{{ __('messages.verified') }}</span>
                                @else
                                    <span style="background: rgba(107, 114, 128, 0.1); color: var(--text-muted); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem;">{{ __('messages.none') }}</span>
                                @endif
                            </span>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
                            <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.verification') }}</span>
                            <span style="font-weight: 600;">
                                @if($signature->verification_passed)
                                    <span style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem;">{{ __('messages.passed') }}</span>
                                @else
                                    <span style="background: rgba(239, 68, 68, 0.1); color: var(--error); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem;">{{ __('messages.failed') }}</span>
                                @endif
                            </span>
                        </div>

                        @if($signature->timestamp_provider)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
                                <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.tsa_provider') }}</span>
                                <span style="font-family: monospace; font-size: 0.75rem; font-weight: 600;">{{ $signature->timestamp_provider }}</span>
                            </div>
                        @endif

                        @if($signature->is_delegated_signature)
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
                                <span style="color: var(--text-secondary); font-size: 0.875rem;">{{ __('messages.delegated_by') }}</span>
                                <span style="font-weight: 600;">{{ $signature->delegatedByUser->full_name ?? 'N/A' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Hashes Card -->
                <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">{{ __('messages.hashes') }}</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.75rem; margin: 0 0 0.5rem 0;">{{ __('messages.signature_hash') }}</p>
                            <p style="font-family: monospace; font-size: 0.65rem; word-break: break-all; margin: 0; background: var(--bg-tertiary); padding: 0.75rem; border-radius: 0.5rem; color: var(--text-muted);">{{ $signature->signature_hash }}</p>
                        </div>
                        <div>
                            <p style="color: var(--text-secondary); font-size: 0.75rem; margin: 0 0 0.5rem 0;">{{ __('messages.record_content_hash') }}</p>
                            <p style="font-family: monospace; font-size: 0.65rem; word-break: break-all; margin: 0; background: var(--bg-tertiary); padding: 0.75rem; border-radius: 0.5rem; color: var(--text-muted);">{{ $signature->record_content_hash }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Added Revocation History Section -->
        @if($signature->revocations && $signature->revocations->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">{{ __('messages.revocation_history') }}</h2>
            
            @foreach($signature->revocations as $revocation)
            <div class="border-l-4 border-red-500 pl-4 mb-4 pb-4 {{ !$loop->last ? 'border-b' : '' }}">
                <div class="grid grid-cols-2 gap-4 mb-3">
                    <div>
                        <p class="text-sm text-gray-600">{{ __('messages.revoked_by') }}</p>
                        <p class="font-semibold">{{ $revocation->revokedBy->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ __('messages.revoked_at') }}</p>
                        <p class="font-mono text-sm">{{ $revocation->revoked_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ __('messages.category') }}</p>
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full 
                            {{ $revocation->revocation_category === 'security_breach' ? 'bg-red-100 text-red-800' : 
                               ($revocation->revocation_category === 'compliance' ? 'bg-yellow-100 text-yellow-800' : 
                               'bg-gray-100 text-gray-800') }}">
                            {{ ucwords(str_replace('_', ' ', $revocation->revocation_category)) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">{{ __('messages.ip_address') }}</p>
                        <p class="font-mono text-sm">{{ $revocation->ip_address }}</p>
                    </div>
                </div>
                
                <div class="mb-3">
                    <p class="text-sm text-gray-600 mb-1">{{ __('messages.revocation_reason') }}</p>
                    <p class="bg-gray-50 p-3 rounded text-sm">{{ $revocation->revocation_reason }}</p>
                </div>
                
                @if($revocation->additional_notes)
                <div>
                    <p class="text-sm text-gray-600 mb-1">{{ __('messages.additional_notes') }}</p>
                    <p class="bg-gray-50 p-3 rounded text-sm">{{ $revocation->additional_notes }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Actions -->
        <div class="flex gap-4 mb-6">
            <!-- Fixed route name from e-signatures.index to admin.e-signatures.index -->
            <a href="{{ route('admin.e-signatures.index') }}" class="text-blue-600 hover:text-blue-800">
                {{ __('messages.back_to_e_signatures') }}
            </a>
            @if(!$signature->is_revoked && (auth()->user()->id === $signature->user_id || auth()->user()->is_admin))
                <button onclick="openEnhancedRevokeModal()" class="text-red-600 hover:text-red-800">
                    {{ __('messages.revoke_signature') }}
                </button>
            @endif
        </div>
    </div>
</div>

<!-- Enhanced Revoke Modal with Categories -->
@if(!$signature->is_revoked && (auth()->user()->id === $signature->user_id || auth()->user()->is_admin))
<div id="enhancedRevokeModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>{{ __('messages.revoke_e_signature') }}</h3>
            <button type="button" class="modal-close" onclick="closeEnhancedRevokeModal()">&times;</button>
        </div>
        
        <form id="enhancedRevokeForm" onsubmit="submitEnhancedRevoke(event)">
            @csrf
            
            <div class="alert alert-warning">
                <strong>{{ __('messages.warning') }}:</strong> {{ __('messages.revoke_signature_warning') }}
            </div>
            
            <div class="signature-preview">
                <div class="preview-row">
                    <span class="preview-label">{{ __('messages.signed_by') }}:</span>
                    <span class="preview-value">{{ $signature->user->full_name ?? $signature->user->name ?? 'N/A' }}</span>
                </div>
                <div class="preview-row">
                    <span class="preview-label">{{ __('messages.signed_at') }}:</span>
                    <span class="preview-value">
                        @if($signature->signed_at)
                            {{ $signature->signed_at->format('Y-m-d H:i:s') }}
                        @else
                            <span style="color: var(--text-muted);">{{ __('messages.not_signed_yet') }}</span>
                        @endif
                    </span>
                </div>
                <div class="preview-row">
                    <span class="preview-label">{{ __('messages.record') }}:</span>
                    <span class="preview-value">{{ $signature->record_type }} #{{ $signature->record_id }}</span>
                </div>
                <div class="preview-row">
                    <span class="preview-label">{{ __('messages.action') }}:</span>
                    <span class="preview-value">{{ $signature->action ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="form-group">
                <label for="revocationCategory">{{ __('messages.revocation_category') }} *</label>
                <select id="revocationCategory" name="revocation_category" class="form-control" required>
                    <option value="">{{ __('messages.select_a_category') }}</option>
                    <option value="user_request">{{ __('messages.user_request') }}</option>
                    <option value="security_breach">{{ __('messages.security_breach') }}</option>
                    <option value="data_modification">{{ __('messages.data_modification') }}</option>
                    <option value="compliance">{{ __('messages.compliance_issue') }}</option>
                    <option value="other">{{ __('messages.other') }}</option>
                </select>
                <small class="text-muted">{{ __('messages.select_primary_reason_revocation') }}</small>
            </div>

            <div class="form-group">
                <label for="revocationReason">{{ __('messages.revocation_reason') }} *</label>
                <textarea id="revocationReason" name="revocation_reason" class="form-control" 
                          placeholder="{{ __('messages.provide_detailed_explanation') }}" 
                          maxlength="1000" rows="4" required></textarea>
                <small class="text-muted">{{ __('messages.recorded_audit_trail_visible_admins') }}</small>
            </div>

            <div class="form-group">
                <label for="additionalNotes">{{ __('messages.additional_notes_optional') }}</label>
                <textarea id="additionalNotes" name="additional_notes" class="form-control" 
                          placeholder="{{ __('messages.any_additional_context') }}" 
                          maxlength="1000" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label for="revokePassword">{{ __('messages.your_password') }} *</label>
                <input type="password" id="revokePassword" name="password" class="form-control" 
                       placeholder="{{ __('messages.confirm_with_password') }}" required>
                <small class="text-muted">{{ __('messages.required_to_authorize_revocation') }}</small>
            </div>

            <div class="form-group">
                <div class="checkbox">
                    <input type="checkbox" id="confirmRevoke" required>
                    <label for="confirmRevoke">
                        {{ __('messages.confirm_revoke_signature') }}
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEnhancedRevokeModal()">{{ __('messages.cancel') }}</button>
                <button type="submit" class="btn btn-danger" id="revokeBtn">{{ __('messages.revoke_signature') }}</button>
            </div>
        </form>

        <div id="revokeStatus" style="display: none; margin-top: 1rem;">
            <div id="revokeMessage" class="alert"></div>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1rem;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
}

.modal-close:hover {
    color: #000;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

textarea.form-control {
    resize: vertical;
}

.checkbox {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.checkbox input[type="checkbox"] {
    margin-top: 0.25rem;
}

.checkbox label {
    margin: 0;
    font-weight: normal;
    font-size: 0.9rem;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-warning {
    background-color: #fef3c7;
    border: 1px solid #fcd34d;
    color: #92400e;
}

.alert-success {
    background-color: #dcfce7;
    border: 1px solid #86efac;
    color: #166534;
}

.alert-error {
    background-color: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}

.signature-preview {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.preview-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.preview-row:last-child {
    border-bottom: none;
}

.preview-label {
    font-weight: 600;
    color: #374151;
    min-width: 120px;
}

.preview-value {
    color: #6b7280;
    text-align: right;
    flex: 1;
}

.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    font-weight: 500;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
}

.btn-danger {
    background-color: #ef4444;
    color: white;
}

.btn-danger:hover {
    background-color: #dc2626;
}

.btn-danger:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}

small.text-muted {
    display: block;
    margin-top: 0.25rem;
    color: #6b7280;
    font-size: 0.875rem;
}
</style>

<script>
function openEnhancedRevokeModal() {
    document.getElementById('enhancedRevokeModal').style.display = 'flex';
}

function closeEnhancedRevokeModal() {
    document.getElementById('enhancedRevokeModal').style.display = 'none';
    document.getElementById('enhancedRevokeForm').reset();
    document.getElementById('revokeStatus').style.display = 'none';
}

async function submitEnhancedRevoke(event) {
    event.preventDefault();
    
    const revokeBtn = document.getElementById('revokeBtn');
    revokeBtn.disabled = true;
    revokeBtn.textContent = '{{ __("messages.revoking") }}...';
    
    const formData = new FormData(document.getElementById('enhancedRevokeForm'));
    
    const data = {
        signature_id: {{ $signature->id }},
        revocation_category: formData.get('revocation_category'),
        revocation_reason: formData.get('revocation_reason'),
        additional_notes: formData.get('additional_notes'),
        password: formData.get('password')
    };
    
    try {
        const response = await fetch('{{ route("admin.e-signatures.revoke") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showRevokeStatus('alert-success', '{{ __("messages.signature_revoked_successfully") }}');
            setTimeout(() => {
                closeEnhancedRevokeModal();
                location.reload();
            }, 2000);
        } else {
            showRevokeStatus('alert-error', result.message || '{{ __("messages.failed_to_revoke_signature") }}');
        }
    } catch (error) {
        showRevokeStatus('alert-error', '{{ __("messages.an_error_occurred") }}: ' + error.message);
    } finally {
        revokeBtn.disabled = false;
        revokeBtn.textContent = '{{ __("messages.revoke_signature") }}';
    }
}

function showRevokeStatus(type, message) {
    const statusDiv = document.getElementById('revokeStatus');
    const messageDiv = document.getElementById('revokeMessage');
    
    messageDiv.className = 'alert ' + type;
    messageDiv.textContent = message;
    statusDiv.style.display = 'block';
}

window.onclick = function(event) {
    const modal = document.getElementById('enhancedRevokeModal');
    if (event.target === modal) {
        closeEnhancedRevokeModal();
    }
}
</script>
@endif
@endsection
