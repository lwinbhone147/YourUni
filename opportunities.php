<?php
require_once __DIR__ . '/db_pdo.php';
require_once __DIR__ . '/opportunities_data.php';
$opportunities = [];
$opportunityError = '';
try {
    // Use the same database settings as the rest of the consolidated YourUni site.
    // The previous version still pointed at the retired 'universities' database.
    $pdo = youruniPdo();
    // Local-development bootstrap. The admin dashboard also initializes this table.
    youruniEnsureOpportunityTable($pdo);
    $opportunities = $pdo->query('SELECT title, icon, description, university_examples, page_link, css_class FROM opportunities WHERE is_active = 1 ORDER BY sort_order ASC, title ASC')->fetchAll();
} catch (Throwable $e) {
    error_log('YourUni public opportunities page: ' . $e->getMessage());
    $opportunityError = 'database-error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Fields & Careers - YourUni</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --agri-bg: #f7fee7;
  --agri-accent: #65a30d;

  --edu-bg: #fffbeb;
  --edu-accent: #d97706;

  --art-bg: #fff1f2;
  --art-accent: #e11d48;

  --science-bg: #f0f9ff;
  --science-accent: #0ea5e9;

  --defense-bg: #f8fafc;
  --defense-accent: #475569;
            --bg-main: #f8fafc;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --nav-bg: #0f172a;
            --brand-blue: #2563eb;
            --brand-light: #eff6ff;
            
            /* Field Accent Colors */
            --eng-accent: #d97706;
            --eng-bg: #fffbeb;
            
            --cs-accent: #2563eb;
            --cs-bg: #eff6ff;
            
            --med-accent: #059669;
            --med-bg: #ecfdf5;
            
            --biz-accent: #7c3aed;
            --biz-bg: #f5f3ff;
            
            --lang-accent: #e11d48;
            --lang-bg: #fff1f2;
            
            --law-accent: #0d9488;
            --law-bg: #f0fdfa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--text-dark);
            background-color: var(--bg-main);
            line-height: 1.5;
            min-height: 100vh;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

       .pagebar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 5%;
            background-color: #121c2d;
            flex-wrap: wrap;
            gap: 15px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.5;
        }

        .homepage {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo {
            font-size: 26px;
            color: #0b5ee7;
        }

        .title h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffffff;
        }

        .title h1 span {
            color: #3b82f6;
        }

        .title p {
            font-size: 0.75rem;
            color: #5a6474;
        }
        
        .links {
            display: flex;
            gap: 30px; 
            align-items: center; 
        }

        .links a {
            font-weight: 500;
            font-size: 0.95rem;
            color: #ffffff;
            padding-bottom: 5px;
            transition: color 0.2s;
        }

        .links a:hover {
            color: #0b5ee7;
        }

        .links a.active {
            color: #0b5ee7;
            border-bottom: 2px solid #0b5ee7;
        }

        /* MAIN CONTAINER */
        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 50px 24px 80px;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 50px;
        }

        .badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .hero-section h2 {
            font-size: 2.5rem;
            color: var(--text-dark);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 12px;
        }

        .hero-section p {
            color: var(--text-muted);
            font-size: 1.05rem;
            max-width: 580px;
            margin: 0 auto;
        }

        /* CATEGORY CARDS GRID */
        .fields-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 28px;
        }

        /* FIELD CARD BASE */
        .field-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .field-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: transparent;
            transition: background 0.3s ease;
        }

        .field-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.08);
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .field-card:hover .icon-wrapper {
            transform: scale(1.08);
        }

        .field-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
            letter-spacing: -0.01em;
        }

        .field-desc {
            font-size: 0.92rem;
            color: var(--text-muted);
            margin-bottom: 22px;
            line-height: 1.6;
        }

        .unis-count {
            font-size: 0.8rem;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            display: inline-block;
            align-self: flex-start;
        }

        .card-footer {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88rem;
            font-weight: 700;
            transition: color 0.2s ease;
        }

        .card-footer span.arrow {
            display: inline-block;
            transition: transform 0.25s ease;
        }

        .field-card:hover .card-footer span.arrow {
            transform: translateX(6px);
        }

        /* CARD INDIVIDUAL ACCENT THEMES */
        
        /* Engineering */
        .card-eng .icon-wrapper { background: var(--eng-bg); }
        .card-eng .unis-count { background: var(--eng-bg); color: var(--eng-accent); }
        .card-eng .card-footer { color: var(--eng-accent); }
        .card-eng:hover { border-color: rgba(217, 119, 6, 0.4); }
        .card-eng:hover::before { background: var(--eng-accent); }

        /* Computer Science */
        .card-cs .icon-wrapper { background: var(--cs-bg); }
        .card-cs .unis-count { background: var(--cs-bg); color: var(--cs-accent); }
        .card-cs .card-footer { color: var(--cs-accent); }
        .card-cs:hover { border-color: rgba(37, 99, 235, 0.4); }
        .card-cs:hover::before { background: var(--cs-accent); }

        /* Medicine */
        .card-med .icon-wrapper { background: var(--med-bg); }
        .card-med .unis-count { background: var(--med-bg); color: var(--med-accent); }
        .card-med .card-footer { color: var(--med-accent); }
        .card-med:hover { border-color: rgba(5, 150, 105, 0.4); }
        .card-med:hover::before { background: var(--med-accent); }

        /* Business */
        .card-biz .icon-wrapper { background: var(--biz-bg); }
        .card-biz .unis-count { background: var(--biz-bg); color: var(--biz-accent); }
        .card-biz .card-footer { color: var(--biz-accent); }
        .card-biz:hover { border-color: rgba(124, 58, 237, 0.4); }
        .card-biz:hover::before { background: var(--biz-accent); }

        /* Languages */
        .card-lang .icon-wrapper { background: var(--lang-bg); }
        .card-lang .unis-count { background: var(--lang-bg); color: var(--lang-accent); }
        .card-lang .card-footer { color: var(--lang-accent); }
        .card-lang:hover { border-color: rgba(225, 29, 72, 0.4); }
        .card-lang:hover::before { background: var(--lang-accent); }

        /* Law */
        .card-law .icon-wrapper { background: var(--law-bg); }
        .card-law .unis-count { background: var(--law-bg); color: var(--law-accent); }
        .card-law .card-footer { color: var(--law-accent); }
        .card-law:hover { border-color: rgba(13, 148, 136, 0.4); }
        .card-law:hover::before { background: var(--law-accent); }

        /* =========================================
  /* Agriculture */
.card-agri .icon-wrapper { background: var(--agri-bg); }
.card-agri .unis-count { background: var(--agri-bg); color: var(--agri-accent); }
.card-agri .card-footer { color: var(--agri-accent); }
.card-agri:hover { border-color: rgba(101, 163, 13, 0.4); }
.card-agri:hover::before { background: var(--agri-accent); }

/* Education */
.card-edu .icon-wrapper { background: var(--edu-bg); }
.card-edu .unis-count { background: var(--edu-bg); color: var(--edu-accent); }
.card-edu .card-footer { color: var(--edu-accent); }
.card-edu:hover { border-color: rgba(217, 119, 6, 0.4); }
.card-edu:hover::before { background: var(--edu-accent); }

/* Arts & Culture */
.card-art .icon-wrapper { background: var(--art-bg); }
.card-art .unis-count { background: var(--art-bg); color: var(--art-accent); }
.card-art .card-footer { color: var(--art-accent); }
.card-art:hover { border-color: rgba(225, 29, 72, 0.4); }
.card-art:hover::before { background: var(--art-accent); }

/* Arts & Science */
.card-science .icon-wrapper { background: var(--science-bg); }
.card-science .unis-count { background: var(--science-bg); color: var(--science-accent); }
.card-science .card-footer { color: var(--science-accent); }
.card-science:hover { border-color: rgba(14, 165, 233, 0.4); }
.card-science:hover::before { background: var(--science-accent); }

/* Defense */
.card-defense .icon-wrapper { background: var(--defense-bg); }
.card-defense .unis-count { background: var(--defense-bg); color: var(--defense-accent); }
.card-defense .card-footer { color: var(--defense-accent); }
.card-defense:hover { border-color: rgba(71, 85, 105, 0.4); }
.card-defense:hover::before { background: var(--defense-accent); }

        /* RESPONSIVE DESIGN */
        @media (max-width: 768px) {
            .hero-section h2 { font-size: 1.9rem; }
            .fields-grid { grid-template-columns: 1fr; }
        }
        .data-message { grid-column: 1 / -1; padding: 24px; border: 1px solid var(--yu-border, #e2e8f0); border-radius: 14px; background: var(--yu-surface, #fff); color: var(--yu-muted, #64748b); text-align: center; }
        html[data-theme="dark"] .data-message { background: var(--yu-surface, #111c2d) !important; color: var(--yu-text, #e5edf7) !important; border-color: var(--yu-border, #2b3a50) !important; }
    </style>
    <link rel="stylesheet" href="assets/youruni-global.css">
    <style>
        :root {
            --atlas-ink: #20201d;
            --atlas-red: #d9432e;
            --atlas-yellow: #f2c84b;
            --atlas-green: #317b58;
            --atlas-paper: #fffaf0;
            --atlas-line: #ded5c3;
        }

        body {
            background: var(--atlas-paper);
            color: var(--atlas-ink);
            font-family: 'Plus Jakarta Sans', 'Trebuchet MS', sans-serif;
        }

        html body .pagebar {
            background: #211f1a;
            border-bottom: 5px solid var(--atlas-yellow);
        }

        html body .pagebar .logo,
        html body .pagebar .title h1 span { color: var(--atlas-yellow); }
        html body .pagebar .title p { color: #e5dfd1; }
        html body .pagebar .links a { color: #fff; }
        html body .pagebar .links a.active { color: #f8d768 !important; border-bottom-color: var(--atlas-yellow) !important; }

        .container {
            width: min(1320px, 92%);
            max-width: none;
            padding: 28px 0 52px;
        }

        .field-atlas-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(220px, .34fr);
            gap: 24px;
            align-items: center;
            margin-bottom: 16px;
            padding: 27px 28px;
            border: 2px solid var(--atlas-ink);
            background-color: #e9f1e7;
            background-image: radial-gradient(#c6d8c6 0.8px, transparent 0.8px);
            background-size: 15px 15px;
            box-shadow: 6px 6px 0 var(--atlas-yellow);
        }

        .atlas-kicker {
            display: inline-block;
            padding: 6px 9px;
            background: var(--atlas-yellow);
            color: var(--atlas-ink);
            font-size: .72rem;
            font-weight: 900;
            text-transform: uppercase;
            transform: rotate(-1deg);
        }

        .field-atlas-hero h2 {
            margin: 13px 0 8px;
            color: var(--atlas-ink);
            font: 700 2.8rem/1.02 Georgia, 'Times New Roman', serif;
        }

        .field-atlas-hero p {
            max-width: 720px;
            margin: 0;
            color: #45483f;
            font-size: .98rem;
        }

        .atlas-stamp {
            padding: 14px;
            border: 2px solid var(--atlas-ink);
            background: #fffdf7;
            box-shadow: 4px 4px 0 var(--atlas-red);
            transform: rotate(1deg);
        }

        .atlas-stamp strong { display: block; color: var(--atlas-red); font: 700 2.1rem/1 Georgia, serif; }
        .atlas-stamp span { display: block; margin-top: 5px; font-size: .78rem; font-weight: 900; text-transform: uppercase; }
        .atlas-stamp small { display: block; margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--atlas-line); color: #615b4f; font-size: .76rem; line-height: 1.4; }

        .atlas-index {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin: 20px 0 16px;
            padding: 13px;
            border: 1px solid var(--atlas-line);
            background: #fffdf7;
        }

        .atlas-index-label { flex: 0 0 100%; color: var(--atlas-green); font-size: .72rem; font-weight: 900; text-transform: uppercase; }
        .atlas-index a {
            padding: 6px 9px;
            border: 1px solid #d8cfbe;
            background: #fff9e8;
            color: var(--atlas-ink);
            font-size: .77rem;
            font-weight: 800;
            line-height: 1.3;
        }
        .atlas-index a:hover, .atlas-index a:focus-visible { background: var(--atlas-yellow); outline-color: var(--atlas-ink); }

        .atlas-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin: 0 0 13px;
        }

        .atlas-tools label { color: var(--atlas-green); font-size: .75rem; font-weight: 900; text-transform: uppercase; }
        .atlas-search-wrap { display: flex; align-items: center; gap: 9px; flex: 1; max-width: 500px; }
        .atlas-search-wrap input {
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid #cfc5b2;
            border-radius: 2px;
            background: #fffdf7;
            color: var(--atlas-ink);
            font: inherit;
            font-size: .88rem;
        }
        .atlas-count { flex: 0 0 auto; color: #6a655b; font-size: .82rem; font-weight: 800; }

        .fields-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .field-card {
            --field-accent: var(--atlas-green);
            --field-wash: #e5f0e5;
            min-width: 0;
            min-height: 0;
            padding: 17px 18px 14px;
            border: 1px solid #d8d0c0;
            border-radius: 3px;
            background: #fffdf7;
            box-shadow: 3px 3px 0 #e7dcc6;
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }

        .field-card::before { height: 5px; background: var(--field-accent); }
        .field-card:hover { transform: translate(-2px, -3px); border-color: var(--field-accent); box-shadow: 6px 6px 0 var(--field-wash); }
        .field-card:focus-visible { outline: 3px solid var(--field-accent); outline-offset: 3px; }
        .field-card[hidden] { display: none !important; }

        .card-eng { --field-accent: #bb4b22; --field-wash: #fae4d5; }
        .card-cs { --field-accent: #2874a5; --field-wash: #dcecf4; }
        .card-med { --field-accent: #317b58; --field-wash: #dcebe1; }
        .card-biz { --field-accent: #a56d13; --field-wash: #f6e8bd; }
        .card-lang { --field-accent: #bc3e58; --field-wash: #f8e1e4; }
        .card-law { --field-accent: #14796e; --field-wash: #d9efea; }
        .card-agri { --field-accent: #55822c; --field-wash: #e6efd9; }
        .card-edu { --field-accent: #a46616; --field-wash: #f5e6ca; }
        .card-art { --field-accent: #b94b40; --field-wash: #f6e0d9; }
        .card-science { --field-accent: #287c9e; --field-wash: #dcecf2; }
        .card-defense { --field-accent: #59645d; --field-wash: #e2e8e1; }

        .field-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
        .field-card .icon-wrapper {
            width: 44px;
            height: 44px;
            margin-bottom: 12px;
            border-radius: 3px;
            background: var(--field-wash);
            color: var(--field-accent);
            font-size: 22px;
        }
        .field-card:hover .icon-wrapper { transform: rotate(-4deg); }
        .field-number { color: var(--field-accent); font: 700 1.25rem/1 Georgia, serif; }
        .field-card .field-title { margin-bottom: 6px; color: var(--atlas-ink); font: 700 1.2rem/1.18 Georgia, 'Times New Roman', serif; }
        .field-card .field-desc { margin-bottom: 12px; color: #5a574f; font-size: .84rem; line-height: 1.48; }
        .field-card .unis-count { max-width: 100%; padding: 5px 8px; border-radius: 2px; background: var(--field-wash); color: var(--field-accent); font-size: .7rem; line-height: 1.35; overflow-wrap: anywhere; }
        .field-card .card-footer { margin-top: 14px; padding-top: 10px; border-top: 1px solid var(--atlas-line); color: var(--field-accent); font-size: .8rem; }
        .card-footer span.arrow { font-size: 1rem; }
        .atlas-empty { display: none; grid-column: 1 / -1; padding: 22px; border: 1px dashed #c9bda6; background: #fffdf7; color: #625c50; text-align: center; }
        .atlas-empty.is-visible { display: block; }

        @media (max-width: 900px) {
            .container { width: 92%; }
            .fields-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .field-atlas-hero { grid-template-columns: minmax(0, 1fr) 190px; gap: 16px; }
            .field-atlas-hero h2 { font-size: 2.4rem; }
        }

        @media (max-width: 650px) {
            html body .pagebar { flex-direction: column; align-items: stretch; gap: 5px; padding: 8px 4% 7px; }
            html body .pagebar > a { align-self: flex-start; }
            html body .pagebar .links { width: 100%; flex-wrap: nowrap; gap: 20px; overflow-x: auto; padding: 1px 0 3px; scrollbar-width: thin; overscroll-behavior-x: contain; }
            html body .pagebar .links a { flex: 0 0 auto; padding: 6px 0 5px; }

            .container { width: 90%; padding: 18px 0 38px; }
            .field-atlas-hero { grid-template-columns: minmax(0, 1fr) 96px; gap: 10px; margin: 0 4px 12px 0; padding: 15px 12px; box-shadow: 4px 4px 0 var(--atlas-yellow); }
            .atlas-kicker { padding: 5px 7px; font-size: .6rem; }
            .field-atlas-hero h2 { margin: 10px 0 6px; font-size: 1.8rem; }
            .field-atlas-hero p { font-size: .78rem; line-height: 1.42; }
            .atlas-stamp { padding: 9px; box-shadow: 3px 3px 0 var(--atlas-red); }
            .atlas-stamp strong { font-size: 1.55rem; }
            .atlas-stamp span { font-size: .62rem; }
            .atlas-stamp small { margin-top: 7px; padding-top: 6px; font-size: .64rem; }

            .atlas-index { flex-wrap: nowrap; overflow-x: auto; margin: 14px 0 12px; padding: 10px; scrollbar-width: thin; scroll-snap-type: x mandatory; overscroll-behavior-x: contain; }
            .atlas-index-label { flex: 0 0 100%; }
            .atlas-index a { flex: 0 0 auto; scroll-snap-align: start; font-size: .72rem; }
            .atlas-tools { align-items: stretch; flex-direction: column; gap: 6px; }
            .atlas-search-wrap { max-width: none; }
            .atlas-count { align-self: flex-end; font-size: .75rem; }
            .fields-grid { grid-template-columns: 1fr; gap: 10px; }
            .field-card { display: grid; grid-template-columns: 42px minmax(0, 1fr); column-gap: 11px; padding: 13px 13px 10px; }
            .field-card::before { width: 4px; height: auto; right: auto; bottom: 0; }
            .field-card > div:first-child { display: contents; }
            .field-card-top { grid-row: 1 / span 4; flex-direction: column; justify-content: flex-start; align-items: center; gap: 8px; }
            .field-card .icon-wrapper { width: 38px; height: 38px; margin: 0; font-size: 20px; }
            .field-number { font-size: .95rem; }
            .field-card .field-title { align-self: end; margin: 0 0 4px; font-size: 1.05rem; }
            .field-card .field-desc { margin: 0 0 8px; font-size: .79rem; }
            .field-card .unis-count { justify-self: start; font-size: .67rem; }
            .field-card .card-footer { grid-column: 2; margin-top: 9px; padding-top: 8px; font-size: .76rem; }
        }

        @media (max-width: 360px) {
            .container { width: 88%; }
            .field-atlas-hero { grid-template-columns: minmax(0, 1fr) 82px; padding: 13px 10px; }
            .field-atlas-hero h2 { font-size: 1.6rem; }
            .atlas-stamp { padding: 7px; }
            .atlas-stamp strong { font-size: 1.35rem; }
        }

        html[data-theme="dark"] body { background: #101a26 !important; color: #e5edf7 !important; }
        html[data-theme="dark"] body .field-atlas-hero { background: var(--yu-surface-soft) !important; background-image: none !important; border-color: var(--yu-border) !important; }
        html[data-theme="dark"] body .field-atlas-hero h2 { color: var(--yu-text) !important; }
        html[data-theme="dark"] body .field-atlas-hero p { color: var(--yu-muted) !important; }
        html[data-theme="dark"] body .atlas-stamp { background: var(--yu-surface) !important; color: var(--yu-text) !important; border-color: var(--yu-border) !important; }
        html[data-theme="dark"] body .atlas-stamp small { color: var(--yu-muted) !important; }
        html[data-theme="dark"] body .atlas-index { background: #142337 !important; border-color: #34445c !important; }
        html[data-theme="dark"] body .atlas-index a { background: var(--yu-surface-soft) !important; color: var(--yu-link) !important; border-color: var(--yu-border) !important; }
        html[data-theme="dark"] body .atlas-tools label { color: #9ed5b4 !important; }
        html[data-theme="dark"] body .atlas-search-wrap input { background: #0e1929 !important; color: #e5edf7 !important; border-color: #526179 !important; }
        html[data-theme="dark"] body .atlas-count { color: #a7b4c7 !important; }
        html[data-theme="dark"] body .field-card { background: #142337 !important; border-color: #34445c !important; }
        html[data-theme="dark"] body .field-card .field-title { color: #e5edf7 !important; }
        html[data-theme="dark"] body .field-card .field-desc { color: #b8c5d8 !important; }
        html[data-theme="dark"] body .field-card .card-footer { border-color: #34445c !important; }
        html[data-theme="dark"] body .atlas-empty { background: #142337 !important; color: #d0dae8 !important; border-color: #526179 !important; }
        @media (prefers-reduced-motion: reduce) { .field-card, .field-card .icon-wrapper, .card-footer span.arrow { transition: none !important; } }
    </style>
</head>
<body>

    <nav class="pagebar">
        <a href="index.php">
            <div class="homepage">
                <div class="logo">🎓</div>
                <div class="title">
                    <h1>Your<span>Uni</span></h1>
                    <p>Explore Myanmar's Universities</p>
                </div>
            </div>
        </a>
        
        <div class="links">
            <a href="index.php">Home</a>
            <a href="Universities.php">Universities</a>
            <a href="opportunities.php" class="active">Opportunities</a>
            <a href="About.html">About Us</a>
        </div>
    </nav>

    <main class="container">
        <header class="field-atlas-hero">
            <div>
                <span class="atlas-kicker">YourUni field atlas · discover your direction</span>
                <h2>Many paths. Find yours.</h2>
                <p>Browse fields across Myanmar, see example campuses, and follow the subjects that catch your eye. Start anywhere; your shortlist can take shape as you explore.</p>
            </div>
            <aside class="atlas-stamp" aria-label="Published fields">
                <strong><?= count($opportunities) ?></strong>
                <span>paths to explore</span>
                <small>From engineering workshops to arts, health, education, and beyond.</small>
            </aside>
        </header>

        <?php if ($opportunities): ?>
            <nav class="atlas-index" aria-label="Jump to a field">
                <span class="atlas-index-label">Field index · jump straight in</span>
                <?php foreach ($opportunities as $index => $opportunity): ?>
                    <a href="#field-<?= htmlspecialchars(youruniMakeSlug($opportunity['title']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?> · <?= htmlspecialchars($opportunity['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="atlas-tools">
                <div class="atlas-search-wrap">
                    <label class="yu-sr-only" for="field-search">Search fields</label>
                    <input id="field-search" type="search" placeholder="Search a field, subject, or campus..." autocomplete="off">
                </div>
                <span class="atlas-count" id="field-count" aria-live="polite"><?= count($opportunities) ?> fields on the board</span>
            </div>
        <?php endif; ?>

        <section class="fields-grid" aria-label="Study fields and career pathways">
            <?php if ($opportunityError !== ''): ?>
                <div class="data-message">Opportunities could not be loaded. Please open the admin dashboard and check the database connection.</div>
            <?php elseif (!$opportunities): ?>
                <div class="data-message">There are no published opportunities to display right now.</div>
            <?php else: ?>
                <?php foreach ($opportunities as $index => $opportunity): ?>
                    <a id="field-<?= htmlspecialchars(youruniMakeSlug($opportunity['title']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" href="<?= htmlspecialchars($opportunity['page_link'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="field-card <?= htmlspecialchars($opportunity['css_class'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <div>
                            <div class="field-card-top">
                                <div class="icon-wrapper"><?= htmlspecialchars($opportunity['icon'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                                <span class="field-number"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
                            </div>
                            <div class="field-title"><?= htmlspecialchars($opportunity['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                            <p class="field-desc"><?= nl2br(htmlspecialchars($opportunity['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?></p>
                            <span class="unis-count"><?= htmlspecialchars($opportunity['university_examples'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        </div>
                        <div class="card-footer">
                            <span>Explore Careers</span>
                            <span class="arrow">➔</span>
                        </div>
                    </a>
                <?php endforeach; ?>
                <div class="atlas-empty" id="field-empty">No field cards match that search. Try another subject or campus name.</div>
            <?php endif; ?>
        </section>
    </main>

    <script src="assets/youruni-global.js" defer></script>
    <script>
        const fieldSearch = document.getElementById('field-search');
        if (fieldSearch) {
            const fieldCards = Array.from(document.querySelectorAll('.field-card'));
            const fieldCount = document.getElementById('field-count');
            const emptyMessage = document.getElementById('field-empty');
            fieldSearch.addEventListener('input', () => {
                const query = fieldSearch.value.trim().toLocaleLowerCase();
                let visibleCount = 0;
                fieldCards.forEach(card => {
                    const matches = card.textContent.toLocaleLowerCase().includes(query);
                    card.hidden = !matches;
                    if (matches) visibleCount += 1;
                });
                if (document.documentElement.lang === 'my') {
                    fieldCount.textContent = `${visibleCount} ${visibleCount === 1 ? 'ပညာရပ်နယ်ပယ်' : 'ပညာရပ်နယ်ပယ်များ'}`;
                } else {
                    fieldCount.textContent = `${visibleCount} ${visibleCount === 1 ? 'field' : 'fields'} on the board`;
                }
                emptyMessage.classList.toggle('is-visible', visibleCount === 0);
            });
        }
    </script>
</body>
</html>