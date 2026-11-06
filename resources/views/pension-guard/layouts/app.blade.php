<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Pension Account Guard')</title>

    <style>
        :root {
            --navy: #12345a;
            --blue: #1769aa;
            --light: #f3f7fb;
            --border: #dce5ef;
            --text: #263548;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: var(--light);
            color: var(--text);
        }

        header {
            background: var(--navy);
            color: white;
            padding: 18px 24px;
        }

        header h1 {
            margin: 0 0 12px;
            font-size: 22px;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        nav a {
            color: white;
            text-decoration: none;
        }

        nav a:hover { text-decoration: underline; }

        main {
            max-width: 1400px;
            margin: 24px auto;
            padding: 0 20px;
        }

        .container { width: 100%; }

        .table-responsive { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
        }

        th { background: #e8f0f8; }

        input, button {
            padding: 9px 12px;
            font: inherit;
        }

        input {
            max-width: 100%;
            border: 1px solid #bdcbd9;
            border-radius: 4px;
        }

        .form-control {
            display: block;
            width: 100%;
            margin-top: 5px;
        }

        .mb-3 { margin-bottom: 16px; }
        .mb-4 { margin-bottom: 24px; }

        .btn {
            display: inline-block;
            padding: 9px 13px;
            border: 0;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--blue);
            color: white;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: var(--text);
        }

        .alert {
            padding: 12px;
            margin-bottom: 16px;
            background: #e1f5e8;
            border: 1px solid #a6d8b5;
            border-radius: 4px;
        }

        pre {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            max-width: 350px;
        }

        footer {
            padding: 24px;
            text-align: center;
            color: #5b6b7d;
        }
    </style>
</head>
<body>
    <header>
        <h1>Pension Account Guard</h1>

    <nav aria-label="Pension Account Guard navigation">
        <a href="{{ route('pension-guard.dashboard') }}">Dashboard</a>

        @auth('pension_guard')
           @if (auth('pension_guard')->user()?->is_admin)
            <a href="{{ route('pension-guard.admin.index') }}">Administration</a>
          <a href="{{ route('pension-guard.admin.audit.index') }}">Audit log</a>
        @endif
           <form method="POST" action="{{ route('pension-guard.logout') }}" style="display:inline">
         @csrf
            <button type="submit">Sign out</button>
             </form>
        @else
            <a href="{{ route('pension-guard.login') }}">Sign in</a>
            <a href="{{ route('pension-guard.register') }}">Create account</a>
        @endauth
    </nav>
    </header>

    <main>
        @if (session('success'))
            <div class="alert" role="status">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer>
     Pension Account Guard &mdash; pension security information and awareness.
    </footer>
</body>
</html>
