<?php
session_start();

// Unset all session variables
$_SESSION = [];

// If you’re using cookies for sessions, expire that cookie too:
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),    // usually PHPSESSID
        '',
        time() - 42000,    // in the past
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finally destroy the session on the server
session_destroy();

// Send the user back to login
header('Location: login.php');
exit;
