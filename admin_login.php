<?php
require_once __DIR__ . '/db_pdo.php';
ini_set('session.use_strict_mode', '1');
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) { header('Location: admin_dashboard.php'); exit; }
if (empty($_SESSION['login_csrf'])) $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
$error = '';
$lockedUntil = (int)($_SESSION['login_locked_until'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals((string)$_SESSION['login_csrf'], (string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Your form expired. Refresh the page and try again.';
    } elseif (time() < $lockedUntil) {
        $error = 'Too many attempts. Please wait a minute before trying again.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        try {
            $pdo = youruniPdo();
            $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admin_users WHERE username = :username LIMIT 1');
            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch();
            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = (int)$admin['id'];
                $_SESSION['admin_username'] = (string)$admin['username'];
                $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
                unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
                $upd = $pdo->prepare('UPDATE admin_users SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id');
                $upd->execute(['id' => (int)$admin['id']]);
                header('Location: admin_dashboard.php'); exit;
            }
            $attempts = (int)($_SESSION['login_attempts'] ?? 0) + 1;
            $_SESSION['login_attempts'] = $attempts;
            if ($attempts >= 5) { $_SESSION['login_locked_until'] = time() + 60; $_SESSION['login_attempts'] = 0; }
            $error = 'Invalid username or password.';
        } catch (Throwable $e) {
            error_log('YourUni admin login: ' . $e->getMessage());
            $error = 'Admin login is not configured yet. If this is your first visit, open setup_admin.php first.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Login - YourUni</title><link rel="stylesheet" href="assets/youruni-global.css"><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/admin.css"><style>
:root{--yu-bg:#f8fafc;--yu-surface:#fff;--yu-text:#0f172a;--yu-muted:#64748b;--yu-border:#e2e8f0}body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--yu-bg);color:var(--yu-text);min-height:100vh;display:grid;place-items:center;padding:24px}.login-card{width:min(100%,390px);background:var(--yu-surface);border:1px solid var(--yu-border);border-radius:20px;padding:30px;box-shadow:0 18px 55px rgba(15,23,42,.09)}h1{margin:0 0 8px;font-size:1.45rem}.muted{color:var(--yu-muted);line-height:1.6}.field{margin:16px 0}.field label{display:block;font-size:.87rem;font-weight:700;margin-bottom:7px}.field input{width:100%;padding:12px;border:1px solid var(--yu-border);border-radius:10px;font:inherit;background:var(--yu-surface);color:var(--yu-text)}button,.button{display:inline-block;width:100%;border:0;border-radius:10px;padding:12px 16px;background:#2563eb;color:white;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}.error{padding:12px;border-radius:10px;background:#fee2e2;color:#991b1b}.hint{font-size:.84rem;color:var(--yu-muted);margin-top:16px}html[data-theme=dark] .login-card{background:#111c2d;border-color:#2b3a50}html[data-theme=dark] .field input{background:#0e1929;color:#e5edf7;border-color:#2b3a50}
</style></head><body class="admin-login-page"><main class="login-card"><h1>🎓 YourUni Admin</h1><p class="muted">Sign in to manage university information.</p><?php if($error): ?><p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><form action="admin_login.php" method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['login_csrf'], ENT_QUOTES, 'UTF-8') ?>"><div class="field"><label for="username">Username</label><input id="username" name="username" autocomplete="username" required maxlength="80"></div><div class="field"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required></div><button type="submit">Sign in</button></form><p class="hint">First time here? <a href="setup_admin.php">Set up the first admin account</a>.</p></main><script src="assets/youruni-global.js" defer></script></body></html>
