@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ __('messages.error_tracking_dashboard') }}</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ __('messages.monitor_manage_system_errors') }}</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('messages.total_errors') }}</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_errors'] }}</p>
                </div>
                <div class="text-4xl text-blue-500">📊</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('messages.unresolved') }}</p>
                    <p class="text-3xl font-bold text-red-600">{{ $stats['unresolved_errors'] }}</p>
                </div>
                <div class="text-4xl text-red-500">⚠️</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('messages.critical') }}</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $stats['critical_errors'] }}</p>
                </div>
                <div class="text-4xl text-orange-500">🔥</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('messages.error_types') }}</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $stats['by_type']->count() }}</p>
                </div>
                <div class="text-4xl text-purple-500">🏷️</div>
            </div>
        </div>
    </div>

    <!-- Trending Errors -->
    @if($trending->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('messages.trending_errors') }}</h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($trending as $error)
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $error->error_type }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $error->error_message }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">{{ $error->file_path }}:{{ $error->line_number }}</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                            @if($error->severity === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @elseif($error->severity === 'error') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                            @elseif($error->severity === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                            {{ ucfirst($error->severity) }}
                        </span>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $error->frequency }}x</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.severity') }}</label>
                <select name="severity" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('messages.all_severities') }}</option>
                    <option value="critical" @selected(request('severity') === 'critical')>{{ __('messages.critical') }}</option>
                    <option value="error" @selected(request('severity') === 'error')>{{ __('messages.error') }}</option>
                    <option value="warning" @selected(request('severity') === 'warning')>{{ __('messages.warning') }}</option>
                    <option value="info" @selected(request('severity') === 'info')>{{ __('messages.info') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.status') }}</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                    <option value="">{{ __('messages.all_status') }}</option>
                    <option value="unresolved" @selected(request('status') === 'unresolved')>{{ __('messages.unresolved') }}</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>{{ __('messages.resolved') }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.search') }}</label>
                <input type="text" name="search" placeholder="{{ __('messages.search_errors') }}" value="{{ request('search') }}" 
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    {{ __('messages.filter') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Errors Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.error_type') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.message') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.severity') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.frequency') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.user') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.date') }}</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.action') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($errors as $error)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $error->error_type }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 truncate max-w-xs">{{ $error->error_message }}</td>
                    <td class="px-6 py-4 text-sm">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                            @if($error->severity === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                            @elseif($error->severity === 'error') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                            @elseif($error->severity === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                            {{ ucfirst($error->severity) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $error->frequency }}x</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $error->user?->email ?? __('messages.anonymous') }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $error->created_at->diffForHumans() }}</td>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('admin.errors.show', $error->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ __('messages.view') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-600 dark:text-gray-400">
                        {{ __('messages.no_errors_found') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $errors->links() }}
    </div>
</div>
@endsection
