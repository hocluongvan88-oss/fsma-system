@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">{{ __('messages.leads_statistics') }}</h1>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-semibold">{{ __('messages.total_leads') }}</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_leads'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-semibold">{{ __('messages.new_leads') }}</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['new_leads'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-semibold">{{ __('messages.converted') }}</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['converted_leads'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-gray-600 text-sm font-semibold">{{ __('messages.conversion_rate') }}</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['conversion_rate'] }}%</p>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">{{ __('messages.status_distribution') }}</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>{{ __('messages.new') }}</span>
                    <span class="font-semibold">{{ $stats['new_leads'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('messages.contacted') }}</span>
                    <span class="font-semibold">{{ $stats['contacted_leads'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('messages.qualified') }}</span>
                    <span class="font-semibold">{{ $stats['qualified_leads'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('messages.converted') }}</span>
                    <span class="font-semibold">{{ $stats['converted_leads'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">{{ __('messages.time_period') }}</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span>{{ __('messages.this_week') }}</span>
                    <span class="font-semibold">{{ $stats['this_week'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ __('messages.this_month') }}</span>
                    <span class="font-semibold">{{ $stats['this_month'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Industries -->
    @if(!empty($stats['leads_by_industry']))
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-xl font-bold mb-4">{{ __('messages.top_industries') }}</h2>
        <div class="space-y-2">
            @foreach($stats['leads_by_industry'] as $industry => $count)
            <div class="flex justify-between">
                <span>{{ $industry ?? __('messages.unspecified') }}</span>
                <span class="font-semibold">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex space-x-4">
        <a href="{{ route('admin.leads.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
            {{ __('messages.back') }}
        </a>
    </div>
</div>
@endsection
