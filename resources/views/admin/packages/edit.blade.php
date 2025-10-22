@extends('layouts.app')

@section('title', __('messages.edit_package_name', ['name' => $package->name]))

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        {{ __('messages.back_to_packages') }}
    </a>
</div>

<div class="card">
    <h2 style="font-size: 1.875rem; font-weight: 700; margin-bottom: 0.5rem;">
        {{ __('messages.edit_package_name', ['name' => $package->name]) }}
    </h2>
    <p style="color: var(--text-secondary); margin-bottom: 2rem;">
        {{ __('messages.configure_pricing_limits_features') }}
    </p>

    <form method="POST" action="{{ route('admin.packages.update', $package) }}">
        @csrf
        @method('PUT')

        <div style="display: grid; gap: 2rem;">
            <!-- Basic Information -->
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                        <polyline points="13 2 13 9 20 9"/>
                    </svg>
                    {{ __('messages.basic_information') }}
                </h3>
                
                <div style="display: grid; gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label" for="name">{{ __('messages.package_name') }} *</label>
                        <input type="text" id="name" name="name" class="form-input" 
                               value="{{ old('name', $package->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">{{ __('messages.description') }}</label>
                        <textarea id="description" name="description" class="form-textarea" rows="3">{{ old('description', $package->description) }}</textarea>
                        <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.brief_description_pricing_table') }}</small>
                    </div>
                </div>
            </div>

            <!-- Usage Limits -->
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    {{ __('messages.usage_limits') }}
                </h3>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
                    {{ __('messages.set_unlimited_limits_help') }}
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                    <div class="form-group">
                        <label class="form-label" for="max_cte_records_monthly">
                            {{ __('messages.cte_records_month') }} *
                        </label>
                        <input type="number" id="max_cte_records_monthly" name="max_cte_records_monthly" 
                               class="form-input" value="{{ old('max_cte_records_monthly', $package->max_cte_records_monthly) }}" 
                               min="0" required>
                        <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.monthly_cte_event_limit') }}</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="max_documents">
                            {{ __('messages.max_documents') }} *
                        </label>
                        <input type="number" id="max_documents" name="max_documents" 
                               class="form-input" value="{{ old('max_documents', $package->max_documents) }}" 
                               min="0" required>
                        <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.total_document_storage') }}</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="max_users">
                            {{ __('messages.max_users') }} *
                        </label>
                        <input type="number" id="max_users" name="max_users" 
                               class="form-input" value="{{ old('max_users', $package->max_users) }}" 
                               min="1" required>
                        <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.team_member_limit') }}</small>
                    </div>
                </div>
            </div>

            <!-- Pricing Section -->
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    {{ __('messages.pricing_usd') }}
                </h3>
                
                <div style="display: grid; gap: 2rem;">
                    <!-- Monthly Pricing -->
                    <div>
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-secondary);">{{ __('messages.monthly_billing') }}</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div class="form-group">
                                <label class="form-label" for="monthly_list_price">
                                    {{ __('messages.list_price') }}
                                </label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">$</span>
                                    <input type="number" id="monthly_list_price" name="monthly_list_price" 
                                           class="form-input" style="padding-left: 2rem;"
                                           value="{{ old('monthly_list_price', $package->monthly_list_price) }}" 
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                                <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.original_price_crossed_out') }}</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="monthly_selling_price">
                                    {{ __('messages.selling_price') }} *
                                </label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">$</span>
                                    <input type="number" id="monthly_selling_price" name="monthly_selling_price" 
                                           class="form-input" style="padding-left: 2rem;"
                                           value="{{ old('monthly_selling_price', $package->monthly_selling_price) }}" 
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                                <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.actual_price_customers_pay') }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Yearly Pricing -->
                    <div>
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--text-secondary);">{{ __('messages.yearly_billing') }}</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div class="form-group">
                                <label class="form-label" for="yearly_list_price">
                                    {{ __('messages.list_price') }}
                                </label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">$</span>
                                    <input type="number" id="yearly_list_price" name="yearly_list_price" 
                                           class="form-input" style="padding-left: 2rem;"
                                           value="{{ old('yearly_list_price', $package->yearly_list_price) }}" 
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                                <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.original_yearly_price') }}</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="yearly_selling_price">
                                    {{ __('messages.selling_price') }} *
                                </label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted);">$</span>
                                    <input type="number" id="yearly_selling_price" name="yearly_selling_price" 
                                           class="form-input" style="padding-left: 2rem;"
                                           value="{{ old('yearly_selling_price', $package->yearly_selling_price) }}" 
                                           step="0.01" min="0" placeholder="0.00">
                                </div>
                                <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.usually_discounted_vs_monthly') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promotion -->
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                    {{ __('messages.promotion_banner') }}
                </h3>
                
                <div style="display: grid; gap: 1.25rem;">
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" name="show_promotion" value="1" 
                                   style="width: 1.25rem; height: 1.25rem; cursor: pointer;"
                                   {{ old('show_promotion', $package->show_promotion) ? 'checked' : '' }}>
                            <span style="font-weight: 500;">{{ __('messages.show_promotion_banner_pricing_card') }}</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="promotion_text">{{ __('messages.promotion_text') }}</label>
                        <input type="text" id="promotion_text" name="promotion_text" class="form-input"
                               value="{{ old('promotion_text', $package->promotion_text) }}" 
                               placeholder="{{ __('messages.promotion_text_example') }}">
                        <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.displayed_highlighted_banner') }}</small>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    {{ __('messages.features_list') }}
                </h3>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
                    {{ __('messages.enter_one_feature_per_line') }}
                </p>
                
                <div class="form-group">
                    <textarea id="features" name="features_text" class="form-textarea" rows="10" 
                              placeholder="{{ __('messages.features_placeholder') }}">{{ old('features_text', is_array($package->features) ? implode("\n", $package->features) : '') }}</textarea>
                </div>
            </div>

            <!-- Display Settings -->
            <div style="background: var(--bg-tertiary); border: 1px solid var(--border-color); border-radius: 0.75rem; padding: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    {{ __('messages.display_settings') }}
                </h3>
                
                <div style="display: grid; gap: 1.25rem;">
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" name="is_visible" value="1" 
                                   style="width: 1.25rem; height: 1.25rem; cursor: pointer;"
                                   {{ old('is_visible', $package->is_visible) ? 'checked' : '' }}>
                            <span style="font-weight: 500;">{{ __('messages.show_in_pricing_table') }}</span>
                        </label>
                        <small style="color: var(--text-muted); font-size: 0.875rem; margin-left: 2rem;">{{ __('messages.uncheck_hide_package_public') }}</small>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer;">
                            <input type="checkbox" name="is_selectable" value="1" 
                                   style="width: 1.25rem; height: 1.25rem; cursor: pointer;"
                                   {{ old('is_selectable', $package->is_selectable) ? 'checked' : '' }}>
                            <span style="font-weight: 500;">{{ __('messages.users_can_select_package') }}</span>
                        </label>
                        <small style="color: var(--text-muted); font-size: 0.875rem; margin-left: 2rem;">{{ __('messages.uncheck_contact_sales_packages') }}</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sort_order">{{ __('messages.sort_order') }}</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-input"
                               value="{{ old('sort_order', $package->sort_order) }}" 
                               min="0" style="max-width: 200px;">
                        <small style="color: var(--text-muted); font-size: 0.875rem;">{{ __('messages.lower_numbers_appear_first') }}</small>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('admin.packages.index') }}" class="btn btn-secondary">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    {{ __('messages.save_changes') }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Convert features textarea to array before submit
document.querySelector('form').addEventListener('submit', function(e) {
    const featuresText = document.getElementById('features').value;
    const featuresArray = featuresText.split('\n').filter(line => line.trim() !== '');
    
    // Remove the textarea
    document.getElementById('features').remove();
    
    // Add hidden inputs for each feature
    featuresArray.forEach((feature, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `features[${index}]`;
        input.value = feature.trim();
        this.appendChild(input);
    });
});
</script>
@endsection
