<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 14px;
            background: #f4f5f7;
            color: #1e1e2e;
            min-height: 100vh;
        }

        /* ── Nav ── */
        .nav {
            background: #1e1e2e;
            color: #cdd6f4;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            height: 52px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav__brand {
            font-weight: 700;
            font-size: .95rem;
            color: #cba6f7;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .nav__links { display: flex; align-items: center; gap: 1.25rem; }

        .nav__link {
            color: #a6adc8;
            text-decoration: none;
            font-size: .84rem;
            transition: color .15s;
        }

        .nav__link:hover, .nav__link.active { color: #cdd6f4; }

        .nav__logout {
            background: none;
            border: 1px solid #45475a;
            color: #a6adc8;
            border-radius: 5px;
            padding: .3rem .75rem;
            font-size: .8rem;
            cursor: pointer;
            transition: border-color .15s, color .15s;
        }

        .nav__logout:hover { border-color: #cdd6f4; color: #cdd6f4; }

        /* ── Page wrapper ── */
        .page { max-width: 1200px; margin: 0 auto; padding: 1.75rem 1.5rem; }

        /* ── Flash ── */
        .flash {
            padding: .7rem 1rem;
            border-radius: 7px;
            font-size: .84rem;
            margin-bottom: 1.25rem;
        }

        .flash--success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .flash--error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }

        /* ── Card ── */
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0,0,0,.06);
            overflow: hidden;
        }

        .card__header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f0f0f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .card__title { font-size: .95rem; font-weight: 700; }

        /* ── Table ── */
        table { width: 100%; border-collapse: collapse; }

        th {
            text-align: left;
            font-size: .75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b7280;
            padding: .65rem 1rem;
            background: #fafafa;
            border-bottom: 1px solid #f0f0f5;
            white-space: nowrap;
        }

        td {
            padding: .7rem 1rem;
            border-bottom: 1px solid #f9f9fb;
            vertical-align: middle;
            font-size: .875rem;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafafa; }

        /* ── Badges ── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: .2rem .6rem;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .badge--new         { background: #eff6ff; color: #1d4ed8; }
        .badge--in_progress { background: #fffbeb; color: #b45309; }
        .badge--completed   { background: #f0fdf4; color: #166534; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .45rem .9rem;
            border-radius: 6px;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: opacity .15s;
        }

        .btn:hover { opacity: .85; }
        .btn--primary  { background: #6366f1; color: #fff; }
        .btn--outline  { background: none; border: 1.5px solid #d1d5db; color: #374151; }
        .btn--danger   { background: #ef4444; color: #fff; }
        .btn--sm       { padding: .3rem .65rem; font-size: .75rem; }

        /* ── Form controls ── */
        .form-inline {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            align-items: flex-end;
        }

        .form-inline label { font-size: .75rem; font-weight: 600; color: #6b7280; display: block; margin-bottom: .2rem; }

        .form-inline input,
        .form-inline select {
            padding: .45rem .7rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 6px;
            font-size: .84rem;
            color: #1e1e2e;
            background: #fff;
            outline: none;
            transition: border-color .15s;
        }

        .form-inline input:focus,
        .form-inline select:focus { border-color: #6366f1; }

        /* ── Pagination ── */
        .pagination {
            display: flex;
            gap: .3rem;
            padding: .85rem 1rem;
            border-top: 1px solid #f0f0f5;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            padding: 0 .5rem;
            border-radius: 5px;
            font-size: .8rem;
            text-decoration: none;
            color: #374151;
            border: 1px solid #e5e7eb;
            transition: background .15s;
        }

        .pagination a:hover { background: #f3f4f6; }
        .pagination span[aria-current] { background: #6366f1; color: #fff; border-color: #6366f1; }

        /* ── Detail grid ── */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        .detail-section { margin-bottom: 1.5rem; }

        .detail-section__title {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #9ca3af;
            margin-bottom: .75rem;
        }

        .detail-row {
            display: flex;
            gap: .5rem;
            margin-bottom: .5rem;
            font-size: .875rem;
        }

        .detail-row__label { color: #6b7280; min-width: 110px; flex-shrink: 0; }

        .content-box {
            background: #fafafa;
            border: 1px solid #f0f0f5;
            border-radius: 7px;
            padding: .85rem 1rem;
            font-size: .875rem;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        /* ── File list ── */
        .file-list { display: flex; flex-direction: column; gap: .4rem; }

        .file-item {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .5rem .75rem;
            background: #fafafa;
            border: 1px solid #f0f0f5;
            border-radius: 6px;
            font-size: .84rem;
        }

        .file-item__name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-item__size { color: #9ca3af; font-size: .75rem; white-space: nowrap; }

        @media (max-width: 640px) {
            .detail-grid { grid-template-columns: 1fr; }
            .form-inline { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>

<nav class="nav">
    <a href="{{ route('admin.tickets.index') }}" class="nav__brand">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
        </svg>
        CRM Admin
    </a>
    <div class="nav__links">
        <a href="{{ route('admin.tickets.index') }}" class="nav__link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">Tickets</a>
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="nav__logout">Log out</button>
        </form>
    </div>
</nav>

<div class="page">
    @if(session('success'))
        <div class="flash flash--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash--error">{{ session('error') }}</div>
    @endif

    @yield('content')
</div>

</body>
</html>
