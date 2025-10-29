@if(auth()->check() && auth()->user()->isOnTrial())
@php
    $daysLeft = auth()->user()->getTrialDaysRemaining();
    $isExpiringSoon = $daysLeft <= 3;
    $bannerBackground = $isExpiringSoon ? 'linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    $buttonColor = $isExpiringSoon ? '#ff6b6b' : '#667eea';
@endphp
<div class="card" style="background: {{ $bannerBackground }}; color: white; margin-bottom: 1.5rem; border: none;">
    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div style="font-size: 2rem;">{{ $isExpiringSoon ? '⏰' : '🎉' }}</div>
        <div style="flex: 1;">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">
                @if($daysLeft > 0)
                    {{ __('messages.trial_days_remaining', ['days' => $daysLeft]) }}
                @else
                    {{ __('messages.trial_expired') }}
                @endif
            </h3>
            <p style="margin: 0; opacity: 0.95; font-size: 0.875rem;">
                @if($daysLeft > 0)
                    {{ __('messages.trial_access_all_features') }}
                @else
                    {{ __('messages.trial_expired_upgrade_message') }}
                @endif
            </p>
        </div>
        {{-- Using PHP variable instead of inline ternary for button color --}}
        <a href="{{ route('pricing') }}" class="btn" style="background: white; color: {{ $buttonColor }}; font-weight: 600;">
            {{ __('messages.view_plans') }}
        </a>
    </div>
</div>
@endif
