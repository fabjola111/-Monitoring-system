<?php
session_start();

if (!isset($_SESSION['PENDING_EMAIL_VERIFY_USER_ID'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Verifikimi i Emailit</title>
    <link rel="stylesheet" href="assets/auth.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<div class="auth-page">
    <div class="auth-box">
        <h2 class="auth-title">Verifiko emailin</h2>
        <p class="auth-subtitle">Vendos kodin 6-shifror që të ka ardhur në email</p>

        <?php if (!empty($_SESSION['verify_error'])): ?>
    <div class="message error">
        <?= htmlspecialchars($_SESSION['verify_error']) ?>
    </div>
    <?php unset($_SESSION['verify_error']); ?>
<?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="message success">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form method="POST" action="verify_email_process.php">
            <div class="form-group">
                <label>Kodi i verifikimit</label>
                <input type="text" name="code" maxlength="6" required>
            </div>

            <button type="submit" name="verify" class="auth-btn">
                Verifiko
            </button>
        </form>

        <form method="POST" action="verify_email_process.php" style="margin-top:12px;">
            <button type="submit" name="resend" class="auth-btn">
                Ridërgo kodin
            </button>
        </form>

        <div class="auth-link">
            <a href="login.php">Kthehu te login</a>
        </div>
    </div>
</div>

</body>
</html>