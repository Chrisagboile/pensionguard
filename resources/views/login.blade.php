<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | CyberReadyAI</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #5b2dcc;
            --primary-dark: #4520a5;
            --text: #17203b;
            --muted: #68718b;
            --border: #e2e4ec;
            --background: #f7f6fc;
        }

        body {
            min-height: 100vh;
            font-family: Inter, -apple-system, BlinkMacSystemFont,
                "Segoe UI", Roboto, Arial, sans-serif;
            background:
                radial-gradient(
                    circle at 10% 20%,
                    rgba(91, 45, 204, 0.12),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 90% 80%,
                    rgba(91, 45, 204, 0.10),
                    transparent 30%
                ),
                var(--background);
            color: var(--text);
        }

        /* =========================
           PAGE
        ========================== */

        .page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
        }

        /* =========================
           LEFT PANEL
        ========================== */

        .brand-panel {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 55px 8%;
            color: white;
            background:
                linear-gradient(
                    145deg,
                    #35158f 0%,
                    #5b2dcc 48%,
                    #7046df 100%
                );
        }

        .brand-panel::before {
            content: "";
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.12);
            top: -180px;
            right: -180px;
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.10);
            bottom: -160px;
            left: -150px;
        }

        .brand {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 13px;
        }

        .brand-logo {
            width: 48px;
            height: 48px;
            border-radius: 13px;
            background: rgba(255,255,255,0.16);
            border: 1px solid rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }

        .brand-name {
            font-size: 23px;
            font-weight: 750;
            line-height: 1;
        }

        .brand-subtitle {
            margin-top: 5px;
            font-size: 12px;
            color: rgba(255,255,255,0.75);
        }

        .panel-content {
            position: relative;
            z-index: 2;
            max-width: 520px;
        }

        .panel-content h1 {
            font-size: clamp(38px, 4vw, 58px);
            line-height: 1.08;
            letter-spacing: -1.5px;
            margin-bottom: 22px;
        }

        .panel-content h1 span {
            color: #dcd0ff;
        }

        .panel-content p {
            max-width: 470px;
            color: rgba(255,255,255,0.78);
            font-size: 17px;
            line-height: 1.7;
        }

        .security-items {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 32px;
        }

        .security-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.88);
            font-size: 14px;
        }

        .security-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: rgba(255,255,255,0.13);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .panel-footer {
            position: relative;
            z-index: 2;
            color: rgba(255,255,255,0.55);
            font-size: 13px;
        }

        /* =========================
           RIGHT PANEL
        ========================== */

        .login-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 50px 8%;
            background: rgba(255,255,255,0.82);
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .mobile-brand {
            display: none;
        }

        .login-header {
            margin-bottom: 30px;
        }

        .login-header h2 {
            font-size: 32px;
            letter-spacing: -0.7px;
            margin-bottom: 8px;
        }

        .login-header p {
            color: var(--muted);
            font-size: 15px;
        }

        /* =========================
           VALIDATION
        ========================== */

        .alert {
            padding: 13px 15px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fff0f0;
            border: 1px solid #ffd2d2;
            color: #b42318;
        }

        .alert-success {
            background: #edfff5;
            border: 1px solid #c8f0d9;
            color: #087443;
        }

        .field-errors {
            margin-top: 6px;
            color: #c62828;
            font-size: 12px;
        }

        /* =========================
           FORM
        ========================== */

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 650;
            margin-bottom: 8px;
            color: #303850;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #8991a8;
            font-size: 17px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            height: 52px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: white;
            padding: 0 15px 0 45px;
            outline: none;
            color: var(--text);
            font-size: 15px;
            transition: 0.2s;
        }

        .form-input::placeholder {
            color: #a0a6b7;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(91,45,204,0.10);
        }

        .form-input.is-invalid {
            border-color: #dc3545;
        }

        /* =========================
           OPTIONS
        ========================== */

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 5px 0 25px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 13px;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
        }

        .forgot-password {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        /* =========================
           BUTTON
        ========================== */

        .login-button {
            width: 100%;
            height: 52px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(
                135deg,
                var(--primary),
                #7046df
            );
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(91,45,204,0.20);
            transition: 0.2s;
        }

        .login-button:hover {
            background: linear-gradient(
                135deg,
                var(--primary-dark),
                var(--primary)
            );
            transform: translateY(-1px);
            box-shadow: 0 13px 25px rgba(91,45,204,0.25);
        }

        .login-button:active {
            transform: translateY(0);
        }

        /* =========================
           REGISTER
        ========================== */

        .register {
            text-align: center;
            margin-top: 25px;
            color: var(--muted);
            font-size: 14px;
        }

        .register a {
            color: var(--primary);
            font-weight: 700;
        }

        .register a:hover {
            text-decoration: underline;
        }

        .back-home {
            text-align: center;
            margin-top: 25px;
        }

        .back-home a {
            color: #737b92;
            font-size: 13px;
        }

        .back-home a:hover {
            color: var(--primary);
        }

        /* =========================
           RESPONSIVE
        ========================== */

        @media (max-width: 850px) {

            .page {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                display: none;
            }

            .login-panel {
                min-height: 100vh;
                padding: 40px 6%;
            }

            .mobile-brand {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                margin-bottom: 40px;
            }

            .mobile-brand .brand-logo {
                background: var(--light-purple);
                color: var(--primary);
                border: none;
            }

            .mobile-brand .brand-name {
                color: var(--primary);
            }
        }

        @media (max-width: 500px) {

            .login-panel {
                padding: 30px 20px;
            }

            .login-header h2 {
                font-size: 28px;
            }

            .form-options {
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>

<body>

<div class="page">

    {{-- =====================================
         LEFT BRAND PANEL
    ====================================== --}}

    <section class="brand-panel">

        <a href="{{ url('/') }}" class="brand">

            <div class="brand-logo">
                🛡️
            </div>

            <div>
                <div class="brand-name">
                    CyberReadyAI
                </div>

                <div class="brand-subtitle">
                    Cybersecurity Awareness Platform
                </div>
            </div>

        </a>


        <div class="panel-content">

            <h1>
                Welcome back to
                <span>CyberReadyAI.</span>
            </h1>

            <p>
                Continue building a stronger security culture.
                Access your training, assessments, reports and
                cybersecurity resources from one secure platform.
            </p>

            <div class="security-items">

                <div class="security-item">
                    <div class="security-icon">
                        🛡️
                    </div>
                    <span>
                        Strengthen your organisation's security awareness
                    </span>
                </div>

                <div class="security-item">
                    <div class="security-icon">
                        📊
                    </div>
                    <span>
                        Track learning and assessment progress
                    </span>
                </div>

                <div class="security-item">
                    <div class="security-icon">
                        🔒
                    </div>
                    <span>
                        Protect your people and digital environment
                    </span>
                </div>

            </div>

        </div>


        <div class="panel-footer">
            © {{ date('Y') }} CyberReadyAI. All rights reserved.
        </div>

    </section>


    {{-- =====================================
         LOGIN PANEL
    ====================================== --}}

    <main class="login-panel">

        <div class="login-container">

            {{-- Mobile brand --}}

            <a href="{{ url('/') }}" class="mobile-brand">

                <div class="brand-logo">
                    🛡️
                </div>

                <div class="brand-name">
                    CyberReadyAI
                </div>

            </a>


            <div class="login-header">

                <h2>
                    Sign in
                </h2>

                <p>
                    Enter your details to access your account.
                </p>

            </div>


            {{-- Session status --}}

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif


            {{-- General validation errors --}}

            @if ($errors->any())
                <div class="alert alert-error">
                    Please check your details and try again.
                </div>
            @endif


            {{-- Login form --}}

            <form method="POST" action="{{ route('login') }}">

                @csrf


                {{-- Email --}}

                <div class="form-group">

                    <label
                        for="email"
                        class="form-label"
                    >
                        Email address
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            ✉
                        </span>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            class="form-input @error('email') is-invalid @enderror"
                            required
                            autofocus
                            autocomplete="email"
                        >

                    </div>

                    @error('email')
                        <div class="field-errors">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- Password --}}

                <div class="form-group">

                    <label
                        for="password"
                        class="form-label"
                    >
                        Password
                    </label>

                    <div class="input-wrapper">

                        <span class="input-icon">
                            🔒
                        </span>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            class="form-input @error('password') is-invalid @enderror"
                            required
                            autocomplete="current-password"
                        >

                    </div>

                    @error('password')
                        <div class="field-errors">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- Remember / Forgot password --}}

                <div class="form-options">

                    <label class="remember">

                        <input
                            type="checkbox"
                            name="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >

                        <span>
                            Remember me
                        </span>

                    </label>


                    @if (Route::has('password.request'))

                        <a
                            href="{{ route('password.request') }}"
                            class="forgot-password"
                        >
                            Forgot password?
                        </a>

                    @endif

                </div>


                {{-- Submit --}}

                <button
                    type="submit"
                    class="login-button"
                >
                    Sign in to CyberReadyAI
                </button>

            </form>


            {{-- Register --}}

            @if (Route::has('register'))

                <div class="register">

                    Don't have an account?

                    <a href="{{ route('register') }}">
                        Create an account
                    </a>

                </div>

            @endif


            <div class="back-home">

                <a href="{{ url('/') }}">
                    ← Back to Tekbiserv Ltd
                </a>

            </div>

        </div>

    </main>

</div>

</body>
</html>
