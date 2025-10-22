@extends('layouts.app')

@section('title', __('messages.query_traceability'))

@section('content')
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">{{ __('messages.query_traceability') }}</h2>
    
    <form method="POST" action="{{ route('reports.traceability.query') }}">
        @csrf
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.tlc_traceability_lot_code') }} *</label>
                <input type="text" name="tlc" class="form-input" required placeholder="{{ __('messages.enter_tlc_to_trace') }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.trace_direction') }} *</label>
                <select name="direction" class="form-select" required>
                    <option value="backward">{{ __('messages.trace_backward_find_sources') }}</option>
                    <option value="forward">{{ __('messages.trace_forward_find_destinations') }}</option>
                    <option value="both">{{ __('messages.both_directions') }}</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">{{ __('messages.query_traceability') }}</button>
    </form>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 600;">{{ __('messages.recent_trace_records') }}</h2>
        <a href="{{ route('reports.traceability.analytics') }}" class="btn btn-secondary">
            <i class="bi bi-graph-up"></i> {{ __('messages.view_analytics') }}
        </a>
    </div>

    <form method="GET" action="{{ route('reports.traceability') }}" style="margin-bottom: 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.search') }}</label>
                <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="{{ __('messages.search_tlc_lot') }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.product') }}</label>
                <select name="product_id" class="form-select">
                    <option value="">{{ __('messages.all_products') }}</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->product_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.location') }}</label>
                <select name="location_id" class="form-select">
                    <option value="">{{ __('messages.all_locations') }}</option>
                    @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                        {{ $location->location_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="consumed" {{ request('status') == 'consumed' ? 'selected' : '' }}>{{ __('messages.consumed') }}</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>{{ __('messages.shipped') }}</option>
                </select>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-top: 1rem;">
            <div class="form-group">
                <label class="form-label">{{ __('messages.date_from') }}</label>
                <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.date_to') }}</label>
                <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
            </div>
        </div>
        
        <div style="display: flex; gap: 0.75rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary">{{ __('messages.apply_filters') }}</button>
            <a href="{{ route('reports.traceability') }}" class="btn btn-secondary">{{ __('messages.clear_filters') }}</a>
        </div>
    </form>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.tlc') }}</th>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.lot_code') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                    <th>{{ __('messages.location') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.created_at') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    <td><strong>{{ $record->tlc }}</strong></td>
                    <td>{{ $record->product->product_name }}</td>
                    <td>{{ $record->lot_code }}</td>
                    <td>{{ $record->quantity }} {{ $record->unit }}</td>
                    <td>{{ $record->location->location_name }}</td>
                    <td>
                        <span class="badge badge-{{ $record->status === 'active' ? 'success' : 'info' }}">
                            {{ __('messages.' . $record->status) }}
                        </span>
                    </td>
                    <td>{{ $record->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <form method="POST" action="{{ route('reports.traceability.query') }}" style="display: inline;">
                            @csrf
                            <input type="hidden" name="tlc" value="{{ $record->tlc }}">
                            <input type="hidden" name="direction" value="both">
                            <button type="submit" class="btn btn-sm btn-primary">{{ __('messages.trace') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                        {{ __('messages.no_records_found') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $records->links() }}
    </div>
</div>
@endsection
