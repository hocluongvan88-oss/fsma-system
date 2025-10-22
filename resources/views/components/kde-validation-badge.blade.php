<!-- KDE Validation Badge Component -->
<div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: {{ $status === 'valid' ? 'var(--success)' : ($status === 'warning' ? 'var(--warning)' : 'var(--danger)') }}; color: white; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600;">
    <span>{{ $status === 'valid' ? '✓' : ($status === 'warning' ? '⚡' : '✕') }}</span>
    <span>
        {{ $status === 'valid' ? __('messages.kde_compliant') : ($status === 'warning' ? __('messages.kde_warning') : __('messages.kde_non_compliant')) }}
    </span>
    @if($count)
    <span style="margin-left: 0.5rem; opacity: 0.8; font-size: 0.75rem;">({{ $count }}/27)</span>
    @endif
</div>
