<!-- Metadata Display Section -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 1.5rem;">
        <!-- Added i18n for title -->
        <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem;">{{ __('messages.metadata') }}</h3>
        
        @if($document->metadata && count($document->metadata) > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
            @foreach($document->metadata as $key => $value)
            <div>
                <div style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; text-transform: uppercase;">{{ str_replace('_', ' ', $key) }}</div>
                <div style="color: var(--text-primary); word-break: break-word;">
                    @if(is_array($value))
                        {{ implode(', ', $value) }}
                    @else
                        {{ $value }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="color: var(--text-secondary); font-style: italic;">{{ __('messages.no_data') }}</div>
        @endif
    </div>
</div>
