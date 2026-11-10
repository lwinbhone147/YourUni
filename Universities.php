<?php
require_once __DIR__ . '/config.php';
$pdo = null;
$matches = [];
$mainMatches = [];
$generalMatches = [];
$otherMatches = [];
$unknownMatches = [];
$smartSearch = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$userDegree = '';
$userRegion = '';
$userMarks = null;
$searchError = '';
$searchName = '';
$searchRegion = '';
$searchMajor = '';
$regions = [];
$majors = [];
$catalogueTotal = 0;

function youruniMajorMatches(string $storedMajor, string $selectedMajor): bool
{
    if ($selectedMajor === '') return true;
    $stored = mb_strtolower($storedMajor, 'UTF-8');
    $selected = mb_strtolower($selectedMajor, 'UTF-8');
    $aliases = [
        'engineering' => '/engineering|technolog|polytechnic/i',
        'computer science' => '/computer science|information technology|\bit\b|computing/i',
        'medicine' => '/medicine|medical|health|nurs|pharmacy|dental/i',
        'arts & science' => '/arts|science|law|general/i',
        'economics' => '/economics|business|management|account/i',
        'foreign languages' => '/foreign language|language|translation|linguistic/i',
    ];
    $pattern = $aliases[$selected] ?? null;
    if ($pattern !== null) return (bool)preg_match($pattern, $stored);
    return mb_stripos($stored, $selected, 0, 'UTF-8') !== false;
}

function youruniIsGeneralOption(array $row): bool
{
    $major = mb_strtolower((string)($row['major'] ?? ''), 'UTF-8');
    // A stored 0 can mean a pass-based option OR that the mark threshold hasn't
    // been entered. The UI therefore asks users to verify current requirements.
    return (bool)preg_match('/arts|science|law|general|liberal/i', $major)
        || (string)($row['admission_status'] ?? 'unknown') === 'pass_based';
}

function youruniRenderUniversityCards(array $rows, bool $showMarks = false): void
{
    foreach ($rows as $row) {
        $page = (string)($row['page'] ?? '#');
        $image = (string)($row['image_path'] ?? '');
        $name = (string)($row['uni_name'] ?? 'University');
        $major = (string)($row['major'] ?? '');
        $region = (string)($row['region'] ?? '');
        $marks = (int)($row['min_marks'] ?? 0);
        $admissionStatus = (string)($row['admission_status'] ?? 'unknown');
        echo '<div class="uni-card">';
        echo '<a href="' . htmlspecialchars($page, ENT_QUOTES, 'UTF-8') . '" style="display:flex;flex-direction:column;height:100%">';
        echo '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" class="card-img" loading="lazy">';
        echo '<div class="card-content"><div class="uni-info">';
        echo '<h5>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h5>';
        echo '<p>' . htmlspecialchars($region, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<span class="tag">' . htmlspecialchars($major, ENT_QUOTES, 'UTF-8') . '</span>';
        if ($showMarks) {
            if ($admissionStatus === 'known' && $marks > 0) echo '<span class="search-mark-note">Listed minimum marks: ' . $marks . ' (verify current requirements)</span>';
            elseif ($admissionStatus === 'pass_based') echo '<span class="search-mark-note">Recorded as pass-based — verify the official admission notice.</span>';
            else echo '<span class="search-mark-note">Admission requirements not verified.</span>';
        }
        echo '</div></div></a></div>';
    }
}

try {
    $pdo = new PDO(
        'mysql:host=' . YOURUNI_DB_HOST . ';port=' . YOURUNI_DB_PORT . ';dbname=' . YOURUNI_DB_NAME . ';charset=utf8mb4',
        YOURUNI_DB_USER,
        YOURUNI_DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]
    );
    $catalogueTotal = (int)$pdo->query('SELECT COUNT(*) FROM uni')->fetchColumn();
    $regions = $pdo->query("SELECT DISTINCT region FROM uni WHERE region <> '' ORDER BY region ASC")->fetchAll(PDO::FETCH_COLUMN);
    $regionLabels = ['Ayeyarwaday' => 'Ayeyarwady', 'Kayar' => 'Kayah'];
    $regions = array_values(array_unique(array_map(static fn($region) => $regionLabels[$region] ?? $region, $regions)));
    $majors = $pdo->query("SELECT major FROM uni WHERE major <> '' GROUP BY major ORDER BY COUNT(*) DESC, major ASC")->fetchAll(PDO::FETCH_COLUMN);

    if ($smartSearch) {
        $userDegree = trim((string)($_POST['major'] ?? ''));
        $userRegion = trim((string)($_POST['zone'] ?? ''));
        $userMarks = filter_var($_POST['marks'] ?? null, FILTER_VALIDATE_INT);
        if ($userMarks === false || $userMarks === null || $userMarks < 0 || $userMarks > 1000) {
            $searchError = 'Please enter a valid score between 0 and 1000.';
        } else {
            // Score and region are hard eligibility filters. Major is a preference:
            // matching programs are shown first, followed by general/other options.
            $regionSql = $userRegion !== '' ? ' AND region = :region' : '';
            $baseParams = ['marks' => $userMarks];
            if ($userRegion !== '') $baseParams['region'] = $userRegion;
            $stmt = $pdo->prepare("SELECT * FROM uni WHERE ((admission_status='known' AND min_marks <= :marks) OR admission_status='pass_based')" . $regionSql . ' ORDER BY popularity ASC, min_marks DESC, uni_name ASC');
            $stmt->execute($baseParams);
            $eligible = $stmt->fetchAll();
            foreach ($eligible as $row) {
                if (youruniMajorMatches((string)$row['major'], $userDegree) && (string)$row['admission_status'] !== 'pass_based') $mainMatches[] = $row;
                elseif (youruniIsGeneralOption($row) || (string)$row['admission_status'] === 'pass_based') $generalMatches[] = $row;
                else $otherMatches[] = $row;
            }
            // Unknown requirements are deliberately visible in their own group; unknown is not the same as pass-based.
            $unknownSql = "SELECT * FROM uni WHERE admission_status='unknown'" . $regionSql . ' ORDER BY popularity ASC, uni_name ASC';
            $unknownStmt = $pdo->prepare($unknownSql);
            $unknownParams = $userRegion !== '' ? ['region' => $userRegion] : [];
            $unknownStmt->execute($unknownParams);
            $unknownMatches = $unknownStmt->fetchAll();
        }
    } else {
        $searchName = trim((string)($_GET['search'] ?? ''));
        $searchRegion = trim((string)($_GET['region'] ?? ''));
        $searchMajor = trim((string)($_GET['major'] ?? ''));
        $conditions = [];
        $params = [];

        if ($searchName !== '') {
            $conditions[] = 'uni_name LIKE ?';
            $params[] = '%' . $searchName . '%';
        }
        if ($searchRegion !== '') {
            $regionAliases = [
                'Ayeyarwady' => ['Ayeyarwady', 'Ayeyarwaday'],
                'Kayah' => ['Kayah', 'Kayar'],
            ];
            $regionValues = $regionAliases[$searchRegion] ?? [$searchRegion];
            $conditions[] = 'region IN (' . implode(', ', array_fill(0, count($regionValues), '?')) . ')';
            array_push($params, ...$regionValues);
        }
        if ($searchMajor !== '') {
            $conditions[] = 'major = ?';
            $params[] = $searchMajor;
        }

        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
        $stmt = $pdo->prepare('SELECT * FROM uni' . $where . ' ORDER BY popularity ASC, uni_name ASC');
        $stmt->execute($params);
        $matches = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    error_log('YourUni university search: ' . $e->getMessage());
    $searchError = 'The university catalogue could not be reached. Check MySQL and import youruni_complete.sql as described in YOURUNI_README.txt.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourUni - University Discovery</title>
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

        nav { background-color: #121d2d; }

        .yy { color: #ffffff; }
        
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

        .featured-section {
            padding: 40px 5% 40px;
            background-color: #ffffff;
            min-height: 80vh; 
        }

        .name-search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        .name-search-form {
            display: flex;
            width: 100%;
            max-width: 600px;
            background: #ffffff;
            border-radius: 50px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .name-search-form input {
            flex: 1;
            padding: 14px 24px;
            border: none;
            outline: none;
            font-size: 1rem;
            color: #121c2d;
        }

        .name-search-form button {
            background-color: #0b5ee7;
            color: white;
            border: none;
            padding: 0 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: background 0.2s;
        }

        .name-search-form button:hover {
            background-color: #084bb8;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .section-header h3 {
            font-size: 1.6rem;
            color: #121c2d;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
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
            height: 170px;
            object-fit: cover;
        }

        .card-content {
            padding: 15px; 
            display: flex;
            gap: 12px;
            flex: 1;
        }

        .uni-info {
            display: flex;
            flex-direction: column;
            flex: 1;
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
            margin-top: auto;
            align-self: flex-start;
        }

        .yy span {
            color: #3b82f6;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 40px;
            color: #5a6474;
        }

        .result-group { margin: 28px 0 42px; }
        .result-group .section-header { margin-bottom: 10px; }
        .result-note { color:#64748b; font-size:.92rem; line-height:1.65; margin-bottom:18px; max-width:900px; }
        .result-count { color:#2563eb; font-weight:700; font-size:.85rem; }
        .search-summary { background:#eff6ff; border:1px solid #dbeafe; border-radius:14px; padding:14px 18px; margin-bottom:24px; color:#1e3a8a; }
        .search-mark-note { display:block; font-size:.75rem; color:#64748b; margin-top:8px; line-height:1.4; }
        .other-results { margin:28px 0; padding:18px; border:1px solid #e2e8f0; border-radius:16px; background:#f8fafc; }
        .other-results summary { cursor:pointer; font-weight:750; color:#1e293b; }
        .other-results > .result-note { margin-top:14px; }
        .other-results .cards-grid { margin-top:18px; }
        @media (max-width:640px) { .search-summary { font-size:.9rem; } .other-results { padding:14px; } }

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
            font-family: 'Plus Jakarta Sans', 'Trebuchet MS', sans-serif;
            color: var(--portal-ink);
            background: var(--portal-paper);
        }

        .featured-section {
            min-height: 0;
            padding: 0 4% 50px;
            background: var(--portal-paper);
        }

        .directory-banner {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 24px;
            margin: 0 -4vw 24px;
            padding: 32px 4vw 28px;
            background-color: #e9f1e7;
            background-image: radial-gradient(#c6d8c6 0.8px, transparent 0.8px);
            background-size: 15px 15px;
            border-bottom: 5px solid var(--portal-yellow);
        }

        .directory-kicker, .browse-title {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: .75rem;
            font-weight: 900;
            text-transform: uppercase;
        }

        .directory-kicker { padding: 6px 9px; background: var(--portal-yellow); transform: rotate(-1deg); }
        .directory-banner h2 { margin: 13px 0 7px; font: 700 2.7rem/1.03 Georgia, 'Times New Roman', serif; }
        .directory-banner p { max-width: 720px; margin: 0; color: #45483f; }

        .directory-stats {
            display: grid;
            grid-template-columns: auto auto;
            align-items: center;
            column-gap: 12px;
            min-width: 190px;
            padding: 12px 14px;
            background: #fffdf7;
            border: 2px solid var(--portal-ink);
            box-shadow: 4px 4px 0 var(--portal-red);
            transform: rotate(1deg);
        }
        .directory-stats strong { grid-row: span 2; font: 700 2.2rem/1 Georgia, serif; color: var(--portal-red); }
        .directory-stats span { font-size: .75rem; font-weight: 900; text-transform: uppercase; }
        .directory-stats a { grid-column: 1 / -1; margin-top: 9px; padding-top: 8px; border-top: 1px solid var(--portal-line); font-size: .8rem; font-weight: 900; color: var(--portal-green); }

        .directory-search-panel {
            width: min(1320px, 100%);
            margin: 0 auto 25px;
            padding: 17px;
            background: #fffdf7;
            border: 2px solid var(--portal-ink);
            box-shadow: 5px 5px 0 var(--portal-yellow);
        }

        .directory-search-heading { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; margin-bottom: 12px; }
        .directory-search-heading h3 { font: 700 1.35rem Georgia, serif; }
        .directory-search-heading p { color: #70685a; font-size: .82rem; }
        .directory-search-form { display: grid; grid-template-columns: minmax(180px, 1.5fr) repeat(2, minmax(150px, .8fr)) auto; gap: 8px; }
        .directory-search-form input, .directory-search-form select {
            width: 100%;
            min-width: 0;
            min-height: 46px;
            padding: 10px 12px;
            border: 1px solid #cfc5b2;
            border-radius: 2px;
            background: #fff;
            color: var(--portal-ink);
            font: inherit;
            font-size: .9rem;
        }
        .directory-search-form button {
            min-height: 46px;
            padding: 10px 18px;
            border: 0;
            border-radius: 2px;
            background: var(--portal-red);
            color: #fff;
            font: inherit;
            font-size: .9rem;
            font-weight: 900;
            cursor: pointer;
        }
        .directory-search-form button:hover { background: #b93122; }

        .browse-rail { margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--portal-line); }
        .browse-title { display: block; margin-bottom: 8px; color: var(--portal-green); }
        .browse-links { display: flex; flex-wrap: wrap; gap: 6px; }
        .browse-links a {
            display: inline-block;
            padding: 6px 9px;
            border: 1px solid #d8cfbe;
            background: #fff9e8;
            color: var(--portal-ink);
            font-size: .77rem;
            font-weight: 800;
            line-height: 1.25;
        }
        .browse-links a:hover, .browse-links a[aria-current="true"] { background: var(--portal-yellow); border-color: var(--portal-ink); }

        .directory-results-head {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 14px;
            width: min(1320px, 100%);
            margin: 28px auto 16px;
        }
        .directory-results-head h3 { margin-top: 4px; font: 700 2rem/1.1 Georgia, serif; color: var(--portal-ink); }
        .directory-results-head .browse-title { margin: 0; }
        .directory-results-count { color: #6a655b; font-size: .82rem; font-weight: 700; }
        .directory-results-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .directory-results-actions .yu-compare-link { border-radius: 2px; border-color: var(--portal-ink); color: var(--portal-red); }
        .directory-clear { color: var(--portal-green); font-size: .82rem; font-weight: 900; }

        .cards-grid {
            width: min(1320px, 100%);
            margin: 0 auto;
            grid-template-columns: repeat(auto-fill, minmax(205px, 1fr));
            gap: 13px;
        }
        .uni-card { border: 1px solid #d8d0c0; border-radius: 3px; box-shadow: 3px 3px 0 #e7dcc6; }
        .uni-card:hover { transform: translate(-2px, -3px); box-shadow: 6px 6px 0 var(--portal-yellow); }
        .card-img { height: 145px; background: #e6e2d7; }
        .card-content { padding: 12px; }
        .uni-info h5 { font: 700 1rem/1.25 Georgia, 'Times New Roman', serif; color: var(--portal-ink); }
        .uni-info p { margin: 0 0 9px; color: #6a655b; font-size: .78rem; }
        .tag { padding: 4px 8px; border-radius: 2px; background: #e5f0e5; color: #286044; }
        .empty-state { background: #fffdf7; border: 2px dashed #c9bda6; }
        .search-summary { background: #e9f1e7; border: 1px solid #c6d8c6; border-radius: 2px; color: #244e38; }
        .result-group { width: min(1320px, 100%); margin-left: auto; margin-right: auto; }
        .result-group .section-header h3 { font: 700 1.45rem Georgia, serif; }
        .result-count { color: var(--portal-red); }
        .other-results { width: min(1320px, 100%); margin-left: auto; margin-right: auto; border-radius: 2px; background: #f3ead4; }

        @media (max-width: 850px) {
            .directory-banner { align-items: flex-start; }
            .directory-search-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .directory-search-form input { grid-column: 1 / -1; }
            .directory-search-form button { grid-column: 1 / -1; }
        }

        @media (max-width: 600px) {
            .featured-section { padding: 0 5% 38px; }
            .directory-banner { flex-direction: column; gap: 16px; margin: 0 -5vw 20px; padding: 24px 5vw 20px; }
            .directory-banner h2 { font-size: 2.2rem; }
            .directory-stats { width: min(230px, calc(100% - 6px)); }
            .directory-search-panel { padding: 12px; }
            .directory-search-heading { align-items: flex-start; flex-direction: column; gap: 3px; }
            .directory-search-form { grid-template-columns: 1fr; }
            .directory-search-form input, .directory-search-form button { grid-column: auto; }
            .directory-results-head { align-items: flex-start; flex-direction: column; gap: 8px; }
            .directory-results-head h3 { font-size: 1.7rem; }
            .cards-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
            .card-img { height: 108px; }
            .card-content { padding: 9px; }
            .uni-info h5 { font-size: .9rem; }
            .tag { white-space: normal; }
        }

        @media (max-width: 380px) { .cards-grid { grid-template-columns: 1fr; } }

        html[data-theme="dark"] body .directory-banner { background: var(--yu-surface-soft) !important; border-color: var(--yu-border) !important; }
        html[data-theme="dark"] body .directory-banner h2 { color: var(--yu-text) !important; }
        html[data-theme="dark"] body .directory-banner p { color: var(--yu-muted) !important; }
        html[data-theme="dark"] body .directory-stats,
        html[data-theme="dark"] body .directory-search-panel { background: var(--yu-surface) !important; color: var(--yu-text) !important; border-color: var(--yu-border) !important; }
        html[data-theme="dark"] body .directory-search-heading h3 { color: var(--yu-text) !important; }
        html[data-theme="dark"] body .directory-search-heading p { color: var(--yu-muted) !important; }
        html[data-theme="dark"] body .directory-search-form input,
        html[data-theme="dark"] body .directory-search-form select { background: #0e1929 !important; color: var(--yu-text) !important; border-color: var(--yu-border) !important; }
        html[data-theme="dark"] body .browse-links a { background: var(--yu-surface-soft) !important; color: var(--yu-link) !important; border-color: var(--yu-border) !important; }
        html[data-theme="dark"] body .directory-results-head h3 { color: var(--yu-text) !important; }
        html[data-theme="dark"] body .directory-results-count { color: var(--yu-muted) !important; }
        html[data-theme="dark"] body .other-results { background: var(--yu-surface) !important; }

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
            <a href="index.php">Home</a>
            <a href="Universities.php"  class="active">Universities</a>
            <a href="opportunities.php">Opportunities</a>
            <a href="About.html">About Us</a>
        </div>
    </nav>

    <section class="featured-section">
        <header class="directory-banner">
            <div>
                <span class="directory-kicker">The YourUni campus noticeboard</span>
                <h2>Find your next campus.</h2>
                <p>One big map of choices: look up a university, pick a region, browse a field, or mix the filters until your shortlist starts to take shape.</p>
            </div>
            <div class="directory-stats" aria-label="University catalogue total">
                <strong><?= number_format($catalogueTotal) ?></strong>
                <span>campus listings</span>
                <a href="compare.php">Open comparison board &rarr;</a>
            </div>
        </header>

        <div class="directory-search-panel">
            <div class="directory-search-heading">
                <h3>Search the whole catalogue</h3>
                <p>Combine a name, region, and field, or leave everything open.</p>
            </div>
            <form action="Universities.php" method="GET" class="directory-search-form">
                <input type="search" name="search" value="<?= htmlspecialchars($searchName, ENT_QUOTES, 'UTF-8') ?>" placeholder="University name" aria-label="University name">
                <select name="region" aria-label="Filter by region">
                    <option value="">Every region</option>
                    <?php foreach ($regions as $region): ?>
                        <option value="<?= htmlspecialchars($region, ENT_QUOTES, 'UTF-8') ?>" <?= $searchRegion === $region ? 'selected' : '' ?>><?= htmlspecialchars($region, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="directory-field-filter" name="major" aria-label="Filter by field">
                    <option value="">Every field</option>
                    <?php foreach ($majors as $major): ?>
                        <option value="<?= htmlspecialchars($major, ENT_QUOTES, 'UTF-8') ?>" <?= $searchMajor === $major ? 'selected' : '' ?>><?= htmlspecialchars($major, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Find universities</button>
            </form>

            <div class="browse-rail">
                <span class="browse-title">Browse by region</span>
                <div class="browse-links">
                    <?php foreach ($regions as $region): ?>
                        <a href="Universities.php?region=<?= rawurlencode($region) ?>" <?= $searchRegion === $region ? 'aria-current="true"' : '' ?>><?= htmlspecialchars($region, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                    <a href="Universities.php">All regions</a>
                </div>
            </div>

            <div class="browse-rail">
                <span class="browse-title">Popular fields in the directory</span>
                <div class="browse-links">
                    <?php foreach (array_slice($majors, 0, 12) as $major): ?>
                        <a href="Universities.php?major=<?= rawurlencode($major) ?>" <?= $searchMajor === $major ? 'aria-current="true"' : '' ?>><?= htmlspecialchars($major, ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                    <?php if (count($majors) > 12): ?><a href="#directory-field-filter">All fields ↓</a><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="directory-results-head">
            <div>
                <span class="browse-title">Showing <?= $smartSearch ? 'admission matches' : number_format(count($matches)) . ' of ' . number_format($catalogueTotal) . ' listings' ?></span>
                <h3><?php if ($smartSearch): ?>Your score-based options<?php elseif ($searchName !== '' || $searchRegion !== '' || $searchMajor !== ''): ?>Filtered universities<?php else: ?>All Universities<?php endif; ?></h3>
                <?php if (!$smartSearch && ($searchName !== '' || $searchRegion !== '' || $searchMajor !== '')): ?>
                    <p class="directory-results-count"><?php if ($searchName !== ''): ?>Name: <?= htmlspecialchars($searchName, ENT_QUOTES, 'UTF-8') ?> · <?php endif; ?><?php if ($searchRegion !== ''): ?><?= htmlspecialchars($searchRegion, ENT_QUOTES, 'UTF-8') ?> · <?php endif; ?><?php if ($searchMajor !== ''): ?><?= htmlspecialchars($searchMajor, ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p>
                <?php endif; ?>
            </div>
            <div class="directory-results-actions">
                <?php if (!$smartSearch && ($searchName !== '' || $searchRegion !== '' || $searchMajor !== '')): ?><a class="directory-clear" href="Universities.php">Clear filters</a><?php endif; ?>
                <a class="yu-compare-link" href="compare.php">Compare up to 3 &rarr;</a>
            </div>
        </div>

        <?php if ($searchError !== ''): ?>
            <div class="empty-state"><h3>Search could not be completed</h3><p><?= htmlspecialchars($searchError, ENT_QUOTES, 'UTF-8') ?></p></div>
        <?php elseif ($smartSearch): ?>
            <div class="search-summary">
                <strong>Score:</strong> <?= (int)$userMarks ?>
                <?php if ($userRegion !== ''): ?> <span>· Region: <?= htmlspecialchars($userRegion, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                <?php if ($userDegree !== ''): ?> <span>· Preferred field: <?= htmlspecialchars($userDegree, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>

            <?php if (count($mainMatches) + count($generalMatches) + count($otherMatches) + count($unknownMatches) === 0): ?>
                <div class="empty-state"><h3>No score-eligible universities found</h3><p>Try a different region or review the current entry requirements.</p><a href="index.php" style="color:#0b5ee7;font-weight:bold;margin-top:10px;display:inline-block;">Back to search</a></div>
            <?php endif; ?>

            <?php if (count($mainMatches) > 0): ?>
                <section class="result-group"><div class="section-header"><h3>Best matches for your selected field</h3><span class="result-count"><?= count($mainMatches) ?> results</span></div><p class="result-note">These records match your selected field and the score/region filters. Always confirm current admission requirements with the university.</p><div class="cards-grid"><?php youruniRenderUniversityCards($mainMatches, true); ?></div></section>
            <?php endif; ?>

            <?php if (count($generalMatches) > 0): ?>
                <section class="result-group"><div class="section-header"><h3>General university options</h3><span class="result-count"><?= count($generalMatches) ?> results</span></div><p class="result-note">These institutions are classified as broad/general options or have confirmed pass-based requirements. Verify the official current admission notice before applying.</p><div class="cards-grid"><?php youruniRenderUniversityCards($generalMatches, true); ?></div></section>
            <?php endif; ?>

            <?php if (count($unknownMatches) > 0): ?>
                <section class="result-group"><div class="section-header"><h3>Options with requirements to verify</h3><span class="result-count"><?= count($unknownMatches) ?> results</span></div><p class="result-note">These records have no verified minimum-score or pass-based status. They are shown for discovery, not as score-eligible or guaranteed admission options.</p><div class="cards-grid"><?php youruniRenderUniversityCards($unknownMatches, true); ?></div></section>
            <?php endif; ?>

            <?php if (count($otherMatches) > 0): ?>
                <details class="other-results"><summary>Other score-eligible universities (<?= count($otherMatches) ?>)</summary><p class="result-note">These entries meet the score and region filters but are not listed as your selected field. They are shown for discovery, not as confirmation that they offer your chosen major.</p><div class="cards-grid"><?php youruniRenderUniversityCards($otherMatches, true); ?></div></details>
            <?php endif; ?>
        <?php else: ?>
            <div class="cards-grid" id="all-fields">
                <?php if (count($matches) > 0): ?>
                    <?php youruniRenderUniversityCards($matches); ?>
                <?php else: ?>
                    <div class="empty-state"><h3>No universities found</h3><p>Try searching with a different criteria or checking your spelling.</p><a href="Universities.php" style="color:#0b5ee7;font-weight:bold;margin-top:10px;display:inline-block;">Clear Search</a></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
    <footer class="yu-site-footer"><span><strong>YourUni</strong> · Explore Myanmar's Universities</span><span><a href="About.html">About Us</a> · <a href="donate.php">Support YourUni</a></span></footer>
    <script src="assets/youruni-global.js" defer></script>
</body>
</html>