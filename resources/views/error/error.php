<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= $status ?></title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --error-color: #e74c3c;
            --bg-color: #f5f6fa;
            --text-color: #34495e;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 1rem;
        }
        
        .error-container {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 32rem;
            width: 100%;
            text-align: center;
        }
        
        .error-status {
            font-size: 4rem;
            font-weight: bold;
            color: var(--error-color);
            margin: 0;
            line-height: 1;
        }
        
        .error-message {
            margin: 1rem 0;
            color: var(--text-color);
            font-size: 1.25rem;
        }
        
        .error-details {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
            font-size: 0.875rem;
            color: var(--text-color);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 0.25rem;
            transition: background 0.2s;
        }
        
        .back-link:hover {
            background: #34495e;
        }
        
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #1a1a1a;
                --text-color: #e1e1e1;
                --primary-color: #3498db;
            }
            
            .error-container {
                background: #2d2d2d;
            }
            
            .error-details {
                border-color: #404040;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-status"><?= $status ?></h1>
        <div class="error-message">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php if (isset($exception) && method_exists($exception, 'getErrors')): ?>
            <div class="error-details">
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach ($exception->getErrors() as $field => $errors): ?>
                        <?php foreach ((array)$errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <a href="/" class="back-link">Return Home</a>
    </div>
</body>
</html>
