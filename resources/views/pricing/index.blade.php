@extends('layouts.app')

@section('title', __('messages.pricing_upgrade_plan'))

@section('content')
<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; margin-bottom: 2rem; border: none;">
    <div style="text-align: center;">
        <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('messages.choose_right_plan') }}</h1>
        <p style="font-size: 1.125rem; opacity: 0.95;">{{ __('messages.fsma_204_compliant_system') }}</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    @foreach($packages as $package)
    <div class="card" style="position: relative; {{ $package['is_highlighted'] ? 'border: 2px solid var(--accent-primary); box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);' : '' }}">
        @if($package['is_highlighted'])
        <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: var(--accent-primary); color: white; padding: 0.25rem 1rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
            {{ __('messages.recommended') }}
        </div>
        @endif
        
        @if($currentPackage === $package['id'])
        <div style="position: absolute; top: 1rem; right: 1rem;">
            <span class="badge badge-success">{{ __('messages.current_plan') }}</span>
        </div>
        @endif
        
        <div style="margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $package['name'] }}</h3>
            
            <div x-data="{ billingCycle: 'monthly' }" style="margin-bottom: 1rem;">
                @if($package['id'] !== 'free' && $package['id'] !== 'enterprise')
                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <button 
                        @click="billingCycle = 'monthly'" 
                        :class="billingCycle === 'monthly' ? 'btn-primary' : 'btn-secondary'"
                        class="btn"
                        style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">
                        {{ __('messages.monthly') }}
                    </button>
                    <button 
                        @click="billingCycle = 'yearly'" 
                        :class="billingCycle === 'yearly' ? 'btn-primary' : 'btn-secondary'"
                        class="btn"
                        style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">
                        {{ __('messages.yearly') }}
                    </button>
                </div>
                @endif
                
                <div x-show="billingCycle === 'monthly'" style="text-align: center;">
                    @if(isset($package['monthly_list_price']) && $package['monthly_list_price'] > $package['monthly_price'])
                    <div style="font-size: 1rem; color: var(--text-secondary); text-decoration: line-through;">
                        ${{ number_format($package['monthly_list_price'], 2) }}
                    </div>
                    @endif
                    <div style="font-size: 2rem; font-weight: 700; color: var(--accent-primary);">
                        @if($package['id'] === 'free')
                            {{ __('messages.free') }}
                        @elseif($package['id'] === 'enterprise')
                            {{ __('messages.custom') }}
                        @else
                            ${{ number_format($package['monthly_price'], 2) }}
                        @endif
                    </div>
                    @if($package['id'] !== 'free' && $package['id'] !== 'enterprise')
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">/{{ __('messages.month') }}</div>
                    @endif
                </div>
                
                @if($package['id'] !== 'free' && $package['id'] !== 'enterprise')
                <div x-show="billingCycle === 'yearly'" style="text-align: center;">
                    @if(isset($package['yearly_list_price']) && $package['yearly_list_price'] > $package['yearly_price'])
                    <div style="font-size: 1rem; color: var(--text-secondary); text-decoration: line-through;">
                        ${{ number_format($package['yearly_list_price'], 2) }}
                    </div>
                    @endif
                    <div style="font-size: 2rem; font-weight: 700; color: var(--accent-primary);">
                        ${{ number_format($package['yearly_price'], 2) }}
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-secondary);">/{{ __('messages.year') }}</div>
                    @php
                        $yearlySavings = ($package['monthly_price'] * 12) - $package['yearly_price'];
                        $discountPercentage = $package['monthly_price'] > 0 ? round(($yearlySavings / ($package['monthly_price'] * 12)) * 100) : 0;
                    @endphp
                    @if($yearlySavings > 0)
                    <div style="margin-top: 0.5rem; padding: 0.25rem 0.75rem; background: var(--success); color: white; border-radius: 0.25rem; font-size: 0.75rem; display: inline-block;">
                        {{ __('messages.save') }} ${{ number_format($yearlySavings, 2) }} ({{ $discountPercentage }}%)
                    </div>
                    @endif
                </div>
                @endif
            </div>
            
            @if($package['show_promotion'] && $package['promotion_text'])
            <div style="background: var(--warning); color: var(--bg-primary); padding: 0.5rem; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 600; text-align: center; margin-bottom: 0.5rem;">
                {{ $package['promotion_text'] }}
            </div>
            @endif
            
            <div style="font-size: 0.875rem; color: var(--text-secondary);">{{ $package['description'] }}</div>
        </div>
        
        <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--bg-tertiary); border-radius: 0.5rem;">
            <div style="font-weight: 600; margin-bottom: 0.5rem;">{{ __('messages.limits') }}:</div>
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.25rem;">
                📊 {{ $package['max_cte_records'] == 0 ? __('messages.unlimited') : number_format($package['max_cte_records']) }} {{ __('messages.cte_records') }}{{ $package['max_cte_records'] > 0 ? '/'.__('messages.month') : '' }}
            </div>
            <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.25rem;">
                📄 {{ $package['max_documents'] == 0 ? __('messages.unlimited') : $package['max_documents'] }} {{ __('messages.documents') }}
            </div>
            <div style="font-size: 0.875rem; color: var(--text-secondary);">
                👥 {{ $package['max_users'] == 0 ? __('messages.unlimited') : $package['max_users'] }} {{ __('messages.users') }}
            </div>
        </div>
        
        <ul style="list-style: none; margin-bottom: 1.5rem;">
            @foreach($package['features'] as $feature)
            <li style="display: flex; align-items: start; gap: 0.5rem; margin-bottom: 0.5rem; font-size: 0.875rem;">
                <span style="color: var(--success); flex-shrink: 0;">✓</span>
                <span>{{ $feature }}</span>
            </li>
            @endforeach
        </ul>
        
        <div x-data="{ billingPeriod: 'monthly', showPaymentModal: false, selectedGateway: 'vnpay' }">
            @if($currentPackage === $package['id'])
            <button class="btn btn-secondary" disabled style="width: 100%;">{{ __('messages.current_plan') }}</button>
            @elseif($package['id'] === 'free')
            <button class="btn btn-secondary" disabled style="width: 100%;">{{ __('messages.not_available') }}</button>
            @elseif($package['id'] === 'enterprise')
            <a href="mailto:sales@fsma204.com?subject={{ __('messages.enterprise_plan_inquiry') }}&body={{ __('messages.enterprise_inquiry_body') }}" 
               class="btn btn-primary" 
               style="width: 100%; text-align: center; text-decoration: none; display: block;">
                {{ __('messages.contact_sales') }}
            </a>
            @else
            <button @click="showPaymentModal = true" class="btn btn-primary" style="width: 100%;">
                {{ __('messages.upgrade_to') }} {{ $package['name'] }}
            </button>
            
            <div x-show="showPaymentModal" 
                 x-cloak
                 @click.away="showPaymentModal = false"
                 style="position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem;">
                <div @click.stop style="background: var(--bg-primary); border-radius: 0.5rem; padding: 2rem; max-width: 500px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                    <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">{{ __('messages.choose_payment_method') }}</h3>
                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
                        {{ __('messages.select_payment_gateway') }}
                    </p>
                    
                    <div @click="selectedGateway = 'vnpay'" 
                         :class="selectedGateway === 'vnpay' ? 'border: 2px solid var(--accent-primary); background: var(--bg-tertiary);' : 'border: 1px solid var(--border-color);'"
                         style="padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; cursor: pointer; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid var(--accent-primary); display: flex; align-items: center; justify-content: center;">
                                <div x-show="selectedGateway === 'vnpay'" style="width: 12px; height: 12px; border-radius: 50%; background: var(--accent-primary);"></div>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ __('messages.vnpay_recommended') }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ __('messages.vnpay_methods') }}</div>
                            </div>
                            <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png" alt="VNPay" style="height: 30px;">
                        </div>
                    </div>
                    
                    <div @click="selectedGateway = 'stripe'" 
                         :class="selectedGateway === 'stripe' ? 'border: 2px solid var(--accent-primary); background: var(--bg-tertiary);' : 'border: 1px solid var(--border-color);'"
                         style="padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; cursor: pointer; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 24px; height: 24px; border-radius: 50%; border: 2px solid var(--accent-primary); display: flex; align-items: center; justify-content: center;">
                                <div x-show="selectedGateway === 'stripe'" style="width: 12px; height: 12px; border-radius: 50%; background: var(--accent-primary);"></div>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ __('messages.stripe_international') }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);">{{ __('messages.stripe_methods') }}</div>
                            </div>
                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="Stripe" style="height: 20px;">
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <button @click="showPaymentModal = false" class="btn btn-secondary" style="flex: 1;">
                            {{ __('messages.cancel') }}
                        </button>
                        <form x-bind:action="selectedGateway === 'vnpay' ? '{{ route('vnpay.create') }}' : '{{ route('checkout.create') }}'" 
                              method="POST" 
                              style="flex: 1;">
                            @csrf
                            <input type="hidden" name="package_id" value="{{ $package['id'] }}">
                            <input type="hidden" name="billing_period" x-model="billingPeriod">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">
                                {{ __('messages.continue') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div class="card">
    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">{{ __('messages.detailed_plan_comparison') }}</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.feature') }}</th>
                    @foreach($packages as $package)
                    <th>{{ $package['name'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ __('messages.cte_records_per_month') }}</td>
                    @foreach($packages as $package)
                    <td>{{ $package['max_cte_records'] == 0 ? __('messages.unlimited') : number_format($package['max_cte_records']) }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td>{{ __('messages.documents') }}</td>
                    @foreach($packages as $package)
                    <td>{{ $package['max_documents'] == 0 ? __('messages.unlimited') : $package['max_documents'] }}</td>
                    @endforeach
                </tr>
                <tr>
                    <td>{{ __('messages.users') }}</td>
                    @foreach($packages as $package)
                    <td>{{ $package['max_users'] == 0 ? __('messages.unlimited') : $package['max_users'] }}</td>
                    @endforeach
                </tr>
                @foreach(['Traceability', 'Export Excel/PDF', 'Advanced Reports', 'Digital Signature', '21 CFR Part 11', 'API Access'] as $feature)
                <tr>
                    <td>{{ $feature }}</td>
                    @foreach($packages as $package)
                    <td>{{ in_array($feature, $package['features']) ? '✓' : '-' }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="background: var(--bg-tertiary); border-left: 4px solid var(--accent-primary);">
    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">{{ __('messages.secure_payment') }}</h3>
    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1rem;">
        {{ __('messages.secure_payment_description') }}
    </p>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.75rem; color: var(--text-secondary);">
        <div>✓ {{ __('messages.automatic_recurring') }}</div>
        <div>✓ {{ __('messages.cancel_anytime') }}</div>
        <div>✓ {{ __('messages.full_vat_invoices') }}</div>
        <div>✓ {{ __('messages.24_7_support') }}</div>
    </div>
    <div style="display: flex; gap: 2rem; margin-top: 1rem; align-items: center;">
        <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png" alt="VNPay" style="height: 30px; opacity: 0.7;">
        <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="Stripe" style="height: 20px; opacity: 0.7;">
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
[x-cloak] { display: none !important; }
</style>
@endpush
@endsection
