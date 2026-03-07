<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Offline — PDR Shop Pro</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            background: #0f172a;
            color: #f8fafc;
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            text-align: center;
            max-width: 360px;
        }
        .icon {
            width: 64px;
            height: 64px;
            background: #1e293b;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem; }
        p { color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem; }
        button {
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
        }
        button:active { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#94a3b8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <h1>You're Offline</h1>
        <p>PDR Shop Pro needs an internet connection to load your shop data. Check your connection and try again.</p>
        <button onclick="window.location.reload()">Try Again</button>
    </div>
</body>
</html>
