
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#123b83">
    <meta name="description" content="Pension Account Guard helps people organise pension records, understand fraud warning signs and follow practical security steps.">
    <title>@yield('title', 'Pension Account Guard')</title>

    <style>
        * { box-sizing: border-box; }

        :root {
            --primary: #123b83;
            --secondary: #168bd2;
            --background: #f3f7fc;
            --surface: #ffffff;
            --text: #172554;
            --muted: #52627a;
            --border: #dbe5f2;
        }

        body {
            margin: 0;
            font: 16px/1.6 Arial, sans-serif;
            color: var(--text);
            background: var(--background);
        }

        a { overflow-wrap: anywhere; }

        a:focus-visible, button:focus-visible {
            outline: 3px solid #fbbf24;
            outline-offset: 3px;
        }

        .site-header {
            color: white;
            background: linear-gradient(120deg, var(--primary), var(--secondary));
        }

        .header-inner {
            max-width: 1200px;
            margin: auto;
            padding: 18px 24px;
        }

        .brand-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .brand {
            color: white;
            text-decoration: none;
            font-size: 1.55rem;
            font-weight: 700;
        }

        .tagline {
            margin: 2px 0 14px;
            color: #e2eeff;
            font-size: .92rem;
        }

        .main-nav {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            padding-top: 12px;
            border-top: 1px solid rgb(255 255 255 / 25%);
        }

        .main-nav a, .main-nav .nav-button {
            display: inline-block;
            padding: 8px 11px;
            color: white;
            border: 0;
            border-radius: 7px;
            background: transparent;
            text-decoration: none;
            font: inherit;
            cursor: pointer;
        }

        .main-nav a:hover,
        .main-nav a[aria-current="page"],
        .main-nav .nav-button:hover {
            background: rgb(255 255 255 / 17%);
        }

        .nav-form { display: inline; margin: 0; }

        .nav-heading {
            width: 100%;
            margin: 10px 8px 0;
            color: #dcecff;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .pg-admin-navigation {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            width: 100%;
            padding: 10px 0;
            border-top: 1px solid rgb(255 255 255 / 25%);
        }

        .pg-admin-label {
            font-weight: 700;
            padding: 9px 12px;
        }

        .pg-customer-navigation {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
            width: 100%;
        }

        main {
            width: 100%;
            max-width: 1200px;
            min-height: 65vh;
            margin: auto;
            padding: 28px 24px;
        }

        footer {
            padding: 24px;
            color: var(--muted);
            text-align: center;
            font-size: .9rem;
        }

        .footer-inner { max-width: 1000px; margin: auto; }

        .panel {
            padding: 22px;
            margin-bottom: 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        .button, button {
            display: inline-block;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: #155eaa;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font: inherit;
        }

        .button:hover, button:hover { background: #0f477f; }

        input, select, textarea {
            max-width: 100%;
            padding: 10px;
            border: 1px solid #9caec5;
            border-radius: 6px;
            font: inherit;
        }

        label { display: block; margin: 10px 0; }
        .muted { color: var(--muted); }
        .error { color: #b42318; }
        .status-message { border-left: 5px solid #168bd2; }
        .error-message { border-left: 5px solid #b42318; }

        @media (max-width: 640px) {
            .header-inner { padding: 16px; }
            .brand { font-size: 1.3rem; }
            .main-nav { align-items: stretch; gap: 3px; }
            .main-nav a, .main-nav .nav-button {
                padding: 8px;
                font-size: .9rem;
            }
            main { padding: 20px 14px; }
            .panel { padding: 16px; }
        }
    </style>
</head>

<body>
<header class="site-header">
    <div class="header-inner">
        <div class="brand-row">
            <a class="brand" href="{{ route('pension-guard.dashboard') }}">
                Pension Account Guard
            </a>

            @auth('pension_guard')
                <span>
                    Welcome, {{ auth('pension_guard')->user()->name ?? 'Member' }}
                </span>
            @endauth
        </div>

        <p class="tagline">
            Understand pension fraud risks. Take practical steps to protect yourself.
        </p>

        <nav class="main-nav" aria-label="Main navigation">
            <a href="{{ route('pension-guard.dashboard') }}"
               @if(request()->routeIs('pension-guard.dashboard')) aria-current="page" @endif>
                Home
            </a>

            <a href="{{ route('about') }}"
               @if(request()->routeIs('about')) aria-current="page" @endif>
                About
            </a>

            <a href="{{ route('pricing') }}"
               @if(request()->routeIs('pricing')) aria-current="page" @endif>
                Pricing
            </a>

            <a href="{{ route('contact') }}"
               @if(request()->routeIs('contact')) aria-current="page" @endif>
                Contact
            </a>

            <a href="{{ route('pension-guard.assessments.create') }}"
               @if(request()->routeIs('pension-guard.assessments.create', 'pension-guard.assessments.store')) aria-current="page" @endif>
                Security assessment
            </a>

            <a href="{{ route('pension-guard.threat-checker.create') }}"
               @if(request()->routeIs('pension-guard.threat-checker.*')) aria-current="page" @endif>
                Scam checker
            </a>

            <a href="{{ route('pension-guard.sources.index') }}"
               @if(request()->routeIs('pension-guard.sources.index')) aria-current="page" @endif>
                Official guidance
            </a>

            @auth('pension_guard')

                @if (auth('pension_guard')->user()->hasVerifiedEmail())
                    <span class="nav-heading">My pension security</span>

                    <a href="{{ route('pension-guard.accounts.index') }}"
                       @if(request()->routeIs('pension-guard.accounts.*', 'pension-guard.checklist.*')) aria-current="page" @endif>
                        My pensions and checklists
                    </a>

                    <a href="{{ route('pension-guard.subscriptions.index') }}"
                       @if(request()->routeIs('pension-guard.subscriptions.*')) aria-current="page" @endif>
                        Subscription
                    </a>

                    <a href="{{ route('pension-guard.assessments.history') }}"
                       @if(request()->routeIs('pension-guard.assessments.history', 'pension-guard.assessments.show')) aria-current="page" @endif>
                        Assessment history
                    </a>

                    <a href="{{ route('pension-guard.trusted-contacts.index') }}"
                       @if(request()->routeIs('pension-guard.trusted-contacts.*')) aria-current="page" @endif>
                        Trusted contacts
                    </a>

                    <a href="{{ route('pension-guard.notifications.index') }}"
                       @if(request()->routeIs('pension-guard.notifications.*')) aria-current="page" @endif>
                        Notifications
                    </a>

                    <span class="nav-heading">Pension protection sessions</span>

                    <div class="pg-customer-navigation">
                        <a href="{{ route('pension-guard.sessions.create') }}"
                           @if(request()->routeIs('pension-guard.sessions.create', 'pension-guard.sessions.store')) aria-current="page" @endif>
                            Book a Pension Protection Session
                        </a>

                        <a href="{{ route('pension-guard.sessions.index') }}"
                           @if(request()->routeIs('pension-guard.sessions.index', 'pension-guard.sessions.show', 'pension-guard.sessions.success', 'pension-guard.sessions.cancelled')) aria-current="page" @endif>
                            My Session Bookings
                        </a>
                    </div>
                @endif

                @if (auth('pension_guard')->user()?->is_admin
                    && auth('pension_guard')->user()->hasVerifiedEmail())

                    <div class="pg-admin-navigation" aria-label="Administration">
                        <span class="pg-admin-label">Administration</span>

                        <a href="{{ route('pension-guard.admin.index') }}"
                           @if(request()->routeIs('pension-guard.admin.index')) aria-current="page" @endif>
                            Admin dashboard
                        </a>

                        <a href="{{ route('pension-guard.admin.availability.index') }}"
                           @if(request()->routeIs('pension-guard.admin.availability.*')) aria-current="page" @endif>
                            Manage Session Availability
                        </a>

                        <a href="{{ route('pension-guard.admin.audit.index') }}"
                           @if(request()->routeIs('pension-guard.admin.audit.*')) aria-current="page" @endif>
                            Audit logs
                        </a>

                        <a href="{{ route('pension-guard.admin.sources.index') }}"
                           @if(request()->routeIs('pension-guard.admin.sources.*')) aria-current="page" @endif>
                            Source management
                        </a>

                        <a href="{{ route('pension-guard.admin.incidents.index') }}"
                           @if(request()->routeIs('pension-guard.admin.incidents.*')) aria-current="page" @endif>
                            Security incidents
                        </a>
                    </div>
                @endif

                <span class="nav-heading">Account</span>

                <form class="nav-form"
                      method="POST"
                      action="{{ route('pension-guard.logout') }}">
                    @csrf
                    <button class="nav-button" type="submit">Sign out</button>
                </form>

            @else

                <span class="nav-heading">Account</span>

                <a href="{{ route('pension-guard.login') }}"
                   @if(request()->routeIs('pension-guard.login')) aria-current="page" @endif>
                    Sign in
                </a>

                <a href="{{ route('pension-guard.register') }}"
                   @if(request()->routeIs('pension-guard.register')) aria-current="page" @endif>
                    Create account
                </a>

            @endauth
        </nav>
    </div>
</header>

<main>
    @if (session('status'))
        <div class="panel status-message" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="panel error error-message" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="panel error error-message" role="alert">
            <p>Please correct the following:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer>
    <div class="footer-inner">
        <p>
            <strong>Pension Account Guard</strong> provides educational
            security information, not financial or pension advice.
        </p>

        <p>
            Never enter your pension password, one-time security code or
            online banking credentials into this application.
        </p>

        <p>
            Information from this application does not guarantee that a
            pension account is secure. Verify concerns directly with your
            pension provider using independently confirmed contact details.
        </p>

        <p>
            <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
            &nbsp;|&nbsp;
            <a href="{{ route('terms-and-conditions') }}">Terms and Conditions</a>
            &nbsp;|&nbsp;
            <a href="{{ route('about') }}">About</a>
            &nbsp;|&nbsp;
            <a href="{{ route('pricing') }}">Pricing</a>
            &nbsp;|&nbsp;
            <a href="{{ route('contact') }}">Contact</a>
        </p>
    </div>
</footer>
</body>
</html>
