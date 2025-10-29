@extends('layouts.app')

@section('title', __('messages.create_product'))

@section('content')
<div class="card" style="max-width: 600px;">
    <form method="POST" action="{{ route('master-data.products.store') }}">
        @csrf
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.sku') }} *</label>
            <input type="text" name="sku" class="form-input" value="{{ old('sku') }}" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.product_name') }} *</label>
            <input type="text" name="product_name" class="form-input" value="{{ old('product_name') }}" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.description') }}</label>
            <textarea name="description" class="form-textarea">{{ old('description') }}</textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.category') }}</label>
            <input type="text" name="category" class="form-input" value="{{ old('category') }}">
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.unit_of_measure') }} *</label>
            <select name="unit_of_measure" class="form-select" required>
                <option value="kg">{{ __('messages.kilogram') }} (kg)</option>
                <option value="lb">{{ __('messages.pound') }} (lb)</option>
                <option value="box">{{ __('messages.box') }}</option>
                <option value="case">{{ __('messages.case') }}</option>
                <option value="pallet">{{ __('messages.pallet') }}</option>
            </select>
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_ftl" value="1" {{ old('is_ftl') ? 'checked' : '' }}>
                <span class="form-label" style="margin: 0;">{{ __('messages.food_traceability_list_ftl') }}</span>
            </label>
        </div>
        
        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">{{ __('messages.create_product') }}</button>
            <a href="{{ route('master-data.products.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
