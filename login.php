<?php
session_start();
require_once "configuration.php";

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['roli'] === 'femije') {
        header("Location: femija/f_dashboard.php");
    } else {
        header("Location: p_dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Logohu</title>
    <link rel="stylesheet" href="assets/auth.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#60a5fa">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<div class="auth-page">
    <div class="auth-box">
        <h2 class="auth-title">Logohu</h2>
        <p class="auth-subtitle">Vendos email-in dhe fjalëkalimin</p>

        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" name="login" class="auth-btn">
                Login
            </button>
        </form>

        <div class="auth-link">
            Nuk ke llogari?
            <a href="signup.php">Regjistrohu këtu</a>
        </div>
    </div>
</div>

<script>
if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("service-worker.js")
        .then(() => {
            console.log("Service Worker u regjistrua me sukses.");
        })
        .catch(error => {
            console.log("Gabim Service Worker:", error);
        });
}
</script>

</body>
</html>