<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Scanner') - {{ \App\Models\Setting::find(1)->app_name ?? config('app.name') }}</title>
    <style>
        :root {
            --primary: {{ \App\Models\Setting::find(1)->primary_color ?? '#6d28d9' }};
            --bg: #0f1115;
            --card: #1a1d24;
            --text: #f5f5f7;
            --muted: #9aa0ab;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: var(--card);
            position: sticky;
            top: 0;
            z-index: 10;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .topbar a { color: var(--muted); text-decoration: none; font-size: 14px; }
        .topbar h1 { font-size: 17px; margin: 0; font-weight: 600; }

        .container { max-width: 560px; margin: 0 auto; padding: 16px; }

        .card {
            background: var(--card);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 14px;
            border: 1px solid rgba(255,255,255,0.06);
        }

        .btn {
            display: inline-block;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 18px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            width: 100%;
        }

        .btn.secondary { background: transparent; border: 1px solid rgba(255,255,255,0.2); color: var(--text); }

        input[type=text], input[type=email], input[type=password] {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.15);
            background: #10131a;
            color: var(--text);
            font-size: 15px;
            margin-bottom: 12px;
        }

        label { display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px; }

        .flash {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 14px;
            font-size: 14px;
        }

        .flash.error { background: rgba(239,68,68,0.15); color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
        .flash.success { background: rgba(34,197,94,0.15); color: #86efac; border: 1px solid rgba(34,197,94,0.3); }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.success { background: rgba(34,197,94,0.15); color: #86efac; }
        .badge.already_used { background: rgba(245,158,11,0.15); color: #fcd34d; }
        .badge.cancelled { background: rgba(148,163,184,0.15); color: #cbd5e1; }
        .badge.invalid { background: rgba(239,68,68,0.15); color: #fca5a5; }
        .badge.expired { background: rgba(239,68,68,0.15); color: #fca5a5; }

        .muted { color: var(--muted); font-size: 13px; }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { text-align: left; padding: 8px 4px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        th { color: var(--muted); font-weight: 500; }
    </style>
    @yield('head')
</head>

<body>
    @yield('body')
</body>

</html>
