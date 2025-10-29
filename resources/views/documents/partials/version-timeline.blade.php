<!-- Version Timeline Section -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 1.5rem;">
        <!-- Added i18n for title -->
        <h3 style="font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 1.5rem;">{{ __('messages.version_history') }}</h3>
        
        @if($document->versions->count() > 0)
        <div style="border-left: 2px solid var(--border-color); padding-left: 1rem;">
            @foreach($document->versions as $version)
            <div style="margin-bottom: 1.5rem; position: relative;">
                <div style="position: absolute; left: -1.5rem; top: 0; width: 12px; height: 12px; background: var(--accent-primary); border-radius: 50%; border: 2px solid var(--bg-secondary);"></div>
                
                <div style="display: flex; justify-content: space-between; align-items: start; gap: 1rem;">
                    <div>
                        <div style="font-weight: 600; color: var(--text-primary);">{{ __('messages.version') }} {{ $version->version }}</div>
                        <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">
                            {{ $version->created_at->format('Y-m-d H:i') }} {{ __('messages.by') }} {{ $version->creator->name }}
                        </div>
                        @if($version->change_notes)
                        <div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.5rem; font-style: italic;">
                            {{ $version->change_notes }}
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('documents.download-version', [$document, $version]) }}" class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem; white-space: nowrap;">
                        {{ __('messages.download') }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="color: var(--text-secondary); font-style: italic;">{{ __('messages.no_previous_versions') }}</div>
        @endif
    </div>
</div>
