<?php
require_once __DIR__ . '/db_pdo.php';
ini_set('session.use_strict_mode', '1');
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
$error = '';
$created = false;
$httpHost = strtolower((string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
$httpHost = preg_replace('/:\\d+$/', '', $httpHost) ?? $httpHost;
$isLocalSetup = in_array($httpHost, ['localhost', '127.0.0.1', '::1'], true);
$setupKeyRequired = !$isLocalSetup;
try {
    $pdo = youruniPdo();
    $hasAdmin = (int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn() > 0;
} catch (Throwable $e) {
    error_log('YourUni first-run admin setup: ' . $e->getMessage());
    http_response_code(500);
    exit('Database setup is incomplete. Import youruni_complete.sql, then refresh this page.');
}
if (!$hasAdmin && empty($_SESSION['setup_csrf'])) {
    $_SESSION['setup_csrf'] = bin2hex(random_bytes(32));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$hasAdmin) {
    if (!hash_equals((string)($_SESSION['setup_csrf'] ?? ''), (string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Your form expired. Refresh the page and try again.';
    } elseif ($setupKeyRequired && (YOURUNI_SETUP_KEY === '' || !hash_equals(YOURUNI_SETUP_KEY, (string)($_POST['setup_key'] ?? '')))) {
        $error = 'Remote first-admin setup is locked unless YOURUNI_SETUP_KEY is configured and entered correctly.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_.-]{4,80}$/', $username)) {
            $error = 'Username must be 4–80 letters, numbers, dots, underscores, or hyphens.';
        } elseif (strlen($password) < 12) {
            $error = 'Use a password with at least 12 characters.';
        } elseif ($password !== $confirm) {
            $error = 'The passwords do not match.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO admin_users (username,password_hash) VALUES (:username,:hash)');
                $stmt->execute(['username' => $username, 'hash' => password_hash($password, PASSWORD_DEFAULT)]);
                session_regenerate_id(true);
                unset($_SESSION['setup_csrf']);
                $created = true;
                $hasAdmin = true;
            } catch (Throwable $e) {
                error_log('YourUni admin creation: ' . $e->getMessage());
                $error = 'The admin account could not be created. The username may already exist.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<title>Admin setup · YourUni</title>
<link rel="stylesheet" href="assets/youruni-global.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  color-scheme: light;
  --setup-bg: #f5f8ff;
  --setup-card: rgba(255,255,255,.94);
  --setup-card-soft: #f8faff;
  --setup-text: #101c31;
  --setup-muted: #61718b;
  --setup-border: #dfe7f3;
  --setup-primary: #2563eb;
  --setup-primary-hover: #1d4ed8;
  --setup-icon-bg: #eaf1ff;
  --setup-shadow: 0 24px 80px rgba(15, 35, 75, .12);
}
html[data-theme="dark"] {
  color-scheme: dark;
  --setup-bg: #09111f;
  --setup-card: rgba(17, 28, 45, .96);
  --setup-card-soft: #0d192a;
  --setup-text: #edf4ff;
  --setup-muted: #a8bad3;
  --setup-border: #293b54;
  --setup-primary: #3b82f6;
  --setup-primary-hover: #60a5fa;
  --setup-icon-bg: #1a3152;
  --setup-shadow: 0 24px 80px rgba(0, 0, 0, .25);
}
* { box-sizing: border-box; }
html, body { min-height: 100%; }
body.setup-page {
  margin: 0 !important;
  min-height: 100vh;
  padding: 40px 22px 92px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  font-family: 'Plus Jakarta Sans', sans-serif !important;
  color: var(--setup-text) !important;
  background:
    radial-gradient(ellipse at 14% 15%, rgba(37,99,235,.10), transparent 34%),
    radial-gradient(ellipse at 88% 82%, rgba(14,165,233,.09), transparent 30%),
    var(--setup-bg) !important;
  transition: background-color .2s ease, color .2s ease;
}
.setup-shell { width: min(100%, 570px); margin: auto; }
.setup-brand { display:flex; align-items:center; justify-content:center; gap:12px; margin: 0 auto 22px; text-align:left; }
.setup-logo { width:48px; height:48px; border-radius:15px; display:grid; place-items:center; flex:0 0 auto; background:var(--setup-icon-bg); font-size:25px; box-shadow:inset 0 0 0 1px var(--setup-border); }
.setup-brand-name { margin:0; color:var(--setup-text); font-size:1.25rem; font-weight:800; letter-spacing:-.04em; line-height:1.2; }
.setup-brand-name span { color:var(--setup-primary); }
.setup-brand-tagline { color:var(--setup-muted); font-size:.8rem; margin-top:4px; }
.setup-card { width:100%; border:1px solid var(--setup-border); border-radius:24px; padding:clamp(24px, 5vw, 36px); background:var(--setup-card); box-shadow:var(--setup-shadow); backdrop-filter:blur(14px); }
.setup-eyebrow { display:inline-flex; align-items:center; gap:8px; padding:7px 11px; border-radius:999px; background:var(--setup-icon-bg); color:var(--setup-primary); font-size:.73rem; font-weight:800; letter-spacing:.09em; text-transform:uppercase; }
.setup-eyebrow-dot { width:7px; height:7px; border-radius:50%; background:var(--setup-primary); }
.setup-title { margin:18px 0 9px; color:var(--setup-text) !important; font-size:clamp(1.55rem, 4vw, 1.9rem); line-height:1.18; letter-spacing:-.045em; font-weight:800; }
.setup-intro { margin:0 0 24px; color:var(--setup-muted) !important; font-size:.94rem; line-height:1.7; }
.setup-field { margin:17px 0; }
.setup-field label { display:block; margin-bottom:8px; color:var(--setup-text) !important; font-size:.86rem; font-weight:700; }
.setup-field input { display:block; width:100%; min-height:48px; padding:12px 14px; border:1px solid var(--setup-border) !important; border-radius:12px; outline:none; background:var(--setup-card-soft) !important; color:var(--setup-text) !important; font:inherit; font-size:.94rem; transition:border-color .15s ease, box-shadow .15s ease; }
.setup-field input::placeholder { color:var(--setup-muted); opacity:.8; }
.setup-field input:focus { border-color:var(--setup-primary) !important; box-shadow:0 0 0 4px rgba(37,99,235,.13); }
.setup-help { display:block; margin-top:7px; color:var(--setup-muted) !important; font-size:.78rem; line-height:1.5; }
.setup-button, .setup-page a.setup-button, .setup-page button.setup-button {
  display:flex !important; width:100%; min-height:50px; align-items:center; justify-content:center; gap:9px;
  margin-top:22px; padding:13px 18px; border:1px solid transparent !important; border-radius:13px !important;
  background:var(--setup-primary) !important; color:#fff !important; -webkit-text-fill-color:#fff !important;
  font:inherit !important; font-weight:800 !important; text-decoration:none !important; text-align:center;
  cursor:pointer; box-shadow:0 8px 18px rgba(37,99,235,.2); transition:background .15s ease, transform .15s ease;
}
.setup-page a.setup-button *, .setup-page button.setup-button * { color:#fff !important; -webkit-text-fill-color:#fff !important; }
.setup-button:hover { background:var(--setup-primary-hover) !important; color:#fff !important; transform:translateY(-1px); }
.setup-button:focus-visible { outline:3px solid rgba(96,165,250,.7); outline-offset:3px; }
.setup-notice { display:flex; align-items:flex-start; gap:13px; margin-top:22px; padding:17px; border:1px solid var(--setup-border); border-radius:16px; background:var(--setup-card-soft); }
.setup-notice-icon { width:38px; height:38px; display:grid; place-items:center; flex:0 0 auto; border-radius:12px; background:var(--setup-icon-bg); font-size:18px; }
.setup-notice-title { margin:1px 0 5px; color:var(--setup-text) !important; font-size:.96rem; font-weight:800; line-height:1.4; }
.setup-notice-copy { margin:0; color:var(--setup-muted) !important; font-size:.86rem; line-height:1.65; }
.setup-notice.success { border-color:rgba(22,163,74,.35) !important; background:rgba(22,163,74,.09) !important; }
.setup-notice.success .setup-notice-icon { background:rgba(22,163,74,.15); }
.setup-notice.error { border-color:rgba(220,38,38,.3) !important; background:rgba(220,38,38,.08) !important; }
.setup-notice.error .setup-notice-icon { background:rgba(220,38,38,.12); }
.setup-notice.error .setup-notice-title, .setup-notice.error .setup-notice-copy { color:var(--setup-text) !important; }
.setup-footer { margin-top:18px; text-align:center; color:var(--setup-muted); font-size:.76rem; line-height:1.6; }
.setup-footer strong { color:var(--setup-text); font-weight:700; }
/* Neutralize old shared alert/button color rules on this standalone page. */
html[data-theme="dark"] body.setup-page .setup-card,
html[data-theme="light"] body.setup-page .setup-card { color:var(--setup-text) !important; }
html[data-theme="dark"] body.setup-page .setup-notice.success,
html[data-theme="light"] body.setup-page .setup-notice.success { color:var(--setup-text) !important; }
@media (max-width:520px) {
  body.setup-page { padding:26px 15px 88px !important; align-items:flex-start !important; }
  .setup-shell { margin:auto; }
  .setup-card { border-radius:20px; padding:23px 19px; }
  .setup-brand { justify-content:flex-start; margin-left:4px; }
  .setup-title { margin-top:15px; }
}
@media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition:none !important; scroll-behavior:auto !important; } }
</style>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body class="setup-page">
<main class="setup-shell">
  <header class="setup-brand" aria-label="YourUni">
    <div class="setup-logo" aria-hidden="true">🎓</div>
    <div>
      <p class="setup-brand-name">Your<span>Uni</span></p>
      <div class="setup-brand-tagline">Explore Myanmar's Universities</div>
    </div>
  </header>

  <section class="setup-card">
    <span class="setup-eyebrow"><span class="setup-eyebrow-dot"></span> Administrator access</span>
    <?php if ($created): ?>
      <h1 class="setup-title">You're all set.</h1>
      <p class="setup-intro">Your YourUni administrator account has been created successfully.</p>
      <div class="setup-notice success" role="status">
        <div class="setup-notice-icon" aria-hidden="true">✓</div>
        <div>
          <p class="setup-notice-title">Admin account created</p>
          <p class="setup-notice-copy">For security, first-time setup is now locked. Continue to the login page to manage your website.</p>
        </div>
      </div>
      <a class="setup-button" href="admin_login.php">Continue to admin login <span aria-hidden="true">→</span></a>
    <?php elseif ($hasAdmin): ?>
      <h1 class="setup-title">Your admin account is ready.</h1>
      <p class="setup-intro">First-time setup has been locked because an administrator account already exists. Sign in to continue managing YourUni.</p>
      <div class="setup-notice">
        <div class="setup-notice-icon" aria-hidden="true">🔒</div>
        <div>
          <p class="setup-notice-title">Setup is already complete</p>
          <p class="setup-notice-copy">You don't need to create another account here. Use your existing administrator username and password.</p>
        </div>
      </div>
      <a class="setup-button" href="admin_login.php">Go to admin login <span aria-hidden="true">→</span></a>
    <?php else: ?>
      <h1 class="setup-title">Create your admin account.</h1>
      <p class="setup-intro">Set up the first administrator for YourUni. Choose a unique password and keep it private.</p>
      <?php if ($error): ?>
        <div class="setup-notice error" role="alert"><div class="setup-notice-icon" aria-hidden="true">!</div><div><p class="setup-notice-title">Account setup needs attention</p><p class="setup-notice-copy"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p></div></div>
      <?php endif; ?>
      <form method="post" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)($_SESSION['setup_csrf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($setupKeyRequired): ?>
          <div class="setup-field"><label for="setup_key">One-time hosting setup key</label><input id="setup_key" name="setup_key" autocomplete="off" required><small class="setup-help">Configure YOURUNI_SETUP_KEY in your hosting environment before using first-time setup online.</small></div>
        <?php endif; ?>
        <div class="setup-field"><label for="username">Admin username</label><input id="username" name="username" autocomplete="username" minlength="4" maxlength="80" required placeholder="Choose a username"><small class="setup-help">Use 4–80 letters, numbers, dots, underscores, or hyphens.</small></div>
        <div class="setup-field"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="new-password" minlength="12" required placeholder="At least 12 characters"><small class="setup-help">Use a long, unique password that you don't use on other sites.</small></div>
        <div class="setup-field"><label for="confirm_password">Confirm password</label><input id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" minlength="12" required placeholder="Enter the same password again"></div>
        <button class="setup-button" type="submit">Create admin account <span aria-hidden="true">→</span></button>
      </form>
    <?php endif; ?>
  </section>
  <footer class="setup-footer"><strong>YourUni</strong> · Secure administrator setup<br>Keep your admin credentials private.</footer>
</main>
<script src="assets/youruni-global.js" defer></script>
</body>
</html>
