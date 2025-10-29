@extends('layouts.app')

@section('title', __('messages.locations'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <form method="GET" style="display: flex; gap: 0.75rem; flex: 1;">
        <input type="text" name="search" class="form-input" placeholder="{{ __('messages.search_locations') }}" value="{{ request('search') }}" style="max-width: 300px;">
        
        <select name="type" class="form-select" style="max-width: 150px;">
            <option value="">{{ __('messages.all_types') }}</option>
            <option value="warehouse" {{ request('type') === 'warehouse' ? 'selected' : '' }}>{{ __('messages.warehouse') }}</option>
            <option value="farm" {{ request('type') === 'farm' ? 'selected' : '' }}>{{ __('messages.farm') }}</option>
            <option value="processing" {{ request('type') === 'processing' ? 'selected' : '' }}>{{ __('messages.processing') }}</option>
            <option value="distribution" {{ request('type') === 'distribution' ? 'selected' : '' }}>{{ __('messages.distribution') }}</option>
        </select>
        
        <button type="submit" class="btn btn-secondary">{{ __('messages.filter') }}</button>
        @if(request()->hasAny(['search', 'type']))
            <a href="{{ route('master-data.locations.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
        @endif
    </form>
    
    <a href="{{ route('master-data.locations.create') }}" class="btn btn-primary">{{ __('messages.add_location') }}</a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.location_name') }}</th>
                    <th>{{ __('messages.type') }}</th>
                    <th>{{ __('messages.gln') }}</th>
                    <th>{{ __('messages.ffrn') }}</th>
                    <th>{{ __('messages.city_state') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($locations as $location)
                <tr>
                    <td><strong>{{ $location->location_name }}</strong></td>
                    <td>
                        <span class="badge badge-info">{{ ucfirst($location->location_type) }}</span>
                    </td>
                    <td>{{ $location->gln ?? '-' }}</td>
                    <td>{{ $location->ffrn ?? '-' }}</td>
                    <td>{{ $location->city }}, {{ $location->state }}</td>
                    <td>
                        <a href="{{ route('master-data.locations.edit', $location) }}" class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">{{ __('messages.edit') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);">{{ __('messages.no_locations_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{ $locations->links() }}
</div>
@endsection
