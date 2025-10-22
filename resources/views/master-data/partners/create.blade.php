@extends('layouts.app')

@section('title', __('messages.create_partner'))

@section('content')
<div class="card" style="max-width: 600px;">
    <form method="POST" action="{{ route('master-data.partners.store') }}">
        @csrf
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.partner_name') }} *</label>
            <input type="text" name="partner_name" class="form-input" value="{{ old('partner_name') }}" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.partner_type') }} *</label>
            <select name="partner_type" class="form-select" required>
                <option value="supplier">{{ __('messages.supplier') }}</option>
                <option value="customer">{{ __('messages.customer') }}</option>
                <option value="both">{{ __('messages.both') }}</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.contact_person') }}</label>
            <input type="text" name="contact_person" class="form-input" value="{{ old('contact_person') }}">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.email') }}</label>
                <input type="email" name="email" class="form-input" value="{{ old('email') }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.phone') }}</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone') }}">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.address') }}</label>
            <textarea name="address" class="form-textarea" rows="2">{{ old('address') }}</textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.gln_13_digits') }}</label>
            <input type="text" name="gln" class="form-input" value="{{ old('gln') }}" maxlength="13" pattern="[0-9]{13}">
        </div>
        
        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">{{ __('messages.create_partner') }}</button>
            <a href="{{ route('master-data.partners.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
