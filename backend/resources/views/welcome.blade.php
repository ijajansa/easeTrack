<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>easeTrack</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #dbeafe 100%);
            color: #0f172a;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            width: min(640px, calc(100vw - 32px));
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);
        }
        a {
            color: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>easeTrack</h1>
        <p>Laravel backend and Python client scaffold is ready.</p>
        <p><a href="{{ route('admin.login') }}">Open admin login</a></p>
    </div>
</body>
</html>

