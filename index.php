<?php
require 'db.php'; // Connect to the database

$query = "SELECT uni_name, major, region, image_path, page FROM uni ORDER BY popularity ASC, uni_name ASC LIMIT 18";
$result = mysqli_query($conn, $query);
$universities = [];
$featuredRows = [];
$homepageCards = [];
$featuredNames = [];
$universityCount = 0;

if ($result instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $universities[] = $row;
    }
}

$featuredResult = mysqli_query($conn, 'SELECT title, university_name, major, zone, min_marks, image_url, page_link FROM courses ORDER BY id ASC LIMIT 18');
if ($featuredResult instanceof mysqli_result) {
    while ($row = mysqli_fetch_assoc($featuredResult)) {
        $nameKey = strtolower(trim((string)$row['university_name']));
        if ($nameKey === '' || isset($featuredNames[$nameKey])) {
            continue;
        }
        $featuredNames[$nameKey] = true;
        $homepageCards[] = [
            'name' => (string)$row['university_name'],
            'major' => (string)$row['major'],
            'region' => (string)$row['zone'],
            'image' => (string)($row['image_url'] ?: 'photos/default.jpg'),
            'page' => (string)($row['page_link'] ?: '#'),
            'featured_title' => (string)$row['title'],
            'min_marks' => (int)$row['min_marks'],
            'is_featured' => true,
        ];
    }
}

foreach ($universities as $row) {
    if (count($homepageCards) >= 18) {
        break;
    }
    $nameKey = strtolower(trim((string)$row['uni_name']));
    if (isset($featuredNames[$nameKey])) {
        continue;
    }
    $homepageCards[] = [
        'name' => (string)$row['uni_name'],
        'major' => (string)$row['major'],
        'region' => (string)$row['region'],
        'image' => (string)$row['image_path'],
        'page' => (string)$row['page'],
        'featured_title' => '',
        'min_marks' => null,
        'is_featured' => false,
    ];
}

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM uni");
if ($countResult instanceof mysqli_result) {
    $countRow = mysqli_fetch_assoc($countResult);
    $universityCount = (int)($countRow['total'] ?? 0);
}

$regions = [];
$regionResult = mysqli_query($conn, "SELECT DISTINCT region FROM uni WHERE region <> '' ORDER BY region ASC");
if ($regionResult instanceof mysqli_result) {
    $regionLabels = ['Ayeyarwaday' => 'Ayeyarwady', 'Kayar' => 'Kayah'];
    while ($regionRow = mysqli_fetch_assoc($regionResult)) {
        $region = $regionLabels[$regionRow['region']] ?? $regionRow['region'];
        if (!in_array($region, $regions, true)) {
            $regions[] = $region;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourUni - Discover Top Universities</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #121c2d;
            background-color: #f8fafc;
            line-height: 1.5;
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

        .background {
            position: relative;
            display: flex;
            padding: 50px 5% 70px;
            min-height: 75vh;
            align-items: center;
            overflow: hidden;
        }

        .hero-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
        }

        .background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, #ffffff 55%, rgba(255,255,255,0.2) 80%, transparent 100%);
            z-index: 1;
        }

        .hero-accent {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none;
        }

        .summary {
            width: 55%;
            min-width: 280px;
            padding-right: 60px;
            position: relative;
            z-index: 3;
        }

        .background h2 {
            font-size: 3.5rem;
            line-height: 1.1;
            margin-bottom: 20px;
            color: #121c2d;
        }

        .background h2 span {
            color: #0b5ee7;
        }
        
        .background p span {
            font-weight: bold;
            color: #0b5ee7;
        }

        .background > p {
            font-size: 1.05rem;
            color: #5a6474;
            margin-bottom: 40px;
            max-width: 90%;
            line-height: 1.6;
        }

        .featured {
            padding: 60px 5% 40px;
            background-color: #ffffff;
        }

        .unilink {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .unilink h3 {
            font-size: 1.6rem;
            color: #121c2d;
        }

        .unilink a {
            color: #0b5ee7;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .unilist {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .uni-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .uni-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
        }

        .card-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .card-content {
            padding: 18px;
            display: flex;
            gap: 12px;
        }

        .uni-logo {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            object-fit: contain;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            flex-shrink: 0;
        }

        .uni-info h5 {
            font-size: 1rem;
            margin-bottom: 4px;
            color: #121c2d;
        }

        .uni-info p {
            font-size: 0.85rem;
            color: #5a6474;
            margin-bottom: 12px;
        }

        .tag {
            display: inline-block;
            background-color: #eff6ff;
            color: #0b5ee7;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .why-choose-section {
            padding: 40px 5% 80px;
            background-color: #ffffff;
        }

        .why-choose-section h3 {
            text-align: center;
            font-size: 1.6rem;
            margin-bottom: 50px;
            color: #121c2d;
        }

        .features-grid {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            flex: 1 1 200px;
        }

        #speed {
            color: #3b82f6;
        }
        
        .feature-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #ffffff;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .icon-blue { background-color: #2563eb; }
        .icon-green { background-color: #10b981; }
        .icon-purple { background-color: #8b5cf6; }
        .icon-orange { background-color: #f59e0b; }

        .feature-text h4 {
            font-size: 1.05rem;
            margin-bottom: 6px;
            color: #121c2d;
        }

        .feature-text p {
            font-size: 0.85rem;
            color: #5a6474;
            line-height: 1.5;
        }

        .hero-search {
            display: flex;
            background: #ffffff;
            padding: 6px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            margin-top: 30px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .hero-search-group {
            flex: 1;
            min-width: 130px;
            display: flex;
            flex-direction: column;
            padding: 8px 16px;
            border-right: 1px solid #e2e8f0;
            overflow: hidden;
        }
        
        .hero-search-group:last-of-type { border-right: none; }
        
        .hero-search-group label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #5a6474;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        
        .hero-search-group select, .hero-search-group input {
            border: none;
            outline: none;
            font-size: 0.95rem;
            font-weight: 500;
            color: #121c2d;
            background: transparent;
            width: 100%;
            cursor: pointer;
            text-overflow: ellipsis;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .hero-search-group input { cursor: text; }
        
        .hero-search-btn {
            background-color: #0b5ee7;
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s;
            margin-left: 8px;
            white-space: nowrap;
        }
        
        .hero-search-btn:hover { background-color: #084bb8; }
        
        @media (max-width: 768px) {
            .hero-search { flex-direction: column; align-items: stretch; padding: 12px; }
            .hero-search-group { border-right: none; border-bottom: 1px solid #e2e8f0; padding: 10px 0; }
            .hero-search-group:last-of-type { border-bottom: none; }
            .hero-search-btn { padding: 12px; margin-top: 12px; margin-left: 0; width: 100%; }
        }

        :root {
            --portal-ink: #20201d;
            --portal-red: #d9432e;
            --portal-yellow: #f2c84b;
            --portal-green: #317b58;
            --portal-blue: #2874a5;
            --portal-paper: #fffaf0;
            --portal-line: #ded5c3;
        }

        body {
            background: var(--portal-paper);
            color: var(--portal-ink);
            font-family: 'Plus Jakarta Sans', 'Trebuchet MS', sans-serif;
        }

        .pagebar {
            background: #211f1a;
            padding: 9px 4%;
            border-bottom: 5px solid var(--portal-yellow);
        }

        .logo { color: var(--portal-yellow); }
        .title h1 span { color: var(--portal-yellow); }
        .title p { color: #e5dfd1; }
        .links { gap: 20px; }
        .links a { font-weight: 700; }

        .background {
            min-height: 0;
            padding: 34px 4% 32px;
            background-color: #e9f1e7;
            background-image: radial-gradient(#c6d8c6 0.8px, transparent 0.8px);
            background-size: 15px 15px;
            border-bottom: 1px solid #cbd9c8;
        }

        .background::before,
        .hero-accent { display: none; }

        .home-hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(310px, .85fr);
            gap: 28px;
            width: min(1320px, 100%);
            margin: 0 auto;
            align-items: stretch;
        }

        .summary {
            width: auto;
            min-width: 0;
            padding: 17px 0 8px;
        }

        .hero-kicker, .board-label, .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .75rem;
            font-weight: 900;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .hero-kicker {
            background: var(--portal-yellow);
            padding: 7px 11px;
            transform: rotate(-1deg);
        }

        .background h2 {
            max-width: 700px;
            margin: 15px 0 12px;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 4rem;
            line-height: .98;
            color: var(--portal-ink);
        }

        .background h2 span { color: var(--portal-red); }
        .background p { color: #45483f; }
        .summary > p { max-width: 630px; margin: 0; font-size: 1rem; }
        #speed { color: var(--portal-green); }

        .hero-search {
            max-width: 820px;
            margin-top: 24px;
            padding: 10px;
            border: 2px solid var(--portal-ink);
            border-radius: 4px;
            box-shadow: 5px 5px 0 var(--portal-yellow);
        }

        .hero-search-group { min-width: 120px; }
        .hero-search-group label { color: #665c49; }
        .hero-search-btn { background: var(--portal-red); border-radius: 3px; }
        .hero-search-btn:hover { background: #b93122; }

        .hero-board {
            align-self: center;
            padding: 15px;
            background: #fffdf7;
            border: 2px solid var(--portal-ink);
            box-shadow: 7px 7px 0 var(--portal-red);
            transform: rotate(1deg);
        }

        .hero-board-head, .section-heading-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .hero-board-head { margin: 0 0 11px; }
        .hero-board-head strong { font-family: Georgia, serif; font-size: 1.2rem; }
        .hero-board-head a { color: var(--portal-red); font-size: .84rem; font-weight: 800; }

        .hero-campus-feature {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            min-height: 190px;
            color: #fff;
            background: var(--portal-green);
        }

        .hero-campus-feature img { width: 100%; height: 100%; min-height: 190px; object-fit: cover; }
        .hero-campus-copy { display: flex; flex-direction: column; justify-content: space-between; padding: 13px; }
        .hero-campus-copy small { color: #f8d768; font-weight: 900; text-transform: uppercase; }
        .hero-campus-copy h3 { margin: 8px 0; font: 700 1.15rem/1.15 Georgia, serif; }
        .hero-campus-copy p { margin: 0; color: #eff5e9; font-size: .82rem; }
        .hero-campus-copy a { margin-top: 12px; font-size: .82rem; font-weight: 800; color: #fff; }

        .hero-board-links { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; }
        .hero-board-links a {
            min-width: 0;
            padding: 8px 9px;
            border: 1px solid var(--portal-line);
            background: #fff9e8;
            font-size: .78rem;
            font-weight: 800;
            line-height: 1.25;
        }
        .hero-board-links a:hover { background: var(--portal-yellow); }

        .field-marquee {
            padding: 17px 4% 20px;
            background: var(--portal-red);
            color: #fff;
            border-bottom: 5px solid var(--portal-yellow);
        }

        .field-marquee-inner { width: min(1320px, 100%); margin: 0 auto; }
        .field-marquee-title { margin-bottom: 10px; font-size: .8rem; font-weight: 900; text-transform: uppercase; }
        .field-links { display: flex; flex-wrap: wrap; gap: 8px; }
        .field-links a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 11px;
            background: #fffaf0;
            color: var(--portal-ink);
            border: 1px solid rgba(32,32,29,.28);
            font-size: .83rem;
            font-weight: 800;
            transition: transform .15s, background .15s;
        }
        .field-links a:hover { transform: translateY(-2px) rotate(-1deg); background: var(--portal-yellow); }
        .field-links span { color: var(--portal-red); font-size: .72rem; }

        .featured { padding: 35px 4% 42px; background: #fffaf0; }
        .unilink { width: min(1320px, 100%); margin: 0 auto 17px; align-items: end; }
        .section-kicker { color: var(--portal-green); }
        .unilink h3 { font: 700 2rem/1.1 Georgia, serif; color: var(--portal-ink); }
        .unilink a { color: var(--portal-red); }
        .catalogue-note { color: #6a655b; font-size: .85rem; }

        .unilist {
            width: min(1320px, 100%);
            margin: 0 auto;
            grid-template-columns: repeat(auto-fill, minmax(205px, 1fr));
            gap: 13px;
        }

        .uni-card {
            border: 1px solid #d8d0c0;
            border-radius: 3px;
            box-shadow: 3px 3px 0 #e7dcc6;
            transition: transform .16s, box-shadow .16s;
        }
        .uni-card:hover { transform: translate(-2px, -3px); box-shadow: 6px 6px 0 var(--portal-yellow); }
        .uni-card > a { display: flex; flex-direction: column; height: 100%; }
        .card-img { height: 138px; background: #e6e2d7; }
        .card-content { flex: 1; padding: 12px; }
        .uni-info h5 { font: 700 1rem/1.25 Georgia, serif; }
        .uni-info p { margin: 0 0 9px; font-size: .78rem; }
        .tag { padding: 4px 8px; border-radius: 2px; background: #e5f0e5; color: #286044; }

        .portal-bottom {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 15px;
            width: min(1320px, 92%);
            margin: 0 auto 38px;
        }

        .portal-panel {
            padding: 18px;
            border: 2px solid var(--portal-ink);
            background: #f6e8bd;
        }
        .portal-panel:nth-child(2) { background: #dcebe1; }
        .portal-panel h3 { margin-bottom: 12px; font: 700 1.35rem Georgia, serif; }
        .region-list, .more-links { display: flex; flex-wrap: wrap; gap: 7px; }
        .region-list a, .more-links a {
            padding: 7px 10px;
            border: 1px solid rgba(32,32,29,.32);
            background: #fffdf7;
            font-size: .8rem;
            font-weight: 800;
        }
        .region-list a:hover, .more-links a:hover { background: var(--portal-yellow); }

        .why-choose-section { padding: 25px 4% 45px; background: #fff; }
        .why-choose-section h3 { margin-bottom: 22px; font: 700 1.8rem Georgia, serif; }
        .feature-item { padding: 12px; border-top: 2px solid var(--portal-yellow); }
        .feature-icon { border-radius: 3px; }
        .icon-blue { background: var(--portal-blue); }
        .icon-green { background: var(--portal-green); }
        .icon-purple { background: var(--portal-red); }
        .icon-orange { background: #d79222; }

        @media (max-width: 900px) {
            .home-hero-grid { grid-template-columns: 1fr; }
            .summary { padding: 5px 0 0; }
            .hero-board { width: min(680px, calc(100% - 8px)); margin: 0 0 7px 3px; }
            .portal-bottom { grid-template-columns: 1fr; }
        }

        @media (max-width: 600px) {
            .background { padding: 25px 5% 28px; }
            .background h2 { font-size: 2.55rem; }
            .hero-board { transform: none; }
            .hero-campus-feature { grid-template-columns: 1fr; }
            .hero-campus-feature img { height: 155px; min-height: 155px; }
            .hero-campus-copy { min-height: 140px; }
            .featured { padding: 28px 5% 34px; }
            .unilink { align-items: flex-start; }
            .unilink h3 { font-size: 1.65rem; }
            .unilist { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
            .card-img { height: 105px; }
            .card-content { padding: 9px; }
            .uni-info h5 { font-size: .9rem; }
            .portal-bottom { width: 90%; }
            .field-marquee { padding-left: 5%; padding-right: 5%; }
        }

        @media (max-width: 380px) {
            .unilist { grid-template-columns: 1fr; }
        }

        html[data-theme="dark"] body .field-marquee {
            background: var(--portal-red) !important;
            color: #ffffff !important;
        }

        html[data-theme="dark"] body .field-links a {
            background: var(--yu-surface-soft) !important;
            color: var(--yu-text) !important;
            border-color: var(--yu-border) !important;
        }

        html[data-theme="dark"] body .hero-board {
            background: var(--yu-surface) !important;
            color: var(--yu-text) !important;
            border-color: var(--yu-border) !important;
        }

        html[data-theme="dark"] body .hero-board-head strong { color: var(--yu-text) !important; }
        html[data-theme="dark"] body .hero-board-head a { color: var(--yu-link) !important; }
        html[data-theme="dark"] body .hero-campus-copy h3,
        html[data-theme="dark"] body .hero-campus-copy a { color: #ffffff !important; }
        html[data-theme="dark"] body .hero-campus-copy small { color: #f8d768 !important; }
        html[data-theme="dark"] body .hero-campus-copy p { color: #eff5e9 !important; }
        html[data-theme="dark"] body .hero-board-links a {
            background: var(--yu-surface-soft) !important;
            color: var(--yu-text) !important;
            border-color: var(--yu-border) !important;
        }

        html[data-theme="dark"] body .portal-panel {
            background: #142337 !important;
            color: #e5edf7 !important;
            border-color: #76836f !important;
        }

        html[data-theme="dark"] body .portal-panel h3 { color: #e5edf7 !important; }
        html[data-theme="dark"] body .portal-panel a {
            background: #20344c !important;
            color: #e5edf7 !important;
            border-color: #53637a !important;
        }

        @media (max-width: 760px) {
            html body .pagebar {
                flex-direction: column;
                align-items: stretch;
                min-height: 0;
                padding: 8px 4% 7px;
                gap: 5px;
            }

            html body .pagebar > a { align-self: flex-start; }
            html body .pagebar .links {
                width: 100%;
                gap: 20px;
                flex-wrap: nowrap;
                overflow-x: auto;
                padding: 1px 0 3px;
                scrollbar-width: thin;
                overscroll-behavior-x: contain;
            }

            html body .pagebar .links a { flex: 0 0 auto; padding: 6px 0 5px; }
            html body .pagebar .links a:last-child { margin-right: 8px; }
        }

        @media (max-width: 600px) {
            .background { padding: 18px 5% 20px; }
            .home-hero-grid { gap: 17px; }
            .summary { padding: 3px 0 0; }
            .hero-kicker { padding: 6px 8px; font-size: .68rem; }
            .background h2 { margin: 12px 0 9px; font-size: 2.3rem; line-height: 1.02; }
            .summary > p { font-size: .9rem; line-height: 1.45; }

            .hero-search {
                display: grid;
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                gap: 2px 12px;
                margin-top: 15px;
                padding: 9px;
            }

            .hero-search-group {
                min-width: 0;
                padding: 7px 3px;
                border-right: 0;
                border-bottom: 1px solid #e2d9c8;
            }

            .hero-search-group:nth-child(2) { border-left: 1px solid #e2d9c8; padding-left: 10px; }
            .hero-search-group:nth-child(3) { border-bottom: 0; }
            .hero-search-group label { font-size: .65rem; }
            .hero-search-group select, .hero-search-group input { font-size: .82rem; }
            .hero-search-group:nth-child(3) input {
                min-height: 34px;
                padding: 5px 7px;
                border: 1px solid #d8cfbe;
                border-radius: 2px;
                background: #fff;
            }
            .hero-search-btn { align-self: center; min-height: 42px; margin: 0; padding: 9px 10px; font-size: .85rem; }

            body .background .summary .donate-home-cta {
                display: grid;
                grid-template-columns: minmax(0, 1fr) max-content;
                align-items: center;
                gap: 10px;
                margin-top: 14px;
                padding: 10px 11px;
                border-radius: 3px;
            }

            body .background .summary .donate-home-copy { min-width: 0; }
            body .background .summary .donate-home-copy strong { font-size: .82rem; line-height: 1.3; }
            body .background .summary .donate-home-copy p { display: none; }
            body .background .summary .donate-home-eyebrow { margin-bottom: 3px; font-size: .58rem; }
            body .background .summary .donate-home-btn {
                width: auto;
                min-height: 36px;
                gap: 5px;
                padding: 7px 9px;
                border-radius: 3px;
                font-size: .75rem;
            }

            .hero-board {
                width: calc(100% - 6px);
                margin: 0 0 6px 2px;
                padding: 10px;
                box-shadow: 5px 5px 0 var(--portal-red);
            }

            .hero-board-head { margin-bottom: 8px; }
            .hero-board-head strong { font-size: 1.05rem; }
            .hero-board-head a { font-size: .75rem; }
            .hero-campus-feature { grid-template-columns: minmax(105px, .75fr) minmax(0, 1fr); min-height: 145px; }
            .hero-campus-feature img { width: 100%; height: 100%; min-height: 145px; object-fit: cover; }
            .hero-campus-copy { min-height: 145px; padding: 10px; }
            .hero-campus-copy small { font-size: .62rem; }
            .hero-campus-copy h3 { margin: 6px 0; font-size: 1rem; }
            .hero-campus-copy p, .hero-campus-copy a { font-size: .72rem; }
            .hero-board-links { gap: 6px; margin-top: 8px; }
            .hero-board-links a { padding: 7px; font-size: .68rem; }

            .field-marquee { padding-top: 12px; padding-bottom: 12px; }
            .field-marquee-title { margin-bottom: 7px; font-size: .7rem; }
            .field-links {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding: 2px 3px 8px;
                scroll-snap-type: x mandatory;
                scrollbar-width: thin;
                overscroll-behavior-x: contain;
            }

            .field-links a { flex: 0 0 auto; scroll-snap-align: start; padding: 7px 9px; font-size: .76rem; }
            .field-links a:last-child { margin-right: 6px; }
            .featured { padding-top: 24px; }
            .unilink { gap: 8px; }
            .unilink h3 { font-size: 1.5rem; }
            .unilink a { font-size: .8rem; }
            .section-kicker { font-size: .65rem; }
            .uni-info h5 { overflow-wrap: anywhere; }
        }

        @media (max-width: 380px) {
            .background h2 { font-size: 2.05rem; }
            .hero-campus-feature { grid-template-columns: 102px minmax(0, 1fr); }
            .hero-board-links a { font-size: .64rem; }
            .unilink h3 { font-size: 1.35rem; }
            .unilink a { max-width: 105px; text-align: right; }
        }

        @media (prefers-reduced-motion: reduce) {
            .field-links a { transition: none; }
        }
    </style>
    <link rel="stylesheet" href="assets/youruni-global.css">
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
            <a href="index.php" class="active">Home</a>
            <a href="Universities.php">Universities</a>
            <a href="opportunities.php">Opportunities</a>
            <a href="About.html">About Us</a>
        </div>
    </nav>

    <dialog class="yu-site-notice" id="yu-site-notice" aria-labelledby="yu-site-notice-title" aria-describedby="yu-site-notice-copy">
        <div class="yu-site-notice-mark" aria-hidden="true">!</div>
        <p class="yu-site-notice-kicker" data-i18n="notice.kicker">A note from the creator</p>
        <h2 id="yu-site-notice-title" data-i18n="notice.title">Help us keep YourUni accurate</h2>
        <p id="yu-site-notice-copy" data-i18n="notice.copy">This website was created by a student at an early stage of their studies, so some information may be inaccurate. Please use the contact details on the About Us page to report errors so they can be corrected. You may also suggest additional universities for inclusion. Donations are welcome and will be used to maintain and improve the website.</p>
        <button class="yu-site-notice-dismiss" id="yu-site-notice-dismiss" type="button" data-i18n="notice.dismiss">Continue to YourUni</button>
    </dialog>

    <section class="background">
        <div class="home-hero-grid">
        <div class="summary">
            <span class="hero-kicker">Your campus hunt starts here · Myanmar</span>
            <h2>Explore <span>Myanmar's</span> Universities<br><span>Choose</span> Your<span id="speed">Uni</span></h2>
            <p>Big choices, all in one place. Browse campuses by field and region, check your options, and keep a few possibilities open.</p>
            
            <form action="Universities.php" method="POST" class="hero-search">
                <div class="hero-search-group">
                    <label>Major</label>
                    <select name="major">
                        <option value="">Any Major</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Medicine">Medicine</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Arts & Science">Arts & Science</option>
                        <option value="Economics">Economics</option>
                        <option value="Foreign Languages">Foreign Languages</option>
                    </select>
                </div>

                <div class="hero-search-group">
                    <label>Region</label>
                    <select name="zone">
                        <option value="">Any Region</option>
                        <option value="Kachin">Kachin</option>
                        <option value="Kayah">Kayah</option>
                        <option value="Kayin">Kayin</option>
                        <option value="Chin">Chin</option>
                        <option value="Rakhine">Rakhine</option>
                        <option value="Yangon">Yangon</option>
                        <option value="Mandalay">Mandalay</option>
                        <option value="Naypyitaw">Naypyitaw</option>
                        <option value="Magway">Magway</option>
                        <option value="Sagaing">Sagaing</option>
                        <option value="Shan">Shan</option>
                        <option value="Ayeyarwady">Ayeyarwady</option>
                        <option value="Bago">Bago</option>
                        <option value="Mon">Mon</option>
                        <option value="Tanintharyi">Tanintharyi</option>
                    </select>
                </div>

                <div class="hero-search-group">
                    <label>Marks</label>
                    <input type="number" name="marks" min="240" max="600" required>
                </div>

                <button type="submit" class="hero-search-btn">Search</button>
            </form>

            <section class="donate-home-cta" aria-label="Donate to YourUni">
                <div class="donate-home-copy">
                    <span class="donate-home-eyebrow" data-i18n="donate.homeEyebrow">SUPPORTING YOURUNI</span>
                    <strong data-i18n="donate.homeTitle">Help keep YourUni available to students.</strong>
                    <p data-i18n="donate.homeCopy">If YourUni helps you explore your options, an optional contribution can help with website hosting and improving university information.</p>
                </div>
                <a class="donate-home-btn" href="donate.php">
                    <span aria-hidden="true">♥</span>
                    <span data-i18n="donate.cta">Donate Us</span>
                    <span aria-hidden="true">→</span>
                </a>
            </section>
        </div>

        <aside class="hero-board" aria-label="University spotlight">
            <div class="hero-board-head">
                <strong>On the campus map</strong>
                <a href="Universities.php">See all <?= number_format($universityCount) ?> &rarr;</a>
            </div>
            <?php if (!empty($universities)): ?>
                <?php $spotlight = $universities[0]; ?>
                <div class="hero-campus-feature">
                    <img src="<?= htmlspecialchars($spotlight['image_path']) ?>" alt="<?= htmlspecialchars($spotlight['uni_name']) ?> campus" loading="eager">
                    <div class="hero-campus-copy">
                        <div>
                            <small>Campus pick · <?= htmlspecialchars($spotlight['region']) ?></small>
                            <h3><?= htmlspecialchars($spotlight['uni_name']) ?></h3>
                            <p><?= htmlspecialchars($spotlight['major']) ?></p>
                        </div>
                        <a href="<?= htmlspecialchars($spotlight['page']) ?>">Take a closer look &rarr;</a>
                    </div>
                </div>
                <div class="hero-board-links">
                    <?php foreach (array_slice($universities, 1, 4) as $pick): ?>
                        <a href="<?= htmlspecialchars($pick['page']) ?>"><?= htmlspecialchars($pick['uni_name']) ?> <span>· <?= htmlspecialchars($pick['region']) ?></span></a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>University listings are being updated. Visit the full directory to explore.</p>
            <?php endif; ?>
        </aside>
        </div>

        <!-- Blue Corner Triangle/Swoosh -->
        <svg class="hero-accent" viewBox="0 0 1000 400" preserveAspectRatio="none">
            <path d="M 550,400 C 750,395 880,340 1000,260 L 1000,400 Z" fill="#0b5ee7" />
        </svg>
    </section>

    <section class="field-marquee" id="study-fields" aria-label="Browse study fields">
        <div class="field-marquee-inner">
            <div class="field-marquee-title">Pick a direction, then wander around</div>
            <div class="field-links">
                <a href="Engineering.html"><span>01</span> Engineering</a>
                <a href="Computerscience.html"><span>02</span> Computer Science</a>
                <a href="medicine.html"><span>03</span> Medicine & Health</a>
                <a href="arts-science.html"><span>04</span> Arts & Science</a>
                <a href="business.html"><span>05</span> Business</a>
                <a href="language.html"><span>06</span> Languages</a>
                <a href="agriculture.html"><span>07</span> Agriculture</a>
                <a href="education.html"><span>08</span> Education</a>
                <a href="law.html"><span>09</span> Law</a>
                <a href="defense.html"><span>10</span> Defense</a>
            </div>
        </div>
    </section>
   
    <section class="featured">
        <div class="unilink">
            <div>
                <span class="section-kicker">Across the country · <?= number_format($universityCount) ?> listings</span>
                <h3>Universities to explore</h3>
            </div>
            <a href="Universities.php">View All Universities &rarr; </a>
        </div>
        <div class="unilist">
            <?php foreach ($homepageCards as $row): ?>
                <article class="uni-card">
                    <a href="<?= htmlspecialchars($row['page']) ?>">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="card-img" loading="lazy">
                        <div class="card-content">
                            <div class="uni-info">
                                <?php if ($row['is_featured']): ?><span class="featured-pick-label"><?= htmlspecialchars($row['featured_title']) ?></span><?php endif; ?>
                                <h5><?= htmlspecialchars($row['name']) ?></h5>
                                <p><?= htmlspecialchars($row['region']) ?><?php if ($row['is_featured'] && $row['min_marks'] > 0): ?> · <?= (int)$row['min_marks'] ?> marks<?php endif; ?></p>
                                <span class="tag"><?= htmlspecialchars($row['major']) ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
            <?php if (empty($universities)): ?>
                <p class="catalogue-note">No university listings are available right now. Open the full directory to try again.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="portal-bottom" aria-label="More ways to explore">
        <div class="portal-panel">
            <h3>Find a place that feels right</h3>
            <div class="region-list">
                <?php foreach ($regions as $region): ?>
                    <a href="Universities.php?region=<?= rawurlencode($region) ?>"><?= htmlspecialchars($region) ?></a>
                <?php endforeach; ?>
                <a href="Universities.php">All regions &rarr;</a>
            </div>
        </div>
        <div class="portal-panel">
            <h3>More corners of YourUni</h3>
            <div class="more-links">
                <a href="compare.php">Compare universities</a>
                <a href="opportunities.php">Scholarships & opportunities</a>
                <a href="#study-fields">Study fields & majors</a>
                <a href="donate.php">Support YourUni</a>
            </div>
        </div>
    </section>

    <section class="why-choose-section">
        <h3>Why <span>YourUni</span> is a good choice</h3>
        
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon icon-blue">🧭</div>
                <div class="feature-text">
                    <h4>Expert Guidance</h4>
                    <p>Get advice from education experts and mentors.</p>
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon icon-green">📄</div>
                <div class="feature-text">
                    <h4>Comprehensive Information</h4>
                    <p>Access detailed information about universities and courses.</p>
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon icon-purple">🎓</div>
                <div class="feature-text">
                    <h4>Job Opportunities</h4>
                    <p>Find and apply for Job Opportunities</p>
                </div>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon icon-orange">✈️</div>
                <div class="feature-text">
                    <h4>Accurate Locations</h4>
                    <p>Precise locations of Universities</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="yu-site-footer"><span><strong>YourUni</strong> · Explore Myanmar's Universities</span><span><a href="Universities.php">Explore universities</a> · <a href="About.html">About Us</a> · <a href="donate.php" data-i18n="donate.cta">Donate Us</a></span></footer>
    <script src="assets/youruni-global.js" defer></script>
</body>
</html>