<!-- Quantity Validation Indicator Component -->
<div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: var(--bg-tertiary); border-radius: 0.375rem; border-left: 3px solid {{ $isValid ? 'var(--success)' : 'var(--danger)' }};">
    <div style="font-size: 1.25rem;">{{ $isValid ? '✓' : '✕' }}</div>
    <div style="flex: 1;">
        <div style="font-size: 0.875rem; font-weight: 600; color: {{ $isValid ? 'var(--success)' : 'var(--danger)' }};">
            {{ $isValid ? __('messages.quantity_valid') : __('messages.quantity_invalid') }}
        </div>
        @if($message)
        <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">{{ $message }}</div>
        @endif
    </div>
    @if($percentage)
    <div style="text-align: right;">
        <div style="font-size: 1.25rem; font-weight: 700; color: var(--primary);">{{ $percentage }}%</div>
        <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ __('messages.yield') }}</div>
    </div>
    @endif
</div>
