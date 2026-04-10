<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #f4f5f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e1e2e;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            padding: 2.5rem 2rem;
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .logo {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
        }
        h1 { font-size: 1.25rem; font-weight: 700; margin-bottom: .4rem; }
        p  { font-size: .875rem; color: #6b7280; margin-bottom: 1.75rem; line-height: 1.55; }
        .links { display: flex; flex-direction: column; gap: .65rem; }
        a {
            display: block;
            padding: .65rem 1rem;
            border-radius: 7px;
            font-size: .9rem;
            font-weight: 600;
            text-decoration: none;
            transition: opacity .15s;
        }
        a:hover { opacity: .85; }
        .btn-primary  { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; }
        .btn-outline   { border: 1.5px solid #d1d5db; color: #374151; }
        .btn-secondary { background: #f3f4f6; color: #374151; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo" aria-hidden="true">
        <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
        </svg>
    </div>
    <h1>{{ config('app.name') }}</h1>
    <p>Mini CRM — ticket management, embeddable widget, and REST API.</p>
    <div class="links">
        <a href="{{ route('admin.login') }}" class="btn-primary">Admin Panel</a>
        <a href="{{ route('widget') }}" class="btn-outline">Contact Widget</a>
        <a href="{{ route('widget.embed') }}" class="btn-secondary">Widget Embed Guide</a>
        <a href="/api/documentation" class="btn-secondary">API Docs (Swagger)</a>
    </div>
</div>
</body>
</html>
