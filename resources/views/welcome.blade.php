```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CyberReadyAI | Cybersecurity Awareness Platform</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #5b2dcc;
            --primary-dark: #4320a5;
            --text: #111a3a;
            --muted: #66708f;
            --light-purple: #f4f0ff;
            --border: #e8e6f0;
            --white: #ffffff;
        }

        body {
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI",
                Roboto, Arial, sans-serif;
            color: var(--text);
            background: #fff;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            height: 82px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 7%;
            border-bottom: 1px solid #eeeeF5;
            background: rgba(255, 255, 255, 0.95);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 42px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 25px;
        }

        .brand-text strong {
            display: block;
            font-size: 22px;
            color: var(--primary);
            line-height: 1.1;
        }

        .brand-text span {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-top: 3px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 35px;
        }

        .nav-links a {
            font-size: 15px;
            color: #30384f;
            transition: 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--primary);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 22px;
        }

        .login {
            color: #27304a;
            font-weight: 500;
        }

        .register-btn,
        .primary-btn {
            background: var(--primary);
            color: white;
            padding: 13px 24px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            transition: 0.2s;
            display: inline-block;
        }

        .register-btn:hover,
        .primary-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* =========================
           HERO
        ========================= */

        .hero {
            min-height: 560px;
            padding: 75px 7%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 60px;
            background:
                radial-gradient(circle at 75% 45%, #eee8ff 0, transparent 35%),
                linear-gradient(135deg, #ffffff 0%, #f8f6ff 100%);
        }

        .hero-content {
            max-width: 620px;
        }

        .hero h1 {
            font-size: clamp(45px, 5vw, 68px);
            line-height: 1.05;
            letter-spacing: -2px;
            margin-bottom: 25px;
            font-weight: 750;
        }

        .hero h1 span {
            color: var(--primary);
        }

        .hero-description {
            font-size: 18px;
            color: var(--muted);
            max-width: 590px;
            margin-bottom: 30px;
        }

        .benefits {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin-bottom: 32px;
        }

        .benefit {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #55607c;
            font-size: 14px;
            font-weight: 500;
        }

        .benefit-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eee7ff;
            color: var(--primary);
            font-size: 19px;
        }

        .hero-buttons {
            display: flex;
            gap: 18px;
        }

        .secondary-btn {
            padding: 13px 25px;
            border: 1px solid #d6d1e7;
            border-radius: 8px;
            color: var(--primary);
            font-weight: 600;
            background: white;
        }

        .secondary-btn:hover {
            background: var(--light-purple);
        }

        /* =========================
           HERO ILLUSTRATION
        ========================= */

        .hero-visual {
            position: relative;
            height: 430px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .glow {
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: #eee7ff;
            filter: blur(2px);
        }

        .laptop {
            position: relative;
            width: 440px;
            z-index: 2;
        }

        .screen {
            height: 275px;
            border: 12px solid #202e57;
            border-radius: 15px 15px 5px 5px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 40px rgba(48, 37, 100, 0.15);
        }

        .shield {
            width: 115px;
            height: 140px;
            background: linear-gradient(135deg, #7851e9, #4d23bc);
            clip-path: polygon(
                50% 0,
                91% 17%,
                84% 70%,
                50% 100%,
                16% 70%,
                9% 17%
            );
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }

        .base {
            width: 500px;
            height: 28px;
            background: #dfe1ee;
            border-radius: 0 0 35px 35px;
            margin-left: -30px;
            box-shadow: 0 12px 25px rgba(30, 35, 70, 0.15);
        }

        .floating-card {
            position: absolute;
            z-index: 4;
            background: white;
            padding: 18px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(40, 30, 90, 0.12);
            font-size: 26px;
            color: var(--primary);
        }

        .floating-card.one {
            top: 35px;
            left: 15px;
        }

        .floating-card.two {
            top: 55px;
            right: 5px;
        }

        .floating-card.three {
            bottom: 70px;
            right: -10px;
        }

        /* =========================
           FEATURES
        ========================= */

        .features {
            padding: 65px 7%;
            background: white;
        }

        .section-heading {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-heading h2 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .section-heading p {
            color: var(--muted);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .feature-card {
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 28px 24px;
            background: white;
            transition: 0.25s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(40, 30, 90, 0.08);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #eee7ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 18px;
        }

        .feature-card:nth-child(2) .feature-icon {
            background: #e4f7ee;
            color: #15965c;
        }

        .feature-card:nth-child(3) .feature-icon {
            background: #e5efff;
            color: #2766d8;
        }

        .feature-card:nth-child(4) .feature-icon {
            background: #fff0d9;
            color: #ed8b00;
        }

        .feature-card h3 {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .feature-card p {
            color: var(--muted);
            font-size: 14px;
        }

        /* =========================
           FOOTER
        ========================= */

        footer {
            background: #f7f4ff;
            padding: 55px 7% 25px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.5fr;
            gap: 50px;
        }

        .footer-brand p {
            color: var(--muted);
            font-size: 14px;
            max-width: 280px;
            margin-top: 15px;
        }

        footer h4 {
            margin-bottom: 15px;
        }

        footer ul {
            list-style: none;
        }

        footer li {
            margin-bottom: 9px;
        }

        footer li a {
            color: #626b86;
            font-size: 14px;
        }

        footer li a:hover {
            color: var(--primary);
        }

        .newsletter p {
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 15px;
        }

        .newsletter-form {
            display: flex;
            gap: 8px;
        }

        .newsletter-form input {
            min-width: 0;
            flex: 1;
            padding: 12px 14px;
            border: 1px solid #d9d5e6;
            border-radius: 7px;
            outline: none;
        }

        .newsletter-form button {
            border: none;
            background: var(--primary);
            color: white;
            border-radius: 7px;
            padding: 0 18px;
            font-weight: 600;
        }

        .copyright {
            text-align: center;
            border-top: 1px solid #e5e1f0;
            margin-top: 40px;
            padding-top: 20px;
            color: #747c94;
            font-size: 13px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1000px) {

            .nav-links {
                display: none;
            }

            .hero {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-content {
                margin: auto;
            }

            .hero-description {
                margin-left: auto;
                margin-right: auto;
            }

            .benefits,
            .hero-buttons {
                justify-content: center;
            }

            .feature-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {

            .navbar {
                padding: 0 5%;
            }

            .brand-text span {
                display: none;
            }

            .login {
                display: none;
            }

            .hero {
                padding: 55px 5%;
            }

            .hero h1 {
                font-size: 42px;
            }

            .hero-visual {
                transform: scale(0.75);
                margin: -40px 0;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }

            .newsletter-form {
                flex-direction: column;
            }

            .newsletter-form button {
                padding: 12px;
            }
        }
    </style>
</head>

<body>

    {{-- =========================
         NAVIGATION
    ========================== --}}

    <header class="navbar">

        <a href="{{ url('/') }}" class="brand">

            <div class="brand-icon">
                🛡️
            </div>

            <div class="brand-text">
                <strong>CyberReadyAI</strong>
                <span>Cybersecurity Awareness Platform</span>
            </div>

        </a>
        <nav class="nav-links">

            <a href="{{ url('/') }}" class="active">
                Home
            </a>

            <a href="{{ url('/webpage/about.html') }}">
                About
            </a>

            <a href="{{ url('/webpage/services.html') }}">
                Services
            </a>

            <a href="{{ url('/webpage/contact.html') }}">
                Contact
            </a>

            <a href="{{ url('/webpage/donate.html') }}">
                Donate
            </a>

        </nav>
        <div class="nav-actions">

            @auth
                <a href="{{ url('/dashboard') }}" class="login">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="login">
                    Log in
                </a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="register-btn">
                        Register
                    </a>
                @endif
            @endauth

        </div>

    </header>


    {{-- =========================
         HERO
    ========================== --}}

    <section class="hero">

        <div class="hero-content">

            <h1>
                Build a Stronger
                <br>
                Security Culture
                <br>
                <span>Together</span>
            </h1>

            <p class="hero-description">
                CyberReadyAI helps organisations empower their teams
                with the knowledge and skills to stay safe in the
                digital world.
            </p>

            <div class="benefits">

                <div class="benefit">
                    <div class="benefit-icon">🛡️</div>
                    <span>Improve Awareness</span>
                </div>

                <div class="benefit">
                    <div class="benefit-icon">📈</div>
                    <span>Track Progress</span>
                </div>

                <div class="benefit">
                    <div class="benefit-icon">🔒</div>
                    <span>Reduce Risk</span>
                </div>

            </div>

            <div class="hero-buttons">

                <a href="{{ route('register') }}" class="primary-btn">
                    Get Started →
                </a>

                <a href="#features" class="secondary-btn">
                    Learn More →
                </a>

            </div>

        </div>


        {{-- Hero Illustration --}}

        <div class="hero-visual">

            <div class="glow"></div>

            <div class="floating-card one">
                📊
            </div>

            <div class="floating-card two">
                ☑️
            </div>

            <div class="floating-card three">
                📈
            </div>

            <div class="laptop">

                <div class="screen">

                    <div class="shield">
                        🔒
                    </div>

                </div>

                <div class="base"></div>

            </div>

        </div>

    </section>


    {{-- =========================
         FEATURES
    ========================== --}}

    <section class="features" id="features">

        <div class="section-heading">

            <h2>
                Everything you need to build awareness
            </h2>

            <p>
                Powerful tools and resources to help your organisation stay secure.
            </p>

        </div>


        <div class="feature-grid">

            <div class="feature-card">

                <div class="feature-icon">
                    👥
                </div>

                <h3>
                    Engaging Learning
                </h3>

                <p>
                    Interactive modules and training materials
                    that make cybersecurity learning engaging.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    📋
                </div>

                <h3>
                    Assess & Measure
                </h3>

                <p>
                    Assess knowledge and track progress with
                    detailed reports and analytics.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    📊
                </div>

                <h3>
                    Smart Reporting
                </h3>

                <p>
                    Generate insights and reports to measure
                    awareness and reduce security risks.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    🎓
                </div>

                <h3>
                    Continuous Training
                </h3>

                <p>
                    Keep your team updated with the latest
                    security practices and real-world scenarios.
                </p>

            </div>

        </div>

    </section>


    {{-- =========================
         FOOTER
    ========================== --}}

    <footer id="contact">

        <div class="footer-grid">

            <div class="footer-brand">

                <div class="brand">

                    <div class="brand-icon">
                        🛡️
                    </div>

                    <div class="brand-text">
                        <strong>CyberReadyAI</strong>
                    </div>

                </div>

                <p>
                    Empowering organisations to build a strong
                    security culture through awareness, training
                    and smart insights.
                </p>

            </div>


            <div>

                <h4>Quick Links</h4>

                <ul>
                    <li>
                        <a href="#features">Features</a>
                    </li>

                    <li>
                        <a href="#about">About</a>
                    </li>

                    <li>
                        <a href="#contact">Contact</a>
                    </li>
                </ul>

            </div>


            <div>

                <h4>Resources</h4>

                <ul>
                    <li>
                        <a href="#">Documentation</a>
                    </li>

                    <li>
                        <a href="#">Guides</a>
                    </li>

                    <li>
                        <a href="#">Training</a>
                    </li>
                </ul>

            </div>


            <div class="newsletter">

                <h4>Stay Connected</h4>

                <p>
                    Get the latest updates and cybersecurity tips
                    delivered to your inbox.
                </p>

                <form class="newsletter-form">

                    <input
                        type="email"
                        placeholder="Your email"
                    >

                    <button type="submit">
                        Subscribe
                    </button>

                </form>

            </div>

        </div>


        <div class="copyright">
            © {{ date('Y') }} Tekbiserv Ltd. All rights reserved.
        </div>

    </footer>

</body>
</html>
```
