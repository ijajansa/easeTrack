<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SnapTrack Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" href="{{ asset('assets/img/icon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: #06111f;
            --bg-2: #10213d;
            --panel-strong: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-2: #7c3aed;
            --border: rgba(148, 163, 184, 0.24);
            --shadow: 0 35px 80px rgba(15, 23, 42, 0.28);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Inter", "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.24), transparent 28%),
                radial-gradient(circle at 90% 10%, rgba(124, 58, 237, 0.24), transparent 26%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            display: grid;
            place-items: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,0.18), transparent 85%);
            pointer-events: none;
        }

        .login-shell {
            position: relative;
            z-index: 1;
            width: min(1120px, 100%);
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            border-radius: 34px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(18px);
        }

        .login-brand {
            padding: 42px;
            color: white;
            background:
                linear-gradient(180deg, rgba(15,23,42,0.78), rgba(15,23,42,0.40));
            /* background:
                linear-gradient(180deg, rgba(15,23,42,0.78), rgba(15,23,42,0.40)),
                url('{{ asset('assets/img/icon.png') }}') center/cover no-repeat; */
            position: relative;
            min-height: 620px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .login-brand::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15,23,42,0.80), rgba(37,99,235,0.30), rgba(124,58,237,0.20));
            mix-blend-mode: multiply;
        }

        .brand-content,
        .brand-footer {
            position: relative;
            z-index: 1;
        }

        .brand-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.16);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .login-brand h1 {
            margin: 18px 0 14px;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(40px, 6vw, 64px);
            line-height: 0.95;
            letter-spacing: -0.05em;
            max-width: 10ch;
        }

        .login-brand p {
            margin: 0;
            max-width: 34ch;
            color: rgba(255,255,255,0.82);
            font-size: 16px;
            line-height: 1.7;
        }

        .feature-list {
            display: grid;
            gap: 12px;
            margin-top: 28px;
            max-width: 360px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.14);
            backdrop-filter: blur(10px);
        }

        .feature strong {
            display: block;
        }

        .feature span {
            color: rgba(255,255,255,0.72);
            font-size: 13px;
        }

        .logo-wrap {
            display: inline-flex;
            align-items: center;
            gap: 14px;
        }

        .logo-wrap img {
            width: 68px;
            height: 68px;
            border-radius: 12px;
            background: rgba(255,255,255,0.92);
            padding: 8px;
            object-fit: cover;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.22);
        }

        .login-panel {
            padding: 42px;
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(248,250,252,0.94));
        }

        .login-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-panel h2 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 32px;
            letter-spacing: -0.03em;
        }

        .login-panel .hint {
            margin: 10px 0 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .form {
            margin-top: 28px;
            display: grid;
            gap: 16px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: #334155;
        }

        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid rgba(148, 163, 184, 0.36);
            border-radius: 16px;
            font: inherit;
            background: rgba(255,255,255,0.92);
        }

        input:focus {
            outline: 2px solid rgba(37, 99, 235, 0.14);
            border-color: rgba(37, 99, 235, 0.38);
        }

        .submit {
            margin-top: 6px;
            padding: 14px 16px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: white;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 16px 30px rgba(37, 99, 235, 0.26);
        }

        .error {
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(220, 38, 38, 0.10);
            color: #991b1b;
            border: 1px solid rgba(220, 38, 38, 0.18);
            font-size: 14px;
        }

        .footer-note {
            margin-top: 20px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        @media (max-width: 960px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-brand {
                min-height: 360px;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 12px;
            }

            .login-brand,
            .login-panel {
                padding: 24px;
            }

            .login-panel h2 {
                font-size: 26px;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <section class="login-brand">
            <div class="brand-content">
                <div class="logo-wrap">
                    <img src="{{ asset('assets/img/icon.png') }}" alt="SnapTrack logo">
                    <div>
                        <div class="brand-chip">Secure Workforce Intelligence</div>
                        <h1>SnapTrack Admin</h1>
                    </div>
                </div>

                <p>
                    A premium control center for employee tracking, screenshot visibility, time analytics,
                    and device monitoring. Built to impress in demos and perform in production.
                </p>

                <div class="feature-list">
                    <div class="feature">
                        <div>01</div>
                        <div>
                            <strong>Live activity</strong>
                            <span>Track working and idle time in near real time.</span>
                        </div>
                    </div>
                    <div class="feature">
                        <div>02</div>
                        <div>
                            <strong>Screenshot vault</strong>
                            <span>Preview and filter uploads by device or date.</span>
                        </div>
                    </div>
                    <div class="feature">
                        <div>03</div>
                        <div>
                            <strong>Investor-ready UI</strong>
                            <span>Modern glass surfaces, gradients, and polished flow.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="brand-footer">
                <div class="brand-chip">Admin console | secure login</div>
            </div>
        </section>

        <section class="login-panel">
            <div class="login-card">
                <div class="logo-wrap" style="margin-bottom: 18px;">
                    <img src="{{ asset('assets/img/icon.png') }}" alt="SnapTrack logo">
                    <div>
                        <div class="brand-chip" style="background: rgba(37,99,235,0.10); color: #1d4ed8; border-color: rgba(37,99,235,0.12);">Welcome back</div>
                        <h2>Sign in to continue</h2>
                    </div>
                </div>

                <p class="hint">Use your admin credentials to open the SnapTrack dashboard.</p>

                @if ($errors->any())
                    <div class="error" style="margin-top: 20px;">{{ $errors->first() }}</div>
                @endif

                <form class="form" method="POST" action="{{ route('admin.login.store') }}">
                    @csrf
                    <div>
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div>
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required>
                    </div>

                    <button type="submit" class="submit">Login to dashboard</button>
                </form>

                <div class="footer-note">
                    Tip: keep the browser open on a secure machine for live dashboards and activity review.
                </div>
            </div>
        </section>
    </div>
</body>
</html>
