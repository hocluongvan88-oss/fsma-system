<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-12 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Email Preview</h1>
                <p class="text-gray-600 mb-8">Select an email template to preview:</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($emails as $key => $label)
                        <a href="{{ route('email.preview', ['emailType' => $key]) }}" 
                           class="block p-6 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                            <h3 class="text-lg font-semibold text-blue-900">{{ $label }}</h3>
                            <p class="text-sm text-blue-700 mt-2">View preview →</p>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8 p-4 bg-gray-50 rounded border border-gray-200">
                    <h3 class="font-semibold text-gray-900 mb-2">Available Locales:</h3>
                    <div class="flex gap-2">
                        @foreach(['en', 'es', 'fr', 'de', 'pt', 'it'] as $locale)
                            <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm">{{ strtoupper($locale) }}</span>
                        @endforeach
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Add <code class="bg-gray-200 px-1 rounded">?locale=xx</code> to preview in different languages</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
