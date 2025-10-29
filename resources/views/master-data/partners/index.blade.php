@extends('layouts.app')

@section('title', __('messages.partners'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <form method="GET" style="display: flex; gap: 0.75rem; flex: 1;">
        <input type="text" name="search" class="form-input" placeholder="{{ __('messages.search_partners') }}" value="{{ request('search') }}" style="max-width: 300px;">
        
        <select name="type" class="form-select" style="max-width: 150px;">
            <option value="">{{ __('messages.all_types') }}</option>
            <option value="supplier" {{ request('type') === 'supplier' ? 'selected' : '' }}>{{ __('messages.supplier') }}</option>
            <option value="customer" {{ request('type') === 'customer' ? 'selected' : '' }}>{{ __('messages.customer') }}</option>
            <option value="both" {{ request('type') === 'both' ? 'selected' : '' }}>{{ __('messages.both') }}</option>
        </select>
        
        <button type="submit" class="btn btn-secondary">{{ __('messages.filter') }}</button>
        @if(request()->hasAny(['search', 'type']))
            <a href="{{ route('master-data.partners.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
        @endif
    </form>
    
    <a href="{{ route('master-data.partners.create') }}" class="btn btn-primary">{{ __('messages.add_partner') }}</a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.partner_name') }}</th>
                    <th>{{ __('messages.type') }}</th>
                    <th>{{ __('messages.contact_person') }}</th>
                    <th>{{ __('messages.email') }}</th>
                    <th>{{ __('messages.phone') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($partners as $partner)
                <tr>
                    <td><strong>{{ $partner->partner_name }}</strong></td>
                    <td>
                        <span class="badge badge-{{ $partner->partner_type === 'supplier' ? 'success' : ($partner->partner_type === 'customer' ? 'warning' : 'info') }}">
                            {{ ucfirst($partner->partner_type) }}
                        </span>
                    </td>
                    <td>{{ $partner->contact_person ?? '-' }}</td>
                    <td>{{ $partner->email ?? '-' }}</td>
                    <td>{{ $partner->phone ?? '-' }}</td>
                    <td>
                        <a href="{{ route('master-data.partners.edit', $partner) }}" class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">{{ __('messages.edit') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);">{{ __('messages.no_partners_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{ $partners->links() }}
</div>
@endsection
