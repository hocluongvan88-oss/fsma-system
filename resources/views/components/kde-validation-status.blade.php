<!-- KDE Validation Status Component -->
<div style="padding: 1.5rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid {{ $status === 'valid' ? 'var(--success)' : ($status === 'warning' ? 'var(--warning)' : 'var(--danger)') }};">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="font-size: 1rem; font-weight: 600; margin: 0;">{{ __('messages.fsma_204_kde_validation') }}</h3>
        <span class="badge {{ $status === 'valid' ? 'badge-success' : ($status === 'warning' ? 'badge-warning' : 'badge-danger') }}">
            {{ $status === 'valid' ? __('messages.compliant') : ($status === 'warning' ? __('messages.warning') : __('messages.non_compliant')) }}
        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
        @foreach($kdeChecks as $kde => $result)
        <div style="padding: 0.75rem; background: var(--bg-secondary); border-radius: 0.375rem; border-left: 3px solid {{ $result['valid'] ? 'var(--success)' : 'var(--danger)' }};">
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem; text-transform: uppercase;">{{ $kde }}</div>
            <div style="font-size: 0.875rem; font-weight: 600; color: {{ $result['valid'] ? 'var(--success)' : 'var(--danger)' }};">
                {{ $result['valid'] ? '✓ ' . __('messages.valid') : '✕ ' . __('messages.invalid') }}
            </div>
            @if($result['message'])
            <div style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem;">{{ $result['message'] }}</div>
            @endif
        </div>
        @endforeach
    </div>

    @if($errors && count($errors) > 0)
    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
        <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--danger); margin-bottom: 0.5rem;">{{ __('messages.validation_errors') }}</h4>
        <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.875rem; color: var(--text-secondary);">
            @foreach($errors as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
