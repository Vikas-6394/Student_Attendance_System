<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'expense_manager');
define('DB_USER', 'root');
define('DB_PASS', 'yourpassword');

define('APP_URL', '/college project/public');

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'use_strict_mode' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);