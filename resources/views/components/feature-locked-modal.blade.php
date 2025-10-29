@props(['feature', 'requiredPackage'])

<div x-data="{ show: false }" 
     @feature-locked.window="if($event.detail.feature === '{{ $feature }}') { show = true }"
     x-show="show"
     x-cloak
     @click.away="show = false"
     style="position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem;">
    <div @click.stop 
         style="background: var(--bg-primary); border-radius: 1rem; padding: 2rem; max-width: 500px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🔒</div>
            <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                {{ __('messages.feature_locked') }}
            </h3>
            <p style="color: var(--text-secondary); font-size: 0.875rem;">
                {{ __('messages.feature_locked_description', ['feature' => $feature]) }}
            </p>
        </div>

        <div style="background: var(--bg-tertiary); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="font-weight: 600; margin-bottom: 1rem; color: var(--accent-primary);">
                {{ __('messages.available_in') }}:
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.5rem;">✓</span>
                <div>
                    <div style="font-weight: 600;">{{ $requiredPackage }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary);">
                        {{ __('messages.and_higher_plans') }}
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem;">
            <button @click="show = false" class="btn btn-secondary" style="flex: 1;">
                {{ __('messages.maybe_later') }}
            </button>
            <a href="{{ route('pricing') }}" class="btn btn-primary" style="flex: 1; text-align: center; text-decoration: none;">
                {{ __('messages.upgrade_now') }}
            </a>
        </div>
    </div>
</div>

@push('scripts')
<style>
[x-cloak] { display: none !important; }
</style>
@endpush
