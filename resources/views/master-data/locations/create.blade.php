@extends('layouts.app')

@section('title', __('messages.create_location'))

@section('content')
<div class="card" style="max-width: 600px;">
    <form method="POST" action="{{ route('master-data.locations.store') }}">
        @csrf
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.location_name') }} *</label>
            <input type="text" name="location_name" class="form-input" value="{{ old('location_name') }}" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.location_type') }} *</label>
            <select name="location_type" class="form-select" required>
                <option value="warehouse">{{ __('messages.warehouse') }}</option>
                <option value="farm">{{ __('messages.farm') }}</option>
                <option value="processing">{{ __('messages.processing_facility') }}</option>
                <option value="distribution">{{ __('messages.distribution_center') }}</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.gln_13_digits') }}</label>
                <input type="text" name="gln" class="form-input" value="{{ old('gln') }}" maxlength="13" pattern="[0-9]{13}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.ffrn') }}</label>
                <input type="text" name="ffrn" class="form-input" value="{{ old('ffrn') }}">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.address') }}</label>
            <textarea name="address" class="form-textarea" rows="2">{{ old('address') }}</textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.city') }}</label>
                <input type="text" name="city" class="form-input" value="{{ old('city') }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.state') }}</label>
                <input type="text" name="state" class="form-input" value="{{ old('state') }}">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.zip_code') }}</label>
                <input type="text" name="zip_code" class="form-input" value="{{ old('zip_code') }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.country') }} *</label>
                <input type="text" name="country" class="form-input" value="{{ old('country', 'USA') }}" required>
            </div>
        </div>
        
        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">{{ __('messages.create_location') }}</button>
            <a href="{{ route('master-data.locations.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
