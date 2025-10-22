@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">{{ __('messages.e_signature_audit_trail') }}</h1>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.search') }}</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_user_email_record_type') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.user') }}</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">{{ __('messages.all_users') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.status') }}</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>{{ __('messages.revoked') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.date_from') }}</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.date_to') }}</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.has_timestamp') }}</label>
                <select name="has_timestamp" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="yes" {{ request('has_timestamp') == 'yes' ? 'selected' : '' }}>{{ __('messages.yes') }}</option>
                    <option value="no" {{ request('has_timestamp') == 'no' ? 'selected' : '' }}>{{ __('messages.no') }}</option>
                </select>
            </div>

            <div class="md:col-span-2 lg:col-span-3 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ __('messages.filter') }}
                </button>
                <a href="{{ route('admin.e-signatures.audit-trail') }}" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('messages.reset') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Trail Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">{{ __('messages.signed_at') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">{{ __('messages.user') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">{{ __('messages.record_type') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">{{ __('messages.action') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">{{ __('messages.status') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">{{ __('messages.timestamp') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($signatures as $signature)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">{{ $signature->signed_at->format('Y-m-d H:i:s') }}</td>
                        <td class="px-6 py-4 text-sm">{{ $signature->user->full_name }}</td>
                        <td class="px-6 py-4 text-sm font-mono">{{ $signature->record_type }}</td>
                        <td class="px-6 py-4 text-sm">{{ $signature->action }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($signature->is_revoked)
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">{{ __('messages.revoked') }}</span>
                            @else
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">{{ __('messages.active') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($signature->timestamp_verified_at)
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ __('messages.verified') }}</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">{{ __('messages.none') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.e-signatures.show', $signature) }}" class="text-blue-600 hover:text-blue-800">
                                {{ __('messages.view') }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            {{ __('messages.no_signatures_found') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $signatures->links() }}
    </div>
</div>
@endsection
