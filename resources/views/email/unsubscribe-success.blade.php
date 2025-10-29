<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe Successful</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        h1 {
            color: #28a745;
            margin: 0 0 10px 0;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin: 10px 0;
        }
        .email {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Unsubscribe Successful</h1>
        <p>You have been successfully unsubscribed from email notifications.</p>
        <p>Email: <span class="email">{{ $email }}</span></p>
        <p>You will no longer receive email notifications from us.</p>
    </div>
</body>
</html>
