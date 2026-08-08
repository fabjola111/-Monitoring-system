<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user_id'])) {
    $_SESSION['user_id'] = (int)$_COOKIE['remember_user_id'];
    $_SESSION['roli'] = $_COOKIE['remember_user_role'] ?? '';
}

if (!isset($_SESSION['user_id'])) {
    $loginPath = (strpos($_SERVER['PHP_SELF'], '/femija/') !== false) ? '../login.php' : 'login.php';
    header("Location: " . $loginPath);
    exit;
}
?>