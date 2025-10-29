@extends('layouts.app')

@section('title', __('messages.pricing_management'))

@section('content')
<div class="card" style="margin-bottom: 1.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin: 0;">{{ __('messages.pricing_management') }}</h1>
    </div>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.package') }}</th>
                    <th>{{ __('messages.list_price_monthly') }}</th>
                    <th>{{ __('messages.sale_price_monthly') }}</th>
                    <th>{{ __('messages.list_price_yearly') }}</th>
                    <th>{{ __('messages.sale_price_yearly') }}</th>
                    <th>{{ __('messages.cte_records') }}</th>
                    <th>{{ __('messages.documents') }}</th>
                    <th>{{ __('messages.users') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pricings as $pricing)
                <tr>
                    <td><strong>{{ $pricing->package_name }}</strong></td>
                    <td>{{ number_format($pricing->list_price_monthly, 0) }} VNĐ</td>
                    <td>{{ number_format($pricing->price_monthly, 0) }} VNĐ</td>
                    <td>{{ number_format($pricing->list_price_yearly, 0) }} VNĐ</td>
                    <td>{{ number_format($pricing->price_yearly, 0) }} VNĐ</td>
                    <td>{{ $pricing->max_cte_records_monthly ?: __('messages.unlimited') }}</td>
                    <td>{{ $pricing->max_documents ?: __('messages.unlimited') }}</td>
                    <td>{{ $pricing->max_users ?: __('messages.unlimited') }}</td>
                    <td>
                        <span class="badge badge-{{ $pricing->is_active ? 'success' : 'secondary' }}">
                            {{ $pricing->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.pricing.edit', $pricing) }}" class="btn btn-sm btn-primary">
                            {{ __('messages.edit') }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
