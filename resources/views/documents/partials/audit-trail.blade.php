<!-- Audit Trail Section -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 1.5rem;">
        <!-- Added i18n for title -->
        <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem;">{{ __('messages.audit_trail') }}</h3>
        
        <div style="border-left: 2px solid var(--border-color); padding-left: 1rem;">
            <!-- Created -->
            <div style="margin-bottom: 1rem; position: relative;">
                <div style="position: absolute; left: -1.5rem; top: 0; width: 12px; height: 12px; background: var(--accent-primary); border-radius: 50%; border: 2px solid var(--bg-secondary);"></div>
                <div style="font-weight: 600; color: var(--text-primary);">{{ __('messages.created') }}</div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">
                    {{ $document->created_at->format('Y-m-d H:i') }} {{ __('messages.by') }} {{ $document->uploader->name }}
                </div>
            </div>

            <!-- Updated -->
            @if($document->updated_at->ne($document->created_at))
            <div style="margin-bottom: 1rem; position: relative;">
                <div style="position: absolute; left: -1.5rem; top: 0; width: 12px; height: 12px; background: var(--accent-primary); border-radius: 50%; border: 2px solid var(--bg-secondary);"></div>
                <div style="font-weight: 600; color: var(--text-primary);">{{ __('messages.updated_at') }}</div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">
                    {{ $document->updated_at->format('Y-m-d H:i') }}
                </div>
            </div>
            @endif

            <!-- Approved -->
            @if($document->approved_at)
            <div style="margin-bottom: 1rem; position: relative;">
                <div style="position: absolute; left: -1.5rem; top: 0; width: 12px; height: 12px; background: var(--success); border-radius: 50%; border: 2px solid var(--bg-secondary);"></div>
                <div style="font-weight: 600; color: var(--text-primary);">{{ __('messages.approved') }}</div>
                <div style="font-size: 0.875rem; color: var(--text-secondary);">
                    {{ $document->approved_at->format('Y-m-d H:i') }} {{ __('messages.by') }} {{ $document->approver->name }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
