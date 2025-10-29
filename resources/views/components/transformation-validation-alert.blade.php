<!-- Transformation Validation Alert Component -->
@if($errors->any())
<div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--danger); margin-bottom: 1.5rem;">
    <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--danger); margin-bottom: 0.5rem;">{{ __('messages.validation_errors') }}</h4>
    <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.875rem;">
        @foreach($errors->all() as $error)
        <li style="color: var(--text-secondary); margin-bottom: 0.25rem;">{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('validation_warnings'))
<div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem; border-left: 4px solid var(--warning); margin-bottom: 1.5rem;">
    <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--warning); margin-bottom: 0.5rem;">{{ __('messages.validation_warnings') }}</h4>
    <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.875rem;">
        @foreach(session('validation_warnings') as $warning)
        <li style="color: var(--text-secondary); margin-bottom: 0.25rem;">{{ $warning }}</li>
        @endforeach
    </ul>
</div>
@endif
