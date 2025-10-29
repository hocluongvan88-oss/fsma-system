@extends('layouts.app')

@section('title', __('messages.edit_partner'))

@section('content')
<div class="card" style="max-width: 600px;">
    <form method="POST" action="{{ route('master-data.partners.update', $partner) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.partner_name') }} *</label>
            <input type="text" name="partner_name" class="form-input" value="{{ old('partner_name', $partner->partner_name) }}" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.partner_type') }} *</label>
            <select name="partner_type" class="form-select" required>
                <option value="supplier" {{ $partner->partner_type === 'supplier' ? 'selected' : '' }}>{{ __('messages.supplier') }}</option>
                <option value="customer" {{ $partner->partner_type === 'customer' ? 'selected' : '' }}>{{ __('messages.customer') }}</option>
                <option value="both" {{ $partner->partner_type === 'both' ? 'selected' : '' }}>{{ __('messages.both') }}</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.contact_person') }}</label>
            <input type="text" name="contact_person" class="form-input" value="{{ old('contact_person', $partner->contact_person) }}">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.email') }}</label>
                <input type="email" name="email" class="form-input" value="{{ old('email', $partner->email) }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.phone') }}</label>
                <input type="text" name="phone" class="form-input" value="{{ old('phone', $partner->phone) }}">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.address') }}</label>
            <textarea name="address" class="form-textarea" rows="2">{{ old('address', $partner->address) }}</textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.gln_13_digits') }}</label>
            <input type="text" name="gln" class="form-input" value="{{ old('gln', $partner->gln) }}" maxlength="13" pattern="[0-9]{13}">
        </div>
        
        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary">{{ __('messages.update_partner') }}</button>
            <a href="{{ route('master-data.partners.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
    
    <form method="POST" action="{{ route('master-data.partners.destroy', $partner) }}" style="margin-top: 1rem;" onsubmit="return confirm('{{ __('messages.confirm_delete_partner') }}')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-secondary" style="background: var(--error); border-color: var(--error); width: 100%;">{{ __('messages.delete_partner') }}</button>
    </form>
</div>
@endsection
