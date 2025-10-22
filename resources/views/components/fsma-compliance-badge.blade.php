<!-- FSMA 204 Compliance Badge Component -->
<div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: {{ $compliant ? 'var(--success)' : 'var(--danger)' }}; color: white; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 600;">
    <span>{{ $compliant ? '✓' : '✕' }}</span>
    <span>{{ $compliant ? __('messages.fsma_204_compliant') : __('messages.fsma_204_non_compliant') }}</span>
    @if($details)
    <span style="margin-left: 0.5rem; opacity: 0.8; font-size: 0.75rem;">{{ $details }}</span>
    @endif
</div>
