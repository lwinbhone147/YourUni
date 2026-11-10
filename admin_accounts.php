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
$currentAdminId = (int)$_SESSION['admin_id'];
$flash = $_SESSION['account_flash'] ?? null;
unset($_SESSION['account_flash']);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($csrfToken, $submittedToken)) {
        $error = 'This form expired. Refresh the page and try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'create') {
            $username = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');
            if (!preg_match('/^[A-Za-z0-9_.-]{4,80}$/', $username)) {
                $error = 'Username must be 4–80 characters using letters, numbers, dots, underscores, or hyphens.';
            } elseif (strlen($password) < 12) {
                $error = 'Use a password with at least 12 characters.';
            } elseif ($password !== $confirm) {
                $error = 'The passwords do not match.';
            } else {
                try {
                    $stmt = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (:username, :password_hash)');
                    $stmt->execute(['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
                    $_SESSION['account_flash'] = ['type' => 'success', 'message' => 'Admin account created. Share the login details privately and securely.'];
                    header('Location: admin_accounts.php'); exit;
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') $error = 'That username is already in use. Choose a different one.';
                    else { error_log('YourUni admin account creation: ' . $e->getMessage()); $error = 'The account could not be created. Check the database connection and try again.'; }
                }
            }
        } elseif ($action === 'delete') {
            $targetId = filter_var($_POST['admin_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$targetId || $targetId < 1) {
                $error = 'Choose a valid admin account.';
            } elseif ((int)$targetId === $currentAdminId) {
                $error = 'You cannot delete the account you are currently using.';
            } else {
                try {
                    $count = (int)$pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
                    if ($count <= 1) {
                        $error = 'The last admin account cannot be deleted.';
                    } else {
                        $stmt = $pdo->prepare('DELETE FROM admin_users WHERE id = :id');
                        $stmt->execute(['id' => (int)$targetId]);
                        $_SESSION['account_flash'] = ['type' => 'success', 'message' => $stmt->rowCount() ? 'Admin account deleted.' : 'That admin account was not found.'];
                        header('Location: admin_accounts.php'); exit;
                    }
                } catch (Throwable $e) {
                    error_log('YourUni admin account deletion: ' . $e->getMessage());
                    $error = 'The account could not be deleted. Please try again.';
                }
            }
        } else {
            $error = 'Unknown action.';
        }
    }
}
$admins = $pdo->query('SELECT id, username, created_at, last_login_at FROM admin_users ORDER BY id ASC')->fetchAll();
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Manage Admin Accounts - YourUni</title>
<link rel="stylesheet" href="assets/youruni-global.css"><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/admin.css">
<style>
*{box-sizing:border-box}body{margin:0;background:var(--yu-bg,#f8fafc);color:var(--yu-text,#0f172a);font-family:'Plus Jakarta Sans',sans-serif}.admin-page{width:min(1120px,calc(100% - 32px));margin:34px auto 90px}.topbar,.panel{background:var(--yu-surface,#fff);border:1px solid var(--yu-border,#e2e8f0);border-radius:20px;box-shadow:0 10px 28px rgba(15,23,42,.06)}.topbar{padding:24px;display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap}.brand{display:flex;align-items:center;gap:14px}.logo{width:48px;height:48px;border-radius:14px;background:#1d3557;display:grid;place-items:center;font-size:24px}.topbar h1{font-size:clamp(22px,3vw,30px);margin:0}.muted{color:var(--yu-muted,#64748b);line-height:1.6;margin:5px 0 0}.actions{display:flex;gap:10px;flex-wrap:wrap}.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;border:1px solid var(--yu-border,#e2e8f0);border-radius:11px;padding:11px 15px;font:inherit;font-weight:700;cursor:pointer;background:var(--yu-surface-soft,#f1f5f9);color:var(--yu-text,#0f172a)}.btn-primary{background:#2563eb;color:#fff;border-color:#2563eb}.btn-danger{background:#fee2e2;color:#991b1b;border-color:#fecaca}.grid{display:grid;grid-template-columns:minmax(280px,.8fr) minmax(0,1.2fr);gap:18px;margin-top:20px}.panel{overflow:hidden}.panel-head{padding:19px 22px;border-bottom:1px solid var(--yu-border,#e2e8f0)}.panel-head h2{margin:0;font-size:19px}.panel-body{padding:22px}.field{display:grid;gap:7px;margin-bottom:15px}.field label{font-size:13px;font-weight:700}.field input{width:100%;border:1px solid var(--yu-border,#cbd5e1);border-radius:10px;padding:12px 13px;font:inherit;background:var(--yu-surface,#fff);color:var(--yu-text,#0f172a)}.help{font-size:12px;color:var(--yu-muted,#64748b);line-height:1.55}.alert{padding:13px 15px;border-radius:12px;margin:0 0 18px;line-height:1.55}.alert.success{background:#dcfce7;color:#166534;border:1px solid #86efac}.alert.error{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5}html[data-theme="dark"] .alert.success{background:#123526!important;color:#bbf7d0!important;border-color:#216a46!important}html[data-theme="dark"] .alert.error{background:#401d27!important;color:#fecaca!important;border-color:#7f3344!important}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:520px}th,td{text-align:left;padding:13px 15px;border-bottom:1px solid var(--yu-border,#e2e8f0);vertical-align:middle}th{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--yu-muted,#64748b);background:var(--yu-surface-soft,#f1f5f9)}td{font-size:13px}.table-form{margin:0}.current{display:inline-block;font-size:11px;font-weight:700;padding:4px 7px;border-radius:999px;background:#dbeafe;color:#1d4ed8}.note{margin-top:20px;color:var(--yu-muted,#64748b);font-size:12px;line-height:1.65}@media(max-width:850px){.grid{grid-template-columns:1fr}.admin-page{margin-top:16px}.topbar{padding:18px}.panel-body{padding:18px}}
</style></head><body class="admin-accounts-page">
<main class="admin-page">
<header class="topbar"><div class="brand"><div class="logo">🎓</div><div><h1>Admin accounts</h1><p class="muted">Create additional trusted admin logins or remove an account that is no longer needed.</p></div></div><div class="actions"><a class="btn" href="change_password.php">Change my password</a><a class="btn" href="admin_dashboard.php">Back to dashboard</a></div></header>
<?php if ($flash): ?><div class="alert <?= accountH((string)$flash['type']) ?>" style="margin-top:18px"><?= accountH((string)$flash['message']) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert error" style="margin-top:18px"><?= accountH($error) ?></div><?php endif; ?>
<div class="grid"><section class="panel"><div class="panel-head"><h2>Create an admin account</h2></div><div class="panel-body">
<form method="post" action="admin_accounts.php" autocomplete="off"><input type="hidden" name="csrf_token" value="<?= accountH($csrfToken) ?>"><input type="hidden" name="action" value="create">
<div class="field"><label for="username">Username</label><input id="username" name="username" minlength="4" maxlength="80" pattern="[A-Za-z0-9_.-]{4,80}" autocomplete="off" required><span class="help">4–80 characters: letters, numbers, dots, underscores, or hyphens.</span></div>
<div class="field"><label for="password">Temporary password</label><input id="password" name="password" type="password" minlength="12" autocomplete="new-password" required><span class="help">At least 12 characters. Give it to the person privately; they should change it after signing in.</span></div>
<div class="field"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" minlength="12" autocomplete="new-password" required></div>
<button class="btn btn-primary" type="submit">Create admin account</button></form>
<p class="note">Every account created here has full admin access. Only create accounts for people you trust. There is no public sign-up form.</p>
</div></section>
<section class="panel"><div class="panel-head"><h2>Existing accounts <span class="muted" style="font-size:13px">(<?= count($admins) ?>)</span></h2></div><div class="table-wrap"><table><thead><tr><th>Username</th><th>Created</th><th>Last login</th><th>Action</th></tr></thead><tbody>
<?php foreach ($admins as $admin): ?><tr><td><strong><?= accountH((string)$admin['username']) ?></strong><?php if ((int)$admin['id'] === $currentAdminId): ?> <span class="current">You</span><?php endif; ?></td><td><?= accountH((string)$admin['created_at']) ?></td><td><?= accountH((string)($admin['last_login_at'] ?? 'Never')) ?></td><td><?php if ((int)$admin['id'] !== $currentAdminId && count($admins) > 1): ?><form class="table-form" method="post" action="admin_accounts.php" onsubmit="return confirm('Delete this admin account? The person will lose access.');"><input type="hidden" name="csrf_token" value="<?= accountH($csrfToken) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="admin_id" value="<?= (int)$admin['id'] ?>"><button class="btn btn-danger" type="submit">Delete</button></form><?php else: ?><span class="muted">Protected</span><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></section></div>
</main><script src="assets/youruni-global.js" defer></script></body></html>
