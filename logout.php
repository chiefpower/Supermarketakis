<?php
session_start();

// Destroy all session data
$_SESSION = [];           // Clear session variables
session_unset();          // Free all session variables
session_destroy();        // Destroy the session

// Optional: clear authentication cookies
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, '/');
}

// Redirect to login page
header("Location: login.html");
exit;