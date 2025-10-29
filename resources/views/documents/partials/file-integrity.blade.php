<!-- File Integrity & Encryption Section -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 1.5rem;">
        <!-- Added i18n for title -->
        <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem;">{{ __('messages.file_integrity') }}</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <!-- File Integrity -->
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.25rem;">🔐</span>
                    <strong style="color: var(--text-primary);">{{ __('messages.file_integrity') }}</strong>
                </div>
                @if($document->file_hash)
                <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.5rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">
                    {{ __('messages.integrity_verified') }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-secondary); word-break: break-all;">
                    {{ substr($document->file_hash, 0, 32) }}...
                </div>
                @else
                <div style="background: rgba(160, 160, 160, 0.1); color: var(--text-secondary); padding: 0.5rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600;">
                    {{ __('messages.not_encrypted') }}
                </div>
                @endif
            </div>

            <!-- Encryption Status -->
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.25rem;">🔒</span>
                    <strong style="color: var(--text-primary);">{{ __('messages.encryption_status') }}</strong>
                </div>
                @if($document->is_encrypted)
                <div style="background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 0.5rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">
                    {{ __('messages.encrypted') }}
                </div>
                <div style="font-size: 0.75rem; color: var(--text-secondary);">
                    {{ $document->encrypted_at->format('Y-m-d H:i') }}
                </div>
                @else
                <div style="background: rgba(245, 158, 11, 0.1); color: var(--warning); padding: 0.5rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600;">
                    {{ __('messages.not_encrypted') }}
                </div>
                @endif
            </div>

            <!-- File Info -->
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                    <span style="font-size: 1.25rem;">📄</span>
                    <strong style="color: var(--text-primary);">{{ __('messages.file') }}</strong>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-secondary);">
                    <div>{{ __('messages.type') }}: {{ $document->file_type }}</div>
                    <div>{{ __('messages.file_size') }}: {{ $document->file_size_human }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
