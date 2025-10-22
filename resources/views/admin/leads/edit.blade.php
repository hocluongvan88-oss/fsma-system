@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">{{ __('messages.edit_lead') }}</h1>

    <div class="bg-white p-6 rounded-lg shadow max-w-2xl">
        <form method="POST" action="{{ route('admin.leads.update', $lead) }}">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-2">{{ __('messages.full_name') }} *</label>
                <input type="text" name="full_name" value="{{ $lead->full_name }}" required class="w-full px-4 py-2 border rounded @error('full_name') border-red-500 @enderror">
                @error('full_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-2">{{ __('messages.email') }} *</label>
                <input type="email" name="email" value="{{ $lead->email }}" required class="w-full px-4 py-2 border rounded @error('email') border-red-500 @enderror">
                @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-2">{{ __('messages.phone') }}</label>
                <input type="tel" name="phone" value="{{ $lead->phone }}" class="w-full px-4 py-2 border rounded">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-2">{{ __('messages.company') }}</label>
                <input type="text" name="company_name" value="{{ $lead->company_name }}" class="w-full px-4 py-2 border rounded">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-2">{{ __('messages.industry') }}</label>
                <input type="text" name="industry" value="{{ $lead->industry }}" class="w-full px-4 py-2 border rounded">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-2">{{ __('messages.status') }} *</label>
                <select name="status" required class="w-full px-4 py-2 border rounded">
                    <option value="new" {{ $lead->status === 'new' ? 'selected' : '' }}>{{ __('messages.new') }}</option>
                    <option value="contacted" {{ $lead->status === 'contacted' ? 'selected' : '' }}>{{ __('messages.contacted') }}</option>
                    <option value="qualified" {{ $lead->status === 'qualified' ? 'selected' : '' }}>{{ __('messages.qualified') }}</option>
                    <option value="converted" {{ $lead->status === 'converted' ? 'selected' : '' }}>{{ __('messages.converted') }}</option>
                    <option value="rejected" {{ $lead->status === 'rejected' ? 'selected' : '' }}>{{ __('messages.rejected') }}</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold mb-2">{{ __('messages.notes') }}</label>
                <textarea name="notes" rows="4" class="w-full px-4 py-2 border rounded">{{ $lead->notes }}</textarea>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded">
                    {{ __('messages.save') }}
                </button>
                <a href="{{ route('admin.leads.show', $lead) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded">
                    {{ __('messages.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
