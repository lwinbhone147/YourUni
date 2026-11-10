<?php
// Shared security bootstrap for authenticated YourUni admin tools.
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || empty($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}
require_once __DIR__ . '/db_pdo.php';
if (empty($_SESSION['admin_csrf_token'])) $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
$csrfToken = (string)$_SESSION['admin_csrf_token'];
function accountH(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$pdo = youruniPdo();
$adminId = (int)$_SESSION['admin_id'];
$flash = $_SESSION['password_flash'] ?? null;
unset($_SESSION['password_flash']);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $submittedToken)) {
        $error = 'This form expired. Refresh the page and try again.';
    } else {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');
        try {
            $stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $adminId]);
            $row = $stmt->fetch();
            if (!$row) {
                $_SESSION = [];
                session_destroy();
                header('Location: admin_login.php?account_missing=1'); exit;
            } elseif (!password_verify($currentPassword, (string)$row['password_hash'])) {
                $error = 'The current password is incorrect.';
            } elseif (strlen($newPassword) < 12) {
                $error = 'Use a new password with at least 12 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'The new passwords do not match.';
            } elseif (hash_equals($currentPassword, $newPassword)) {
                $error = 'Choose a new password that is different from the current one.';
            } else {
                $update = $pdo->prepare('UPDATE admin_users SET password_hash = :hash WHERE id = :id');
                $update->execute(['hash' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $adminId]);
                session_regenerate_id(true);
                $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['password_flash'] = ['type' => 'success', 'message' => 'Password changed successfully. Use the new password the next time you sign in.'];
                header('Location: change_password.php'); exit;
            }
        } catch (Throwable $e) {
            error_log('YourUni admin password change: ' . $e->getMessage());
            $error = 'The password could not be changed. Please try again.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Change Password - YourUni</title>
<link rel="stylesheet" href="assets/youruni-global.css"><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
<style>*{box-sizing:border-box}body{margin:0;background:var(--yu-bg,#f8fafc);color:var(--yu-text,#0f172a);font-family:'Plus Jakarta Sans',sans-serif}.wrap{width:min(620px,calc(100% - 32px));margin:56px auto 90px}.panel{background:var(--yu-surface,#fff);color:var(--yu-text,#0f172a);border:1px solid var(--yu-border,#e2e8f0);border-radius:22px;padding:clamp(22px,4vw,36px);box-shadow:0 14px 38px rgba(15,23,42,.07)}h1{font-size:clamp(24px,4vw,32px);margin:0 0 8px}.muted{color:var(--yu-muted,#64748b);line-height:1.65}.field{display:grid;gap:7px;margin:20px 0}.field label{font-weight:700;font-size:13px}.field input{width:100%;padding:13px;border:1px solid var(--yu-border,#cbd5e1);border-radius:11px;background:var(--yu-surface,#fff);color:var(--yu-text,#0f172a);font:inherit}.btn{display:inline-flex;align-items:center;justify-content:center;border-radius:11px;border:1px solid #2563eb;background:#2563eb;color:white;padding:12px 16px;font:inherit;font-weight:700;text-decoration:none;cursor:pointer}.btn-secondary{border-color:var(--yu-border,#e2e8f0);background:var(--yu-surface-soft,#f1f5f9);color:var(--yu-text,#0f172a);margin-left:8px}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}.actions .btn-secondary{margin-left:0}.alert{padding:14px 16px;border-radius:12px;margin:18px 0;line-height:1.5}.alert.success{background:#dcfce7;color:#166534;border:1px solid #86efac}.alert.error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}html[data-theme="dark"] .alert.success{background:#123526!important;color:#bbf7d0!important;border-color:#216a46!important}html[data-theme="dark"] .alert.error{background:#401d27!important;color:#fecaca!important;border-color:#7f3344!important}.note{font-size:12px;color:var(--yu-muted,#64748b);line-height:1.6;margin-top:18px}</style></head>
<body class="admin-password-page"><main class="wrap"><section class="panel"><div style="font-size:30px;margin-bottom:12px">🔐</div><h1>Change your password</h1><p class="muted">Change the password for the admin account you're currently signed in with. Your current password is required for security.</p>
<?php if ($flash): ?><div class="alert <?= accountH((string)$flash['type']) ?>"><?= accountH((string)$flash['message']) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert error"><?= accountH($error) ?></div><?php endif; ?>
<form method="post" action="change_password.php" autocomplete="off"><input type="hidden" name="csrf_token" value="<?= accountH($csrfToken) ?>">
<div class="field"><label for="current_password">Current password</label><input id="current_password" type="password" name="current_password" autocomplete="current-password" required></div>
<div class="field"><label for="new_password">New password</label><input id="new_password" type="password" name="new_password" minlength="12" autocomplete="new-password" required><span class="note">Use at least 12 characters. Don't reuse a password from another account.</span></div>
<div class="field"><label for="confirm_password">Confirm new password</label><input id="confirm_password" type="password" name="confirm_password" minlength="12" autocomplete="new-password" required></div>
<div class="actions"><button class="btn" type="submit">Change password</button><a class="btn btn-secondary" href="admin_dashboard.php">Back to dashboard</a></div></form>
</section></main><script src="assets/youruni-global.js" defer></script></body></html>
