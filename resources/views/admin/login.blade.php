<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #f4f5f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            width: 100%;
            max-width: 380px;
            padding: 2.25rem 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 1.75rem;
        }

        .logo__icon {
            width: 36px; height: 36px;
            border-radius: 9px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
        }

        .logo__text { font-size: 1rem; font-weight: 700; color: #1e1e2e; }

        .form-group { margin-bottom: 1rem; }

        label { display: block; font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: .3rem; }

        input {
            width: 100%;
            padding: .6rem .85rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 7px;
            font-size: .9rem;
            color: #1e1e2e;
            background: #fafafa;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,.12);
            background: #fff;
        }

        .error {
            font-size: .78rem;
            color: #ef4444;
            margin-top: .3rem;
        }

        .btn {
            width: 100%;
            padding: .7rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: .5rem;
            transition: opacity .15s;
        }

        .btn:hover { opacity: .9; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <div class="logo__icon" aria-hidden="true">
            <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
            </svg>
        </div>
        <span class="logo__text">CRM Admin</span>
    </div>

    <form method="POST" action="{{ route('admin.login.post') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autofocus autocomplete="email" required>
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>

        <button type="submit" class="btn">Sign in</button>
    </form>
</div>
</body>
</html>
