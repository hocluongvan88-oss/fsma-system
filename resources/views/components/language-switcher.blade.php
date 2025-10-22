@php
    $availableLocales = config('locales.available_locales', []);
    $currentLocale = app()->getLocale();
    $currentLocaleData = $availableLocales[$currentLocale] ?? ['flag' => '🌐', 'name' => 'English'];
@endphp

@if(!empty($availableLocales))
<div class="language-switcher" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open" class="language-btn" type="button">
        <span class="language-flag">{{ $currentLocaleData['flag'] }}</span>
        <span class="language-name">{{ $currentLocaleData['name'] }}</span>
        <svg class="language-arrow" :class="{ 'rotate-180': open }" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="4 6 8 10 12 6"></polyline>
        </svg>
    </button>
    
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="language-dropdown">
        @foreach($availableLocales as $code => $locale)
            <a href="{{ route('language.switch', $code) }}" 
               class="language-option {{ $currentLocale === $code ? 'active' : '' }}">
                <span class="language-flag">{{ $locale['flag'] }}</span>
                <span class="language-name">{{ $locale['name'] }}</span>
                @if($currentLocale === $code)
                    <svg class="language-check" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 8 6 11 13 4"></polyline>
                    </svg>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endif

<style>
.language-switcher {
    position: relative;
    display: inline-block;
}

.language-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
    min-height: 44px;
}

.language-btn:hover {
    background: var(--bg-secondary);
    border-color: var(--accent-primary);
}

.language-flag {
    font-size: 1.25rem;
    line-height: 1;
}

.language-name {
    font-weight: 500;
}

.language-arrow {
    transition: transform 0.2s;
    flex-shrink: 0;
}

.language-arrow.rotate-180 {
    transform: rotate(180deg);
}

.language-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    min-width: 200px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    z-index: 1000;
    overflow: hidden;
}

.language-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--text-secondary);
    text-decoration: none;
    transition: all 0.2s;
    min-height: 44px;
}

.language-option:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.language-option.active {
    background: var(--accent-primary);
    color: white;
}

.language-option.active .language-flag,
.language-option.active .language-name {
    color: white;
}

.language-check {
    margin-left: auto;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .language-dropdown {
        right: auto;
        left: 0;
    }
}
</style>
