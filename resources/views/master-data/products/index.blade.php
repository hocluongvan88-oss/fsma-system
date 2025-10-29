@extends('layouts.app')

@section('title', __('messages.products'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <form method="GET" style="display: flex; gap: 0.75rem; flex: 1;">
        <input type="text" name="search" class="form-input" placeholder="{{ __('messages.search_products') }}" value="{{ request('search') }}" style="max-width: 300px;">
        
        <select name="ftl" class="form-select" style="max-width: 150px;">
            <option value="">{{ __('messages.all_products') }}</option>
            <option value="yes" {{ request('ftl') === 'yes' ? 'selected' : '' }}>{{ __('messages.ftl_only') }}</option>
            <option value="no" {{ request('ftl') === 'no' ? 'selected' : '' }}>{{ __('messages.non_ftl') }}</option>
        </select>
        
        <select name="category" class="form-select" style="max-width: 150px;">
            <option value="">{{ __('messages.all_categories') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                    {{ $category }}
                </option>
            @endforeach
        </select>
        
        <button type="submit" class="btn btn-secondary">{{ __('messages.filter') }}</button>
        @if(request()->hasAny(['search', 'ftl', 'category']))
            <a href="{{ route('master-data.products.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
        @endif
    </form>
    
    <a href="{{ route('master-data.products.create') }}" class="btn btn-primary">{{ __('messages.add_product') }}</a>
</div>

<div class="card">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.product_name') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th>{{ __('messages.ftl') }}</th>
                    <th>{{ __('messages.unit') }}</th>
                    <th>{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td><strong>{{ $product->sku }}</strong></td>
                    <td>{{ $product->product_name }}</td>
                    <td>{{ $product->category ?? '-' }}</td>
                    <td>
                        @if($product->is_ftl)
                            <span class="badge badge-success">{{ __('messages.yes') }}</span>
                        @else
                            <span class="badge badge-info">{{ __('messages.no') }}</span>
                        @endif
                    </td>
                    <td>{{ $product->unit_of_measure }}</td>
                    <td>
                        <a href="{{ route('master-data.products.edit', $product) }}" class="btn btn-secondary" style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">{{ __('messages.edit') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted);">{{ __('messages.no_products_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{ $products->links() }}
</div>
@endsection
