<?php
/**
 * YourUni admin logout handler.
 * Clears the admin session and redirects back to the login page.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Remove all session values.
$_SESSION = [];

// Expire the session cookie in the browser when cookies are enabled.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the server-side session and send the user to the login page.
session_destroy();
header('Location: admin_login.php?logged_out=1');
exit;
