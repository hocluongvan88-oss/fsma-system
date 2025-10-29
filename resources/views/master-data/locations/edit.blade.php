@extends('layouts.app')

@section('title', __('messages.edit_location'))

@section('content')
<div class="card" style="max-width: 600px;">
    <form method="POST" action="{{ route('master-data.locations.update', $location) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.location_name') }} *</label>
            <input type="text" name="location_name" class="form-input" value="{{ old('location_name', $location->location_name) }}" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.location_type') }} *</label>
            <select name="location_type" class="form-select" required>
                <option value="warehouse" {{ $location->location_type === 'warehouse' ? 'selected' : '' }}>{{ __('messages.warehouse') }}</option>
                <option value="farm" {{ $location->location_type === 'farm' ? 'selected' : '' }}>{{ __('messages.farm') }}</option>
                <option value="processing" {{ $location->location_type === 'processing' ? 'selected' : '' }}>{{ __('messages.processing_facility') }}</option>
                <option value="distribution" {{ $location->location_type === 'distribution' ? 'selected' : '' }}>{{ __('messages.distribution_center') }}</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.gln_13_digits') }}</label>
                <input type="text" name="gln" class="form-input" value="{{ old('gln', $location->gln) }}" maxlength="13" pattern="[0-9]{13}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.ffrn') }}</label>
                <input type="text" name="ffrn" class="form-input" value="{{ old('ffrn', $location->ffrn) }}">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.address') }}</label>
            <textarea name="address" class="form-textarea" rows="2">{{ old('address', $location->address) }}</textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.city') }}</label>
                <input type="text" name="city" class="form-input" value="{{ old('city', $location->city) }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.state') }}</label>
                <input type="text" name="state" class="form-input" value="{{ old('state', $location->state) }}">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.zip_code') }}</label>
                <input type="text" name="zip_code" class="form-input" value="{{ old('zip_code', $location->zip_code) }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.country') }} *</label>
                <input type="text" name="country" class="form-input" value="{{ old('country', $location->country) }}" required>
            </div>
        </div>
        
        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">{{ __('messages.update_location') }}</button>
            <a href="{{ route('master-data.locations.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
    
    <form method="POST" action="{{ route('master-data.locations.destroy', $location) }}" style="margin-top: 1rem;" onsubmit="return confirm('{{ __('messages.confirm_delete_location') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-secondary" style="background: var(--error); border-color: var(--error); width: 100%;">{{ __('messages.delete_location') }}</button>
    </form>
</div>
@endsection
