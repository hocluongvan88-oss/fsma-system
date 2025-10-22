@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="{{ route('admin.errors.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 mb-4 inline-block">
            ← {{ __('messages.back_to_errors') }}
        </a>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('messages.error_details') }}</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Error Details -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $errorLog->error_type }}</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $errorLog->error_message }}</p>
                    </div>
                    <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                        @if($errorLog->severity === 'critical') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                        @elseif($errorLog->severity === 'error') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                        @elseif($errorLog->severity === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                        {{ ucfirst($errorLog->severity) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.error_code') }}</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $errorLog->error_code ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.frequency') }}</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $errorLog->frequency }}x</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.file') }}</p>
                        <p class="text-sm font-mono text-gray-900 dark:text-white break-all">{{ $errorLog->file_path }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.line') }}</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $errorLog->line_number }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('messages.url') }}</p>
                    <p class="text-sm font-mono text-gray-900 dark:text-white break-all bg-gray-100 dark:bg-gray-700 p-3 rounded">{{ $errorLog->url }}</p>
                </div>

                <div class="mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('messages.stack_trace') }}</p>
                    <div class="bg-gray-900 text-gray-100 p-4 rounded font-mono text-xs overflow-x-auto">
                        @foreach($errorLog->stack_trace as $frame)
                        <div class="mb-2">
                            <span class="text-blue-400">{{ $frame['file'] ?? 'unknown' }}</span>:<span class="text-yellow-400">{{ $frame['line'] ?? 0 }}</span>
                            <span class="text-green-400">{{ $frame['function'] ?? 'unknown' }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($errorLog->context)
                <div class="mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('messages.context') }}</p>
                    <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-xs overflow-x-auto text-gray-900 dark:text-white">{{ json_encode($errorLog->context, JSON_PRETTY_PRINT) }}</pre>
                </div>
                @endif

                @if(!$errorLog->is_resolved)
                <form method="POST" action="{{ route('admin.errors.resolve', $errorLog->id) }}" class="mt-6">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.resolution_notes') }}</label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="{{ __('messages.add_notes_resolution') }}"></textarea>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        {{ __('messages.mark_as_resolved') }}
                    </button>
                </form>
                @else
                <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-lg p-4 mt-6">
                    <p class="text-sm text-green-800 dark:text-green-200">
                        <strong>{{ __('messages.resolved') }}:</strong> {{ $errorLog->resolved_at->format('M d, Y H:i') }} {{ __('messages.by') }} {{ $errorLog->resolvedBy?->name }}
                    </p>
                    @if($errorLog->notes)
                    <p class="text-sm text-green-700 dark:text-green-300 mt-2">{{ $errorLog->notes }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Error Info -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('messages.error_information') }}</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.user') }}</p>
                        <p class="text-gray-900 dark:text-white">{{ $errorLog->user?->email ?? __('messages.anonymous') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.ip_address') }}</p>
                        <p class="text-gray-900 dark:text-white font-mono">{{ $errorLog->ip_address }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.method') }}</p>
                        <p class="text-gray-900 dark:text-white">{{ $errorLog->method }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.first_occurred') }}</p>
                        <p class="text-gray-900 dark:text-white">{{ $errorLog->created_at->format('M d, Y H:i:s') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.last_occurred') }}</p>
                        <p class="text-gray-900 dark:text-white">{{ $errorLog->updated_at->format('M d, Y H:i:s') }}</p>
                    </div>
                </div>
            </div>

            <!-- Similar Errors -->
            @if($similar->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ __('messages.similar_errors') }}</h3>
                
                <div class="space-y-3">
                    @foreach($similar as $error)
                    <a href="{{ route('admin.errors.show', $error->id) }}" class="block p-3 bg-gray-50 dark:bg-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $error->created_at->diffForHumans() }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $error->frequency }}x</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
