<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <title>SnapTrack | Workforce intelligence console</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-0: #07111f;
            --bg-1: #0f1b33;
            --surface: rgba(255, 255, 255, 0.80);
            --surface-strong: #ffffff;
            --surface-dark: rgba(15, 23, 42, 0.92);
            --border: rgba(148, 163, 184, 0.22);
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-2: #7c3aed;
            --success: #16a34a;
            --warning: #d97706;
            --danger: #dc2626;
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
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255,255,255,0.24) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.18) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,0.16), transparent 72%);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .page {
            position: relative;
            z-index: 1;
        }

        .container {
            width: min(1240px, calc(100vw - 32px));
            margin: 0 auto;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(18px);
            background: rgba(255,255,255,0.68);
            border-bottom: 1px solid rgba(148, 163, 184, 0.16);
        }

        .topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo {
            width: 54px;
            height: 54px;
            object-fit: cover;
            border-radius: 13px;
            background: rgba(255,255,255,0.9);
            padding: 6px;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
        }

        .brand-copy h1 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 22px;
            letter-spacing: -0.03em;
        }

        .brand-copy p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .navlinks {
            display: flex;
            align-items: center;
            gap: 20px;
            color: #334155;
            font-weight: 700;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-panel {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .nav-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            border: 1px solid rgba(148, 163, 184, 0.22);
            background: rgba(255, 255, 255, 0.88);
            color: #0f172a;
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
            cursor: pointer;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
        }

        .nav-toggle:hover {
            transform: translateY(-1px);
            border-color: rgba(37, 99, 235, 0.28);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.10);
        }

        .nav-toggle-lines {
            position: relative;
            width: 18px;
            height: 14px;
        }

        .nav-toggle-lines span {
            position: absolute;
            left: 0;
            width: 100%;
            height: 2px;
            border-radius: 999px;
            background: currentColor;
            transition: transform 220ms ease, top 220ms ease, opacity 180ms ease;
        }

        .nav-toggle-lines span:nth-child(1) { top: 0; }
        .nav-toggle-lines span:nth-child(2) { top: 6px; }
        .nav-toggle-lines span:nth-child(3) { top: 12px; }

        .topbar.menu-open .nav-toggle-lines span:nth-child(1) {
            top: 6px;
            transform: rotate(45deg);
        }

        .topbar.menu-open .nav-toggle-lines span:nth-child(2) {
            opacity: 0;
        }

        .topbar.menu-open .nav-toggle-lines span:nth-child(3) {
            top: 6px;
            transform: rotate(-45deg);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 800;
            border: 1px solid rgba(148, 163, 184, 0.22);
            background: rgba(255,255,255,0.86);
            color: #0f172a;
            transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            border-color: rgba(37, 99, 235, 0.28);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.10);
        }

        .btn.primary {
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            border-color: transparent;
            box-shadow: 0 18px 32px rgba(37, 99, 235, 0.28);
        }

        .hero {
            padding: 72px 0 24px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 28px;
            align-items: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.10);
            color: var(--primary);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .hero h2 {
            margin: 18px 0 14px;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(44px, 6vw, 76px);
            letter-spacing: -0.05em;
            line-height: 0.95;
        }

        .hero p {
            margin: 0;
            max-width: 660px;
            font-size: 18px;
            line-height: 1.7;
            color: #334155;
        }

        .hero-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 28px;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 30px;
        }

        .stat {
            border-radius: 22px;
            padding: 18px;
            background: rgba(255,255,255,0.78);
            border: 1px solid rgba(148, 163, 184, 0.20);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .stat strong {
            display: block;
            margin-top: 6px;
            font-family: "Space Grotesk", sans-serif;
            font-size: 26px;
            letter-spacing: -0.03em;
        }

        .preview-shell {
            position: relative;
            min-height: 620px;
            display: grid;
            place-items: center;
        }

        .story-stack {
            display: grid;
            gap: 18px;
            margin-top: 22px;
        }

        .story-row {
            display: grid;
            grid-template-columns: minmax(0, 0.94fr) minmax(0, 1.06fr);
            gap: 22px;
            align-items: center;
            padding: 18px;
            border-radius: 30px;
            border: 1px solid rgba(148, 163, 184, 0.16);
            /* background: linear-gradient(180deg, rgba(255,255,255,0.90), rgba(255,255,255,0.74)); */
            background: linear-gradient(180deg, rgb(255, 255, 255), rgba(255,255,255));
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.08);
        }

        .story-row.reverse {
            grid-template-columns: minmax(0, 1.06fr) minmax(0, 0.94fr);
        }

        .story-copy {
            padding: 12px 10px;
        }

        .story-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.10);
            color: var(--primary);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .story-copy h4 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(28px, 3.2vw, 42px);
            line-height: 1.02;
            letter-spacing: -0.05em;
        }

        .story-copy p {
            margin: 14px 0 0;
            color: #475569;
            line-height: 1.75;
            font-size: 16px;
            max-width: 56ch;
        }

        .story-points {
            display: grid;
            gap: 10px;
            margin-top: 18px;
        }

        .story-point {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            color: #334155;
            line-height: 1.6;
        }

        .story-point .dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            margin-top: 8px;
            flex: 0 0 auto;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.10);
        }

        .story-media {
            min-width: 0;
        }

        .story-frame {
            overflow: hidden;
            /* border-radius: 26px; */
            /* background: linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.88)); */
            /* border: 1px solid rgba(148, 163, 184, 0.14); */
            /* box-shadow: 0 24px 64px rgba(15, 23, 42, 0.18); */
        }

        .story-frame img {
            display: block;
            width: 100%;
            height: auto;
        }

        .story-tagrow {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .story-tagrow .badge {
            background: rgba(37, 99, 235, 0.10);
        }

        .image-float {
            animation: imageFloat 12s ease-in-out infinite;
        }

        .image-float.delay-1 {
            animation-delay: 1.8s;
        }

        .image-float.delay-2 {
            animation-delay: 3.2s;
        }

        .preview-glow {
            position: absolute;
            inset: 12% 10% 16% 10%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.18), rgba(124, 58, 237, 0.14), transparent 70%);
            filter: blur(30px);
            border-radius: 50%;
            animation: drift 16s ease-in-out infinite;
        }

        .mock-dashboard {
            position: relative;
            width: min(100%, 620px);
            border-radius: 30px;
            padding: 18px;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(20, 29, 50, 0.95));
            color: white;
            box-shadow: 0 34px 90px rgba(15, 23, 42, 0.24);
            transform: rotate(-2deg);
            animation: float 8s ease-in-out infinite;
        }

        .mock-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .mock-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mock-brand img {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: white;
            padding: 5px;
        }

        .mock-brand h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 18px;
        }

        .mock-brand p {
            margin: 2px 0 0;
            color: rgba(226, 232, 240, 0.74);
            font-size: 12px;
        }

        .mock-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 16px;
        }

        .mock-card {
            border-radius: 24px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            padding: 16px;
            backdrop-filter: blur(10px);
        }

        .mock-kpis {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .mock-kpi {
            border-radius: 18px;
            padding: 14px;
            background: rgba(255,255,255,0.08);
        }

        .mock-kpi .label {
            color: rgba(226, 232, 240, 0.72);
            font-size: 12px;
        }

        .mock-kpi strong {
            display: block;
            margin-top: 8px;
            font-size: 24px;
            font-family: "Space Grotesk", sans-serif;
        }

        .mock-table {
            display: grid;
            gap: 10px;
        }

        .mock-row {
            display: grid;
            grid-template-columns: 1.1fr 0.7fr 0.7fr;
            gap: 10px;
            align-items: center;
            padding: 12px 14px;
            border-radius: 16px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .mock-row span {
            color: rgba(226, 232, 240, 0.82);
            font-size: 13px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            background: rgba(255,255,255,0.10);
        }

        .pill.success { color: #bbf7d0; }
        .pill.warning { color: #fde68a; }
        .pill.danger { color: #fecaca; }

        .section {
            padding: 44px 0;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: end;
            margin-bottom: 24px;
        }

        .section-heading h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(28px, 3vw, 42px);
            letter-spacing: -0.04em;
        }

        .section-heading p {
            margin: 0;
            max-width: 640px;
            color: #475569;
            line-height: 1.7;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
        }

        .feature {
            border-radius: 24px;
            padding: 22px;
            background: rgba(255,255,255,0.80);
            border: 1px solid rgba(148, 163, 184, 0.20);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
            min-height: 210px;
        }

        .feature .icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            margin-bottom: 16px;
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.22);
            font-weight: 800;
        }

        .feature h4 {
            margin: 0 0 8px;
            font-size: 18px;
            font-family: "Space Grotesk", sans-serif;
        }

        .feature p {
            margin: 0;
            color: #475569;
            line-height: 1.65;
        }

        .flow {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .flow-card {
            position: relative;
            padding: 24px;
            border-radius: 26px;
            background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(248,250,252,0.92));
            border: 1px solid rgba(148, 163, 184, 0.20);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .flow-card .step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            color: white;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 16px 32px rgba(37, 99, 235, 0.22);
            margin-bottom: 16px;
        }

        .flow-card h4 {
            margin: 0 0 10px;
            font-family: "Space Grotesk", sans-serif;
            font-size: 20px;
        }

        .flow-card p {
            margin: 0;
            color: #475569;
            line-height: 1.7;
        }

        .security-grid {
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            gap: 18px;
            align-items: stretch;
        }

        .glass {
            padding: 26px;
            border-radius: 28px;
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(148, 163, 184, 0.20);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .glass.dark {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.96));
            color: white;
        }

        .glass.dark .muted {
            color: rgba(226, 232, 240, 0.72);
        }

        .checklist {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .check {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .check .mark {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(34, 197, 94, 0.14);
            color: #86efac;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .cta {
            padding: 26px;
            border-radius: 30px;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 54%, #7c3aed 100%);
            color: white;
            box-shadow: 0 32px 80px rgba(15, 23, 42, 0.24);
        }

        .cta h3 {
            margin: 0 0 10px;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(28px, 4vw, 46px);
            letter-spacing: -0.04em;
        }

        .cta p {
            margin: 0;
            max-width: 740px;
            line-height: 1.7;
            color: rgba(255,255,255,0.84);
        }

        .footer {
            padding: 26px 0 40px;
            color: #475569;
            font-size: 14px;
        }

        .reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 700ms ease, transform 700ms ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .delay-1 { transition-delay: 120ms; }
        .delay-2 { transition-delay: 220ms; }
        .delay-3 { transition-delay: 320ms; }

        .float-a { animation: float 10s ease-in-out infinite; }
        .float-b { animation: float 12s ease-in-out infinite reverse; }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-10px) rotate(-1deg); }
        }

        @keyframes drift {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(10px, -8px, 0) scale(1.04); }
        }

        @keyframes imageFloat {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-10px) scale(1.01); }
        }

        @media (max-width: 1100px) {
            .hero-grid, .security-grid {
                grid-template-columns: 1fr;
            }

            .feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .story-row,
            .story-row.reverse {
                grid-template-columns: 1fr;
            }

            .flow {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 780px) {
            .container {
                width: min(100vw - 24px, 1240px);
            }

            .topbar-inner,
            .section-heading {
                flex-direction: column;
                align-items: flex-start;
            }

            .topbar-inner {
                position: relative;
            }

            .nav-toggle {
                display: inline-flex;
                position: absolute;
                top: 14px;
                right: 0;
            }

            .nav-panel {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 14px;
                padding: 2px;
                border-radius: 22px;
                border: 1px solid rgba(148, 163, 184, 0.16);
                background: rgba(255,255,255,0.86);
                box-shadow: 0 24px 50px rgba(15, 23, 42, 0.10);
                overflow: hidden;
                max-height: 0;
                opacity: 0;
                transform: translateY(-10px) scale(0.98);
                pointer-events: none;
                transition: max-height 320ms ease, opacity 240ms ease, transform 240ms ease;
            }

            .topbar.menu-open .nav-panel {
                max-height: 420px;
                opacity: 1;
                transform: translateY(0) scale(1);
                pointer-events: auto;
            }

            .navlinks {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                padding:10px;

                font-size: 15px;
            }

            .actions {
                width: 100%;
            }

            .actions .btn {
                width: 100%;
            }

            .hero {
                padding-top: 48px;
            }

            .hero-stats,
            .feature-grid {
                grid-template-columns: 1fr;
            }

            .mock-dashboard {
                transform: none;
            }

            .mock-grid {
                grid-template-columns: 1fr;
            }

            .mock-row {
                grid-template-columns: 1fr;
            }

            .story-row {
                padding: 14px;
                gap: 16px;
            }

            .story-copy {
                padding: 4px 2px 2px;
            }
        }
    </style>
</head>
<body>
<div class="page">
    <header class="topbar">
        <div class="container topbar-inner">
            <div class="brand">
                <img class="brand-logo" src="{{ asset('assets/img/icon.png') }}" alt="SnapTrack logo">
                <div class="brand-copy">
                    <h1>SnapTrack</h1>
                    <p>Workforce intelligence console</p>
                </div>
            </div>

            <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="topbar-menu">
                <span class="nav-toggle-lines" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>

            <div class="nav-panel" id="topbar-menu">
                <nav class="navlinks">
                    <a href="#platform">Platform</a>
                    <a href="#workflow">Workflow</a>
                    <a href="#security">Security</a>
                    <a href="#contact">Contact</a>
                </nav>

                <div class="actions">
                    <a class="btn" href="{{ route('admin.login') }}">Admin login</a>
                    <a class="btn primary" href="#contact">Explore platform</a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div>
                    <span class="eyebrow reveal visible">Monitor work without friction</span>
                    <h2 class="reveal visible delay-1">See every workday, screenshot, and idle gap in one clean command center.</h2>
                    <p class="reveal visible delay-2">
                        SnapTrack gives teams a polished admin console, secure device onboarding, screenshot vaults, and working-time reporting built for real operations.
                    </p>

                    <div class="hero-actions reveal visible delay-3">
                        <a class="btn primary" href="{{ route('admin.login') }}">Open admin console</a>
                        <a class="btn" href="#platform">Explore the platform</a>
                    </div>

                    <div class="hero-stats reveal visible delay-3">
                        <div class="stat">
                            <div class="muted">Secure device onboarding</div>
                            <strong>Auto tokens</strong>
                        </div>
                        <div class="stat">
                            <div class="muted">Screenshot vault</div>
                            <strong>1 min pings</strong>
                        </div>
                        <div class="stat">
                            <div class="muted">Working / idle reports</div>
                            <strong>Live charts</strong>
                        </div>
                    </div>
                </div>

                <div class="preview-shell">
                    <div class="preview-glow"></div>
                    <div class="mock-dashboard float-a">
                        <div class="mock-top">
                            <div class="mock-brand">
                                <img src="{{ asset('assets/img/icon.png') }}" alt="SnapTrack icon">
                                <div>
                                    <h3>Admin Dashboard</h3>
                                    <p>Live workforce pulse</p>
                                </div>
                            </div>
                            <span class="pill success">Online</span>
                        </div>

                        <div class="mock-grid">
                            <div class="mock-card">
                                <div class="mock-kpis">
                                    <div class="mock-kpi">
                                        <div class="label">Devices healthy</div>
                                        <strong>18</strong>
                                    </div>
                                    <div class="mock-kpi">
                                        <div class="label">Idle alerts</div>
                                        <strong>02</strong>
                                    </div>
                                    <div class="mock-kpi">
                                        <div class="label">Screenshots today</div>
                                        <strong>1,284</strong>
                                    </div>
                                    <div class="mock-kpi">
                                        <div class="label">Active now</div>
                                        <strong>12</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="mock-card">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                    <strong>Live feed</strong>
                                    <span class="pill warning">Delayed</span>
                                </div>
                                <div class="mock-table">
                                    <div class="mock-row">
                                        <span>Rohit Singh</span>
                                        <span>device-003</span>
                                        <span class="pill success">Healthy</span>
                                    </div>
                                    <div class="mock-row">
                                        <span>Neha Sharma</span>
                                        <span>device-004</span>
                                        <span class="pill warning">Idle</span>
                                    </div>
                                    <div class="mock-row">
                                        <span>Arjun Verma</span>
                                        <span>device-005</span>
                                        <span class="pill danger">Offline</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="platform" class="section">
            <div class="container">
                <div class="section-heading reveal">
                    <div>
                        <h3>Built like a real operations console</h3>
                        <p>Everything in SnapTrack is tuned for clarity: compact tables, live badges, activity charts, quick screenshots, and device health signals that read at a glance.</p>
                    </div>
                </div>

                <div class="feature-grid">
                    <article class="feature reveal">
                        <div class="icon">01</div>
                        <h4>Screenshot vault</h4>
                        <p>Browse screenshots by employee, time, or day with preview popups, zoom, and fast pagination.</p>
                    </article>
                    <article class="feature reveal delay-1">
                        <div class="icon">02</div>
                        <h4>Working / idle analytics</h4>
                        <p>See daily work hours, idle time, alert flags, and chart-based summaries without leaving the profile page.</p>
                    </article>
                    <article class="feature reveal delay-2">
                        <div class="icon">03</div>
                        <h4>Auto-generated device keys</h4>
                        <p>Every employee gets a unique sequential device ID and token from the admin panel, ready for setup in one copy step.</p>
                    </article>
                    <article class="feature reveal delay-3">
                        <div class="icon">04</div>
                        <h4>Global settings control</h4>
                        <p>Set screenshot interval, idle threshold, tracking enablement, and timeout values once for every connected device.</p>
                    </article>
                </div>

                <div class="story-stack">
                    <article class="story-row reveal">
                        <div class="story-copy">
                            <span class="story-label">Platform view</span>
                            <h4>Command dashboard for clear operations visibility.</h4>
                            <p>
                                The landing page showcases a sharp command center with live device health, working-hour visibility,
                                and a clean snapshot of the whole workforce from one premium screen.
                            </p>
                            <div class="story-points">
                                <div class="story-point"><span class="dot"></span><span>Health widgets for healthy, delayed, and offline devices.</span></div>
                                <div class="story-point"><span class="dot"></span><span>Working time and idle flags surfaced in a compact layout.</span></div>
                                <div class="story-point"><span class="dot"></span><span>Built to present device health and activity at a glance.</span></div>
                            </div>
                            <div class="story-tagrow">
                                <span class="badge">Live KPIs</span>
                                <span class="badge">Charts</span>
                                <span class="badge">Device health</span>
                            </div>
                        </div>
                        <div class="story-media">
                            <div class="story-frame">
                                <img class="image-float" src="{{ asset('assets/img/landing/dashboard-hero.png') }}" alt="SnapTrack dashboard preview">
                            </div>
                        </div>
                    </article>

                    <article class="story-row reverse reveal delay-1">
                        <div class="story-media">
                            <div class="story-frame">
                                <img class="image-float delay-1" src="{{ asset('assets/img/landing/screenshot-vault.png') }}" alt="SnapTrack screenshot vault preview">
                            </div>
                        </div>
                        <div class="story-copy">
                            <span class="story-label">Monitoring view</span>
                            <h4>Screenshot vault with a premium review flow.</h4>
                            <p>
                                The screenshot page is presented like a polished content wall, with large previews, zoomed focus,
                                and enough breathing room to scan employee activity without feeling crowded.
                            </p>
                            <div class="story-points">
                                <div class="story-point"><span class="dot"></span><span>Fullscreen-style review experience for quick inspection.</span></div>
                                <div class="story-point"><span class="dot"></span><span>Pagination and filtering ready for large archives.</span></div>
                                <div class="story-point"><span class="dot"></span><span>Built for centered preview and clean reading flow.</span></div>
                            </div>
                            <div class="story-tagrow">
                                <span class="badge">Preview modal</span>
                                <span class="badge">Pagination</span>
                                <span class="badge">Zoom</span>
                            </div>
                        </div>
                    </article>

                    <article class="story-row reveal delay-2">
                        <div class="story-copy">
                            <span class="story-label">Setup flow</span>
                            <h4>Compact employee setup that removes friction.</h4>
                            <p>
                                New devices get a generated device ID, unique API token, and server URL in one clean handoff,
                                so employees can install once and start tracking without repeated manual setup.
                            </p>
                            <div class="story-points">
                                <div class="story-point"><span class="dot"></span><span>Automatic device ID creation for each employee.</span></div>
                                <div class="story-point"><span class="dot"></span><span>Unique token handling for secure device authentication.</span></div>
                                <div class="story-point"><span class="dot"></span><span>Compact admin setup sheet for copy-and-run onboarding.</span></div>
                            </div>
                            <div class="story-tagrow">
                                <span class="badge">Device ID</span>
                                <span class="badge">API token</span>
                                <span class="badge">One-time setup</span>
                            </div>
                        </div>
                        <div class="story-media">
                            <div class="story-frame">
                                <img class="image-float delay-2" src="{{ asset('assets/img/landing/setup-sheet.png') }}" alt="SnapTrack setup sheet preview">
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section id="workflow" class="section">
            <div class="container">
                <div class="section-heading reveal">
                    <div>
                        <h3>Simple onboarding flow</h3>
                        <p>We designed the setup so employees only enter their details once. The rest is automatic and stays in sync with the admin panel.</p>
                    </div>
                </div>

                <div class="flow">
                    <article class="flow-card reveal">
                        <div class="step">1</div>
                        <h4>Create employee</h4>
                        <p>Admin enters the employee name and SnapTrack creates the sequential device ID and unique API token automatically.</p>
                    </article>
                    <article class="flow-card reveal delay-1">
                        <div class="step">2</div>
                        <h4>Run the EXE</h4>
                        <p>The employee opens the portable app or installer, enters the credentials once, and the tracker runs in the background.</p>
                    </article>
                    <article class="flow-card reveal delay-2">
                        <div class="step">3</div>
                        <h4>Monitor live</h4>
                        <p>The admin dashboard shows screenshots, activity trends, idle alerts, and tracker health in a clean, compact layout.</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="security" class="section">
            <div class="container security-grid">
                <div class="glass dark reveal">
                    <span class="eyebrow" style="background: rgba(255,255,255,0.10); color: #bfdbfe;">Security & reliability</span>
                    <h3 style="margin:18px 0 10px; font-family:'Space Grotesk', sans-serif; font-size: clamp(28px, 4vw, 48px); letter-spacing:-0.04em;">
                        Designed for production, not just a showcase.
                    </h3>
                    <p class="muted" style="line-height:1.75; font-size:16px; max-width: 620px;">
                        SnapTrack uses token-based device authentication, HTTPS-ready endpoints, local offline queues, and health indicators so administrators can see when a laptop is delayed, sleeping, or offline.
                    </p>

                    <div class="checklist">
                        <div class="check">
                            <span class="mark">✓</span>
                            <div>
                                <strong>Unique token per device</strong>
                                <div class="muted">Keeps each employee isolated and easy to revoke individually.</div>
                            </div>
                        </div>
                        <div class="check">
                            <span class="mark">✓</span>
                            <div>
                                <strong>Queue when offline</strong>
                                <div class="muted">Screenshots and payloads retry automatically when the network returns.</div>
                            </div>
                        </div>
                        <div class="check">
                            <span class="mark">✓</span>
                            <div>
                                <strong>Sleep-aware tracking</strong>
                                <div class="muted">Laptop suspend time is excluded from working and idle counters.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass reveal">
                    <span class="eyebrow">Dashboard preview</span>
                    <div style="margin-top:18px; display:grid; gap:14px;">
                        <div class="stat" style="background: linear-gradient(180deg, rgba(37,99,235,0.08), rgba(124,58,237,0.06));">
                            <div class="muted">Health widgets</div>
                            <strong style="font-size: 30px;">Healthy, delayed, offline</strong>
                            <div class="muted">Built for instant scanning by managers.</div>
                        </div>
                        <div class="stat">
                            <div class="muted">Employee detail page</div>
                            <strong style="font-size: 30px;">Charts + screenshots + flags</strong>
                            <div class="muted">Open a profile for a full picture of activity and performance.</div>
                        </div>
                        <div class="stat">
                            <div class="muted">Setup sheet</div>
                            <strong style="font-size: 30px;">Compact handoff</strong>
                            <div class="muted">Copy the device ID, token, and server URL in one place.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="section">
            <div class="container">
                <div class="cta reveal">
                    <h3>Ready to launch SnapTrack on your domain?</h3>
                    <p>
                        The landing page is live at the root URL, and the admin console is ready behind it.
                        You can introduce the platform from the public page, then send visitors straight into the admin login when they’re ready.
                    </p>
                    <div class="hero-actions" style="margin-top:22px;">
                        <a class="btn primary" href="{{ route('admin.login') }}">Open admin login</a>
                        <a class="btn" href="#platform">See features again</a>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="container" style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:center; padding-top: 8px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <img src="{{ asset('assets/img/icon.png') }}" alt="SnapTrack logo" style="width:34px; height:34px; border-radius:10px; background:white; padding:4px; box-shadow: 0 14px 30px rgba(15, 23, 42, 0.10);">
                    <div>
                        <strong style="display:block; color:#0f172a;">SnapTrack</strong>
                        <span>Workforce intelligence console</span>
                    </div>
                </div>
                <div>Built for employee monitoring, screenshots, and time tracking.</div>
            </div>
        </footer>
    </main>
</div>

<script>
    (() => {
        const topbar = document.querySelector('.topbar');
        const toggle = document.querySelector('.nav-toggle');
        const menu = document.getElementById('topbar-menu');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.16 });

        document.querySelectorAll('.reveal').forEach((el) => {
            if (! el.classList.contains('visible')) {
                observer.observe(el);
            }
        });

        const setMenuState = (open) => {
            topbar.classList.toggle('menu-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        toggle?.addEventListener('click', () => {
            const isOpen = topbar.classList.contains('menu-open');
            setMenuState(! isOpen);
        });

        menu?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 780) {
                    setMenuState(false);
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 780) {
                setMenuState(false);
            }
        });
    })();
</script>
</body>
</html>
