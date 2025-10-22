@extends('layouts.app')

@section('title', __('messages.package_management'))

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; margin: 0;">{{ __('messages.package_management') }}</h2>
        <div style="color: var(--text-muted); font-size: 0.875rem;">
            {{ __('messages.manage_pricing_features_packages') }}
        </div>
    </div>

    @if(session('success'))
    <div style="background: var(--success); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        {{ session('success') }}
    </div>
    @endif

    <div style="display: grid; gap: 1.5rem;">
        @foreach($packages as $package)
        <div class="card" style="background: var(--card-bg); border: 2px solid var(--border);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                <div>
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">
                        {{ $package->name }}
                        @if(!$package->is_visible)
                        <span style="background: var(--text-muted); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; margin-left: 0.5rem;">
                            {{ __('messages.hidden') }}
                        </span>
                        @endif
                        @if(!$package->is_selectable)
                        <span style="background: var(--warning); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; margin-left: 0.5rem;">
                            {{ __('messages.not_selectable') }}
                        </span>
                        @endif
                    </h3>
                    <p style="color: var(--text-secondary); margin: 0;">{{ $package->description }}</p>
                </div>
                <a href="{{ route('admin.packages.edit', $package) }}" class="btn btn-primary">
                    {{ __('messages.edit_package') }}
                </a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">{{ __('messages.cte_records_month') }}</div>
                    <div style="font-size: 1.125rem; font-weight: 600;">
                        {{ $package->hasUnlimitedCte() ? __('messages.unlimited') : number_format($package->max_cte_records_monthly) }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">{{ __('messages.documents') }}</div>
                    <div style="font-size: 1.125rem; font-weight: 600;">
                        {{ $package->hasUnlimitedDocuments() ? __('messages.unlimited') : number_format($package->max_documents) }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">{{ __('messages.users') }}</div>
                    <div style="font-size: 1.125rem; font-weight: 600;">
                        {{ $package->hasUnlimitedUsers() ? __('messages.unlimited') : $package->max_users }}
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; padding: 1rem; background: var(--bg); border-radius: 8px; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('messages.monthly_pricing') }}</div>
                    @if($package->monthly_selling_price)
                    <div style="display: flex; align-items: baseline; gap: 0.5rem;">
                        @if($package->monthly_list_price && $package->monthly_list_price > $package->monthly_selling_price)
                        <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.875rem;">
                            ${{ number_format($package->monthly_list_price, 2) }}
                        </span>
                        @endif
                        <span style="font-size: 1.5rem; font-weight: 700;">
                            ${{ number_format($package->monthly_selling_price, 2) }}
                        </span>
                        <span style="color: var(--text-muted);">{{ __('messages.per_month') }}</span>
                    </div>
                    @if($package->getMonthlyDiscount())
                    <div style="color: var(--success); font-size: 0.75rem; margin-top: 0.25rem;">
                        {{ __('messages.save_percent', ['percent' => number_format($package->getMonthlyDiscount(), 0)]) }}
                    </div>
                    @endif
                    @else
                    <div style="color: var(--text-muted);">{{ __('messages.free') }}</div>
                    @endif
                </div>

                <div>
                    <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('messages.yearly_pricing') }}</div>
                    @if($package->yearly_selling_price)
                    <div style="display: flex; align-items: baseline; gap: 0.5rem;">
                        @if($package->yearly_list_price && $package->yearly_list_price > $package->yearly_selling_price)
                        <span style="text-decoration: line-through; color: var(--text-muted); font-size: 0.875rem;">
                            ${{ number_format($package->yearly_list_price, 2) }}
                        </span>
                        @endif
                        <span style="font-size: 1.5rem; font-weight: 700;">
                            ${{ number_format($package->yearly_selling_price, 2) }}
                        </span>
                        <span style="color: var(--text-muted);">{{ __('messages.per_year') }}</span>
                    </div>
                    @if($package->getYearlySavings())
                    <div style="color: var(--success); font-size: 0.75rem; margin-top: 0.25rem;">
                        {{ __('messages.save_percent_vs_monthly', ['percent' => number_format($package->getYearlySavings(), 0)]) }}
                    </div>
                    @endif
                    @else
                    <div style="color: var(--text-muted);">{{ __('messages.free') }}</div>
                    @endif
                </div>
            </div>

            @if($package->show_promotion && $package->promotion_text)
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-weight: 600;">
                {{ $package->promotion_text }}
            </div>
            @endif

            @if($package->features)
            <div>
                <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">{{ __('messages.features') }}</div>
                <ul style="list-style: none; padding: 0; margin: 0; display: grid; gap: 0.5rem;">
                    @foreach($package->features as $feature)
                    <li style="display: flex; align-items: start; gap: 0.5rem;">
                        <span style="color: var(--success); margin-top: 0.125rem;">✓</span>
                        <span>{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection
