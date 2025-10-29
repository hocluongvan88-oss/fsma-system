<!-- Advanced Filters Panel -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <!-- Added i18n for title -->
            <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-secondary); margin: 0; text-transform: uppercase;">{{ __('messages.advanced_filters') }}</h3>
            <button type="button" onclick="toggleAdvancedFilters()" style="background: none; border: none; color: var(--accent-primary); cursor: pointer; font-size: 1.25rem; padding: 0;">
                <span id="filterToggleIcon">−</span>
            </button>
        </div>
        
        <form method="GET" action="{{ route('documents.index') }}" id="advancedFiltersForm" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; align-items: flex-end;">
            <!-- Search Input -->
            <div>
                <!-- Added i18n for labels -->
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">{{ __('messages.search') }}</label>
                <input type="text" name="search" class="form-input" 
                       placeholder="{{ __('messages.doc_number_title') }}" 
                       value="{{ request('search') }}"
                       style="width: 100%;">
            </div>

            <!-- Type Filter -->
            <div>
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">{{ __('messages.document_type') }}</label>
                <select name="type" class="form-select" style="width: 100%;">
                    <option value="">{{ __('messages.all_types') }}</option>
                    <option value="traceability_plan" {{ request('type') == 'traceability_plan' ? 'selected' : '' }}>{{ __('messages.traceability_plan') }}</option>
                    <option value="sop" {{ request('type') == 'sop' ? 'selected' : '' }}>{{ __('messages.sop') }}</option>
                    <option value="fda_correspondence" {{ request('type') == 'fda_correspondence' ? 'selected' : '' }}>{{ __('messages.fda_correspondence') }}</option>
                    <option value="training_material" {{ request('type') == 'training_material' ? 'selected' : '' }}>{{ __('messages.training_material') }}</option>
                    <option value="audit_report" {{ request('type') == 'audit_report' ? 'selected' : '' }}>{{ __('messages.audit_report') }}</option>
                    <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>{{ __('messages.other') }}</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">{{ __('messages.status') }}</label>
                <select name="status" class="form-select" style="width: 100%;">
                    <option value="">{{ __('messages.all_status') }}</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('messages.draft') }}</option>
                    <option value="review" {{ request('status') == 'review' ? 'selected' : '' }}>{{ __('messages.review') }}</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('messages.approved') }}</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>{{ __('messages.archived') }}</option>
                </select>
            </div>

            <!-- Expiry Status Filter -->
            <div>
                <label class="form-label" style="font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">{{ __('messages.expiry_status') }}</label>
                <select name="expiry_status" class="form-select" style="width: 100%;">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="expired" {{ request('expiry_status') == 'expired' ? 'selected' : '' }}>{{ __('messages.expired') }}</option>
                    <option value="expiring_soon" {{ request('expiry_status') == 'expiring_soon' ? 'selected' : '' }}>{{ __('messages.expiring_soon') }}</option>
                    <option value="active" {{ request('expiry_status') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    {{ __('messages.filter') }}
                </button>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary" style="flex: 1;">
                    {{ __('messages.clear') }}
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAdvancedFilters() {
    const form = document.getElementById('advancedFiltersForm');
    const icon = document.getElementById('filterToggleIcon');
    if (form.style.display === 'none') {
        form.style.display = 'grid';
        icon.textContent = '−';
    } else {
        form.style.display = 'none';
        icon.textContent = '+';
    }
}
</script>
