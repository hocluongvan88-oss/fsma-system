@extends('layouts.app')

@section('title', __('messages.edit_pricing') . ' - ' . $pricing->package_name)

@section('content')
<div class="card" style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.5rem; font-weight: 700; margin: 0;">{{ __('messages.edit_pricing') }}: {{ $pricing->package_name }}</h1>
</div>

<div class="card">
    <form action="{{ route('admin.pricing.update', $pricing) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label for="package_name">{{ __('messages.package_name') }}</label>
                <input type="text" id="package_name" name="package_name" class="form-control" value="{{ $pricing->package_name }}" required>
            </div>
        </div>

        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-primary);">
            {{ __('messages.monthly_pricing') }}
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label for="list_price_monthly">{{ __('messages.list_price_vnd') }}</label>
                <input type="number" id="list_price_monthly" name="list_price_monthly" class="form-control" value="{{ $pricing->list_price_monthly }}" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="price_monthly">{{ __('messages.sale_price_vnd') }}</label>
                <input type="number" id="price_monthly" name="price_monthly" class="form-control" value="{{ $pricing->price_monthly }}" step="0.01" required>
            </div>
        </div>

        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-primary);">
            {{ __('messages.yearly_pricing') }}
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label for="list_price_yearly">{{ __('messages.list_price_vnd') }}</label>
                <input type="number" id="list_price_yearly" name="list_price_yearly" class="form-control" value="{{ $pricing->list_price_yearly }}" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="price_yearly">{{ __('messages.sale_price_vnd') }}</label>
                <input type="number" id="price_yearly" name="price_yearly" class="form-control" value="{{ $pricing->price_yearly }}" step="0.01" required>
            </div>
        </div>

        <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--border-primary);">
            {{ __('messages.package_limits_zero_unlimited') }}
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label for="max_cte_records_monthly">{{ __('messages.cte_records_per_month') }}</label>
                <input type="number" id="max_cte_records_monthly" name="max_cte_records_monthly" class="form-control" value="{{ $pricing->max_cte_records_monthly }}" required>
            </div>
            <div class="form-group">
                <label for="max_documents">{{ __('messages.documents') }}</label>
                <input type="number" id="max_documents" name="max_documents" class="form-control" value="{{ $pricing->max_documents }}" required>
            </div>
            <div class="form-group">
                <label for="max_users">{{ __('messages.users') }}</label>
                <input type="number" id="max_users" name="max_users" class="form-control" value="{{ $pricing->max_users }}" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ $pricing->is_active ? 'checked' : '' }}>
                <span>{{ __('messages.activate_package') }}</span>
            </label>
        </div>

        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">{{ __('messages.save_changes') }}</button>
            <a href="{{ route('admin.pricing.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
