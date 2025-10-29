<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Preview - {{ $emailType }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Email Preview</h1>
                    <p class="text-gray-600 mt-1">{{ $emailType }} ({{ strtoupper($locale) }})</p>
                </div>
                <a href="{{ route('email.preview-index') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    ← Back
                </a>
            </div>

            <!-- Email Preview Container -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-50 p-4 border-b">
                    <p class="text-sm text-gray-600">
                        <!-- Use $subject variable instead of calling envelope() on string -->
                        <strong>Subject:</strong> {{ $subject }}
                    </p>
                </div>

                <!-- Email Content -->
                <div class="p-0">
                    {!! $htmlContent !!}
                </div>
            </div>

            <!-- Locale Switcher -->
            <div class="mt-6 bg-white rounded-lg shadow p-4">
                <p class="text-sm font-semibold text-gray-900 mb-3">Preview in other languages:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['en', 'es', 'fr', 'de', 'pt', 'it'] as $loc)
                        <a href="{{ route('email.preview', ['emailType' => $emailType, 'locale' => $loc]) }}"
                           class="px-3 py-2 rounded text-sm font-medium transition
                                  {{ $locale === $loc 
                                      ? 'bg-blue-600 text-white' 
                                      : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            {{ strtoupper($loc) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</body>
</html>
