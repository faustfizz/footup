<?php ?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>500 Server Error</title>
    <style>
        body { font-family: sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 100px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #d9534f; margin: 0; }
        p { color: #666; line-height: 1.6; }
        .debug { background: #f8f9fa; padding: 15px; border-left: 4px solid #d9534f; margin-top: 20px; font-family: monospace; font-size: 12px; overflow-x: auto; }
        a { color: #0275d8; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>500 - Server Error</h1>
        <p>Something went wrong on our end. We're working to fix it.</p>
        <?php if (config('app.debug')): ?>
            <div class="debug">
                <strong>Error:</strong> <?= htmlspecialchars($message ?? 'Unknown error') ?>
            </div>
        <?php endif; ?>
        <p><a href="/">← Back to home</a></p>
    </div>
</body>
</html>
