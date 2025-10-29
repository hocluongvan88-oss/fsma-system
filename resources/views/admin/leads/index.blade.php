@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">{{ __('messages.leads_management') }}</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.leads.statistics') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                {{ __('messages.statistics') }}
            </a>
            <a href="{{ route('admin.leads.export') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                {{ __('messages.export_csv') }}
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-semibold">{{ __('messages.total_leads') }}</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-semibold">{{ __('messages.new_leads') }}</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['new'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-semibold">{{ __('messages.contacted_leads') }}</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['contacted'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-semibold">{{ __('messages.conversion_rate') }}</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['conversion_rate'] }}%</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold mb-2">{{ __('messages.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_by_name_email_company') }}" class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">{{ __('messages.status') }}</label>
                <select name="status" class="w-full px-3 py-2 border rounded">
                    <option value="all">{{ __('messages.all') }}</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>{{ __('messages.new') }}</option>
                    <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>{{ __('messages.contacted') }}</option>
                    <option value="qualified" {{ request('status') === 'qualified' ? 'selected' : '' }}>{{ __('messages.qualified') }}</option>
                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>{{ __('messages.converted') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">{{ __('messages.from_date') }}</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full px-3 py-2 border rounded">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-2">{{ __('messages.to_date') }}</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full px-3 py-2 border rounded">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    {{ __('messages.filter') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Leads Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">{{ __('messages.full_name') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">{{ __('messages.email') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">{{ __('messages.company') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">{{ __('messages.status') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">{{ __('messages.created_date') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $lead->full_name }}</td>
                    <td class="px-6 py-4">{{ $lead->email }}</td>
                    <td class="px-6 py-4">{{ $lead->company_name ?? '-' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            {{ $lead->status === 'new' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $lead->status === 'contacted' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $lead->status === 'qualified' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $lead->status === 'converted' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $lead->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ $lead->status_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4">{{ $lead->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 space-x-2">
                        <a href="{{ route('admin.leads.show', $lead) }}" class="text-blue-500 hover:text-blue-700">{{ __('messages.view') }}</a>
                        <a href="{{ route('admin.leads.edit', $lead) }}" class="text-green-500 hover:text-green-700">{{ __('messages.edit') }}</a>
                        <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" style="display:inline;" onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">{{ __('messages.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">{{ __('messages.no_leads_found') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $leads->links() }}
    </div>
</div>
@endsection
