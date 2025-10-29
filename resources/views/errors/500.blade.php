<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
        }
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #f5576c;
            line-height: 1;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        .error-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .error-details {
            background-color: #f5f5f5;
            border-left: 4px solid #f5576c;
            padding: 15px;
            margin-bottom: 30px;
            text-align: left;
            border-radius: 4px;
            font-size: 13px;
            color: #555;
            max-height: 200px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
        }
        .error-link {
            display: inline-block;
            padding: 12px 30px;
            background-color: #f5576c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .error-link:hover {
            background-color: #f093fb;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <div class="error-title">Server Error</div>
        <div class="error-message">
            An error occurred while processing your request. Please try again later.
        </div>
        @if(isset($exception) && config('app.debug'))
            <div class="error-details">
                <strong>Error:</strong> {{ $exception->getMessage() }}<br>
                <strong>File:</strong> {{ $exception->getFile() }}<br>
                <strong>Line:</strong> {{ $exception->getLine() }}
            </div>
        @endif
        <a href="{{ route('email.preview-index') }}" class="error-link">Back to Email Templates</a>
    </div>
</body>
</html>
