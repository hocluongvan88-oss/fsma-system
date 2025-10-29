@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">{{ __('messages.lead_details') }}</h1>
        <div class="space-x-2">
            <a href="{{ route('admin.leads.edit', $lead) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                {{ __('messages.edit') }}
            </a>
            <a href="{{ route('admin.leads.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                {{ __('messages.back') }}
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Lead Information -->
        <div class="md:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h2 class="text-xl font-bold mb-4">{{ __('messages.basic_information') }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.full_name') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->full_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.email') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->email }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.phone') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.company') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->company_name ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.industry') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->industry ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.status') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->status_label }}</p>
                    </div>
                </div>
            </div>

            @if($lead->message)
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <h2 class="text-xl font-bold mb-4">{{ __('messages.message') }}</h2>
                <p>{{ $lead->message }}</p>
            </div>
            @endif

            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">{{ __('messages.tracking_information') }}</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.source') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->source_label }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.utm_source') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->utm_source ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.utm_medium') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->utm_medium ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.utm_campaign') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->utm_campaign ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.ip_address') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->ip_address ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold mb-4">{{ __('messages.additional_information') }}</h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.created_date') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.updated_date') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($lead->contacted_at)
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.contacted_date') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->contacted_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                    @if($lead->notes)
                    <div>
                        <label class="text-sm text-gray-600">{{ __('messages.notes') }}</label>
                        <p class="text-lg font-semibold">{{ $lead->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
