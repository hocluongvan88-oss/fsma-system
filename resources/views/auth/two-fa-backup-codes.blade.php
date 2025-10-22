@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <!-- Translated backup codes page -->
        <h1 class="text-2xl font-bold mb-6">{{ __('messages.save_your_backup_codes') }}</h1>

        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-yellow-800">
                {{ __('messages.backup_codes_warning') }}
            </p>
        </div>

        <div class="bg-gray-100 p-4 rounded-lg mb-6 font-mono text-sm">
            @foreach($backupCodes as $code)
                <div class="mb-2">{{ $code }}</div>
            @endforeach
        </div>

        <div class="flex gap-2 mb-6">
            <button
                onclick="copyToClipboard()"
                class="flex-1 bg-gray-600 text-white py-2 rounded-md hover:bg-gray-700 transition"
            >
                {{ __('messages.copy_codes') }}
            </button>
            <button
                onclick="window.print()"
                class="flex-1 bg-gray-600 text-white py-2 rounded-md hover:bg-gray-700 transition"
            >
                {{ __('messages.print') }}
            </button>
        </div>

        <a
            href="{{ route('dashboard') }}"
            class="block w-full text-center bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition"
        >
            {{ __('messages.continue_to_dashboard') }}
        </a>
    </div>
</div>

<script>
function copyToClipboard() {
    const codes = document.querySelector('.bg-gray-100').innerText;
    navigator.clipboard.writeText(codes).then(() => {
        alert('{{ __('messages.backup_codes_copied') }}');
    });
}
</script>
@endsection
