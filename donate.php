<?php
require_once __DIR__ . '/config.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Donate Us | YourUni</title>
    <link rel="stylesheet" href="assets/youruni-global.css">
    <link rel="stylesheet" href="assets/donate.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >
    <style>
        :root {
            --d-bg: #f8fafc;
            --d-surface: #fff;
            --d-text: #0f172a;
            --d-muted: #64748b;
            --d-border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--d-bg);
            color: var(--d-text);
        }

        .pagebar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding: 12px 5%;
            background: #121c2d;
            color: #fff;
        }

        .homepage {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo {
            font-size: 26px;
        }

        .title h1 {
            margin: 0;
            font-size: 1.3rem;
            color: #fff;
        }

        .title h1 span {
            color: #3b82f6;
        }

        .title p {
            margin: 0;
            font-size: .75rem;
            color: #cbd5e1;
        }

        .links {
            display: flex;
            gap: 26px;
            align-items: center;
            flex-wrap: wrap;
        }

        .links a {
            color: #fff;
            text-decoration: none;
            padding: 8px 0 6px;
            font-size: .95rem;
        }

        .links a.active {
            color: #60a5fa;
            border-bottom: 2px solid #3b82f6;
        }

        .hero {
            width: min(1000px, 92%);
            margin: 52px auto;
        }

        .hero-box {
            background: linear-gradient(130deg, #101e33, #1e40af);
            border-radius: 24px;
            padding: clamp(28px, 6vw, 64px);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .eyebrow {
            text-transform: uppercase;
            letter-spacing: .14em;
            font-size: .76rem;
            font-weight: 800;
            color: #bfdbfe;
        }

        .hero h2 {
            font-size: clamp(2rem, 5vw, 3.6rem);
            line-height: 1.08;
            margin: 16px 0;
        }

        .hero p {
            font-size: 1.02rem;
            line-height: 1.8;
            max-width: 720px;
            color: #dbeafe;
        }

        .support-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-top: 22px;
        }

        .support-card {
            background: var(--d-surface);
            border: 1px solid var(--d-border);
            border-radius: 18px;
            padding: 24px;
        }

        .support-card h3 {
            margin-top: 0;
        }

        .muted {
            color: var(--d-muted);
            line-height: 1.7;
        }

        .local-payment-qr {
            display: grid;
            place-items: center;
            min-height: 220px;
            margin-top: 18px;
            padding: 20px;
            border: 2px dashed #94a3b8;
            border-radius: 14px;
            background: #f8fafc;
            text-align: center;
        }

        .local-payment-qr p {
            margin: 0;
            color: var(--d-muted);
        }

        .local-payment-qr img {
            display: block;
            width: min(100%, 280px);
            height: auto;
            border-radius: 8px;
        }

        .notice {
            border: 1px solid #f59e0b;
            background: #fffbeb;
            color: #92400e;
            border-radius: 14px;
            padding: 18px;
            line-height: 1.65;
            margin-top: 20px;
        }

        .back {
            display: inline-block;
            margin-top: 18px;
            color: #2563eb;
            font-weight: 800;
            text-decoration: none;
        }

        .yu-site-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 24px 5%;
            background: #0b1423;
            color: #94a3b8;
            font-size: .85rem;
        }

        .yu-site-footer a {
            color: #bfdbfe;
        }

        @media (max-width: 700px) {
            .support-grid {
                grid-template-columns: 1fr;
            }

            .links {
                gap: 14px;
            }

            .hero {
                margin: 28px auto;
            }
        }

        html[data-theme="dark"] body {
            background: #0b1220 !important;
            color: #e5edf7 !important;
        }

        html[data-theme="dark"] .support-card {
            background: #111c2d !important;
            border-color: #2b3a50 !important;
        }

        html[data-theme="dark"] .muted {
            color: #a7b4c7 !important;
        }

        html[data-theme="dark"] .local-payment-qr {
            background: #111c2d;
            border-color: #475569;
        }

        html[data-theme="dark"] .notice {
            background: #382b11 !important;
            color: #fde68a !important;
            border-color: #854d0e !important;
        }
    </style>
</head>
<body class="donate-page">
    <nav class="pagebar">
        <a href="index.php" class="homepage">
            <div class="logo">🎓</div>
            <div class="title">
                <h1>Your<span>Uni</span></h1>
                <p>Explore Myanmar's Universities</p>
            </div>
        </a>
        <div class="links">
            <a href="index.php">Home</a>
            <a href="Universities.php">Universities</a>
            <a href="opportunities.php">Opportunities</a>
            <a href="About.html">About Us</a>
        </div>
    </nav>

    <main class="hero">
        <section class="hero-box">
            <div class="eyebrow" data-i18n="donate.eyebrow">
                DONATE US · SUPPORT EDUCATION ACCESS
            </div>
            <h2 data-i18n="donate.title">Help students find their next step.</h2>
            <p data-i18n="donate.copy">
                YourUni brings Myanmar university information together to make educational choices easier to explore.
                Support can help maintain the website, improve information quality, and keep the project accessible.
            </p>
        </section>

        <div class="support-grid">
            <section class="support-card">
                <h3 data-i18n="donate.currentTitle">Current donation status</h3>
                <p class="muted" data-i18n="donate.currentCopy">
                    Local QR payments are being prepared. This page is not currently collecting or verifying donations.
                </p>

                <h4>Local payment QR</h4>
                <div class="local-payment-qr" id="local-payment-qr">
                    

                    <!--
                        ===== ADD YOUR REAL LOCAL PAYMENT QR IMAGE HERE =====
                        1. Put your provider-issued QR image in assets/images/.
                        2. Replace the filename below with your image's filename.
                        3. Remove the comment markers around the img element.
                    -->
                     <img src="photos/kpay.jpg" alt="Scan to donate using local payment"> 
                </div>

                <div class="notice" data-i18n="donate.notice">
                   Can be donated!
                </div>
            </section>

            <section class="support-card">
                <h3 data-i18n="donate.transparencyTitle">How support will be used</h3>
                <p class="muted" data-i18n="donate.transparencyCopy">
                    Potential uses include hosting costs, maintaining university records, accessibility improvements,
                    and site development. Donations must remain optional and never affect access to university information.
                </p>
                <p class="muted" data-i18n="donate.privacy">
                    YourUni will never ask for a payment PIN, wallet password, or card details on this website.
                </p>
            </section>
        </div>

        <a href="About.html" class="back" data-i18n="donate.back">← Back to About YourUni</a>
    </main>

    <footer class="yu-site-footer">
        <span><strong>YourUni</strong> · Explore Myanmar's Universities</span>
        <span><a href="About.html">About Us</a></span>
    </footer>

    <script src="assets/youruni-global.js" defer></script>
</body>
</html>