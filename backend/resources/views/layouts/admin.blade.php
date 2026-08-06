<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SnapTrack Admin' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --bg-0: #07111f;
            --bg-1: #0f1b33;
            --bg-2: #dbeafe;
            --surface: rgba(255, 255, 255, 0.78);
            --surface-strong: #ffffff;
            --surface-dark: rgba(15, 23, 42, 0.82);
            --border: rgba(148, 163, 184, 0.24);
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-2: #7c3aed;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #d97706;
            --shadow: 0 30px 80px rgba(15, 23, 42, 0.16);
        }

        * { box-sizing: border-box; }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            color: var(--text);
            font-family: "Inter", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.16), transparent 26%),
                radial-gradient(circle at top right, rgba(124, 58, 237, 0.16), transparent 24%),
                linear-gradient(135deg, #eef6ff 0%, #e5edf7 55%, #dbeafe 100%);
            min-height: 100vh;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,0.25) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.18) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,0.15), transparent 75%);
        }

        a {
            color: inherit;
        }

        .app-shell {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 24px;
            padding: 24px;
        }

        .sidebar {
            position: sticky;
            top: 24px;
            align-self: start;
            border: 1px solid var(--border);
            border-radius: 28px;
            padding: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(255,255,255,0.70));
            backdrop-filter: blur(16px);
            box-shadow: var(--shadow);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 22px;
        }

        .brand-logo {
            width: 54px;
            height: 54px;
            border-radius: 8px;
            object-fit: cover;
            flex: 0 0 auto;
            background: rgba(255,255,255,0.88);
            padding: 6px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            color: white;
            font-family: "Space Grotesk", sans-serif;
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.28);
        }

        .brand h1 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 22px;
            letter-spacing: -0.03em;
        }

        .brand p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .nav {
            display: grid;
            gap: 8px;
            margin-top: 14px;
        }

        .nav a, .nav button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            width: 100%;
            padding: 13px 14px;
            border-radius: 16px;
            border: 1px solid transparent;
            text-decoration: none;
            font-weight: 600;
            color: #1e293b;
            background: rgba(255,255,255,0.72);
            transition: transform 140ms ease, box-shadow 140ms ease, border-color 140ms ease;
        }

        .nav a:hover, .nav button:hover {
            transform: translateY(-1px);
            border-color: rgba(37, 99, 235, 0.20);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
        }

        .nav .active {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(124, 58, 237, 0.10));
            border-color: rgba(37, 99, 235, 0.22);
            color: #0f172a;
        }

        .nav .logout {
            background: rgba(220, 38, 38, 0.08);
        }

        .sidebar-card {
            margin-top: 18px;
            padding: 16px;
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.96));
            color: white;
        }

        .sidebar-card .muted {
            color: rgba(226, 232, 240, 0.76);
        }

        .workspace {
            min-width: 0;
        }

        .page-wrap {
            border: 1px solid var(--border);
            border-radius: 30px;
            background: linear-gradient(180deg, rgba(255,255,255,0.90), rgba(255,255,255,0.78));
            backdrop-filter: blur(18px);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .page-header {
            padding: 22px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.18);
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.10), rgba(124, 58, 237, 0.06), transparent);
        }

        .page-header h2 {
            margin: 0;
            font-size: 22px;
            font-family: "Space Grotesk", sans-serif;
            letter-spacing: -0.03em;
        }

        .page-header p {
            margin: 5px 0 0;
            color: var(--muted);
        }

        .content {
            padding: 24px;
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        .stats {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        }

        .card {
            background: var(--surface-strong);
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 24px;
            padding: 18px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .card.soft {
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.92));
        }

        .card h2, .card h3, .card p {
            margin-top: 0;
        }

        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
        }

        .section-title h3 {
            margin: 0;
            font-size: 18px;
            font-family: "Space Grotesk", sans-serif;
        }

        .muted {
            color: var(--muted);
        }

        .badge, .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .badge {
            background: rgba(37, 99, 235, 0.10);
            color: var(--primary);
        }

        .status-pill {
            border: 1px solid transparent;
        }

        .status-pill.online {
            background: rgba(22, 163, 74, 0.12);
            color: #15803d;
        }

        .status-pill.away {
            background: rgba(217, 119, 6, 0.12);
            color: #b45309;
        }

        .status-pill.offline {
            background: rgba(220, 38, 38, 0.12);
            color: #b91c1c;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
        }

        .table th, .table td {
            padding: 10px 10px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.16);
            text-align: left;
            vertical-align: top;
        }

        .table th {
            color: #334155;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .table tr:hover td {
            background: rgba(37, 99, 235, 0.03);
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .actions a, .actions button, .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border: 1px solid rgba(148, 163, 184, 0.24);
            background: white;
            color: #0f172a;
            padding: 11px 16px;
            border-radius: 14px;
            cursor: pointer;
            font-weight: 700;
            transition: transform 140ms ease, box-shadow 140ms ease, border-color 140ms ease;
        }

        .actions a:hover, .actions button:hover, .btn:hover {
            transform: translateY(-1px);
            border-color: rgba(37, 99, 235, 0.22);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
        }

        .actions a.primary, .btn.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: white;
            border-color: transparent;
        }

        .actions a.ghost {
            background: rgba(255,255,255,0.76);
        }

        .pagination {
            margin-top: 16px;
        }

        .compact-pagination {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding-top: 14px;
            border-top: 1px solid rgba(148, 163, 184, 0.16);
        }

        .compact-pagination__summary {
            font-size: 12px;
            color: var(--muted);
            white-space: nowrap;
        }

        .compact-pagination__nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }

        .compact-pagination__item,
        .compact-pagination__ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, 0.22);
            background: white;
            text-decoration: none;
            color: #0f172a;
            font-size: 13px;
            line-height: 1;
        }

        .compact-pagination__item.is-active {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: white;
            border-color: transparent;
            font-weight: 700;
        }

        .compact-pagination__item.is-disabled {
            opacity: 0.45;
            pointer-events: none;
        }

        .compact-pagination__ellipsis {
            border-color: transparent;
            background: transparent;
            min-width: 20px;
        }

        .form-row {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #334155;
        }

        input, select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, 0.28);
            font: inherit;
            background: rgba(255,255,255,0.92);
        }

        input:focus, select:focus {
            outline: 2px solid rgba(37, 99, 235, 0.14);
            border-color: rgba(37, 99, 235, 0.30);
        }

        .preview {
            width: 120px;
            height: auto;
            border-radius: 16px;
            border: 1px solid rgba(148, 163, 184, 0.24);
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.10);
        }

        .hero {
            display: grid;
            grid-template-columns: 1.4fr 0.9fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        .hero-main {
            padding: 24px;
            border-radius: 28px;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 54%, #7c3aed 100%);
            color: white;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.20);
        }

        .hero-main h2 {
            margin: 8px 0 10px;
            font-family: "Space Grotesk", sans-serif;
            font-size: 36px;
            letter-spacing: -0.04em;
        }

        .hero-main p {
            margin: 0;
            max-width: 680px;
            color: rgba(255,255,255,0.86);
            line-height: 1.6;
        }

        .hero-side {
            display: grid;
            gap: 18px;
        }

        .mini-stat {
            padding: 20px;
            border-radius: 24px;
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(148, 163, 184, 0.22);
        }

        .mini-stat strong {
            display: block;
            font-size: 28px;
            margin-top: 6px;
            font-family: "Space Grotesk", sans-serif;
        }

        .live-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            background: currentColor;
            box-shadow: 0 0 0 5px rgba(255,255,255,0.18);
        }

        @media (max-width: 1100px) {
            .app-shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: relative;
                top: auto;
            }

            .hero {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .app-shell {
                padding: 14px;
                gap: 14px;
            }

            .page-header, .actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .hero-main h2 {
                font-size: 28px;
            }

            .table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <img class="brand-logo" src="{{ asset('assets/img/icon.png') }}" alt="SnapTrack logo">
            <div>
                <h1>SnapTrack</h1>
                <p>Workforce intelligence console</p>
            </div>
        </div>

        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span>Dashboard</span>
                <span>01</span>
            </a>
            <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                <span>Employees</span>
                <span>02</span>
            </a>
            <a href="{{ route('admin.screenshots.index') }}" class="{{ request()->routeIs('admin.screenshots.*') ? 'active' : '' }}">
                <span>Screenshots</span>
                <span>03</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <span>Settings</span>
                <span>04</span>
            </a>
            <form action="{{ route('admin.logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="logout">
                    <span>Logout</span>
                    <span>↗</span>
                </button>
            </form>
        </nav>

        <div class="sidebar-card">
            <div class="muted">Admin status</div>
            <div style="font-size: 18px; font-weight: 800; margin-top: 8px;">Online and monitoring</div>
            <p style="margin: 10px 0 0; line-height: 1.6;">Live employee status, screenshots, activity time, and reporting tools in one place.</p>
        </div>
    </aside>

    <main class="workspace">
        <div class="page-wrap">
            <div class="page-header">
                <div>
                    <h2>{{ $pageTitle ?? 'Admin Workspace' }}</h2>
                    <p>{{ $pageSubtitle ?? 'Monitor employees, devices, screenshots, and activity at a glance.' }}</p>
                </div>
                <div class="actions">
                    <span class="badge">Secure session</span>
                    <span class="badge">{{ now()->format('M d, Y') }}</span>
                </div>
            </div>

            <div class="content">
                @yield('content')
            </div>
        </div>
    </main>
</div>

<script>
    function formatLastSeen(ts) {
        if (!ts) return { label: 'Never', state: 'offline' };
        const date = new Date(ts);
        if (Number.isNaN(date.getTime())) return { label: 'Never', state: 'offline' };
        const diff = Math.max(0, Date.now() - date.getTime());
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        if (seconds < 60) return { label: `${seconds}s ago`, state: 'online' };
        if (minutes < 60) return { label: `${minutes}m ago`, state: minutes <= 2 ? 'online' : 'away' };
        return { label: `${hours}h ago`, state: hours <= 2 ? 'away' : 'offline' };
    }

    function refreshLiveBadges() {
        document.querySelectorAll('[data-live-last-seen]').forEach((el) => {
            const result = formatLastSeen(el.getAttribute('data-last-ping-at'));
            el.dataset.state = result.state;
            el.classList.remove('online', 'away', 'offline');
            el.classList.add(result.state);

            const label = el.querySelector('[data-live-label]');
            if (label) {
                label.textContent = result.label;
            }

            const dot = el.querySelector('[data-live-dot]');
            if (dot) {
                dot.classList.remove('online', 'away', 'offline');
                dot.classList.add(result.state);
            }
        });
    }

    refreshLiveBadges();
    setInterval(refreshLiveBadges, 5000);
</script>

@stack('scripts')

</body>
</html>
