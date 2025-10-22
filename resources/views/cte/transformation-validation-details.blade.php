<!-- Transformation Validation Details Modal/Panel -->
<div style="padding: 1.5rem; background: var(--bg-tertiary); border-radius: 0.5rem; margin-bottom: 1.5rem;">
    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">{{ __('messages.transformation_validation_details') }}</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <!-- Input Validation -->
        <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 0.375rem;">
            <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-secondary);">{{ __('messages.input_validation') }}</div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">{{ $validation['inputs_valid'] ? '✓' : '✕' }}</span>
                <span style="color: {{ $validation['inputs_valid'] ? 'var(--success)' : 'var(--danger)' }}; font-weight: 600;">
                    {{ $validation['inputs_valid'] ? __('messages.valid') : __('messages.invalid') }}
                </span>
            </div>
            @if(!$validation['inputs_valid'])
            <div style="font-size: 0.75rem; color: var(--danger); margin-top: 0.5rem;">
                {{ $validation['input_errors'] }}
            </div>
            @endif
        </div>

        <!-- Quantity Validation -->
        <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 0.375rem;">
            <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-secondary);">{{ __('messages.quantity_validation') }}</div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">{{ $validation['quantity_valid'] ? '✓' : '✕' }}</span>
                <span style="color: {{ $validation['quantity_valid'] ? 'var(--success)' : 'var(--danger)' }}; font-weight: 600;">
                    {{ $validation['quantity_valid'] ? __('messages.valid') : __('messages.invalid') }}
                </span>
            </div>
            @if(!$validation['quantity_valid'])
            <div style="font-size: 0.75rem; color: var(--danger); margin-top: 0.5rem;">
                {{ $validation['quantity_errors'] }}
            </div>
            @endif
        </div>

        <!-- Date Sequence Validation -->
        <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 0.375rem;">
            <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-secondary);">{{ __('messages.date_sequence_validation') }}</div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">{{ $validation['date_sequence_valid'] ? '✓' : '✕' }}</span>
                <span style="color: {{ $validation['date_sequence_valid'] ? 'var(--success)' : 'var(--danger)' }}; font-weight: 600;">
                    {{ $validation['date_sequence_valid'] ? __('messages.valid') : __('messages.invalid') }}
                </span>
            </div>
            @if(!$validation['date_sequence_valid'])
            <div style="font-size: 0.75rem; color: var(--danger); margin-top: 0.5rem;">
                {{ $validation['date_sequence_errors'] }}
            </div>
            @endif
        </div>

        <!-- KDE Validation -->
        <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 0.375rem;">
            <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-secondary);">{{ __('messages.kde_validation') }}</div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.5rem;">{{ $validation['kde_valid'] ? '✓' : '✕' }}</span>
                <span style="color: {{ $validation['kde_valid'] ? 'var(--success)' : 'var(--danger)' }}; font-weight: 600;">
                    {{ $validation['kde_valid'] ? __('messages.valid') : __('messages.invalid') }}
                </span>
            </div>
            @if(!$validation['kde_valid'])
            <div style="font-size: 0.75rem; color: var(--danger); margin-top: 0.5rem;">
                {{ $validation['kde_errors'] }}
            </div>
            @endif
        </div>
    </div>

    <!-- Detailed Validation Report -->
    @if($validation['details'])
    <div style="padding: 1rem; background: var(--bg-secondary); border-radius: 0.375rem;">
        <h4 style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('messages.detailed_validation_report') }}</h4>
        <div style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.6;">
            {!! nl2br(e($validation['details'])) !!}
        </div>
    </div>
    @endif
</div>
