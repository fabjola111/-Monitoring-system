<?php
session_start();
require_once "configuration.php";

if (!isset($_POST['login'])) {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    die("Ju lutem plotësoni të dhënat për t'u loguar.");
}

$stmt = $conn->prepare("SELECT * FROM perdorues WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    showMessage("Emaili nuk ekziston, ju lutem kontrolloni sërish!");
}

$user = $res->fetch_assoc();

if ((int)$user['is_email_verified'] === 0) {
    showMessage("Duhet të verifikoni emailin me kodin e verifikimit!");
}

if (!password_verify($password, $user['password'])) {
   showMessage("Fjalëkalim i gabuar, provoni sërish!");
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['roli'] = $user['roli'];
$_SESSION['emri'] = $user['emri'];


setcookie("remember_user_id", $user['id'], time() + (30 * 24 * 60 * 60), "/");
setcookie("remember_user_role", $user['roli'], time() + (30 * 24 * 60 * 60), "/");

if ($user['roli'] === 'prind') {
    header("Location: p_dashboard.php");
} else {
    header("Location: femija/f_dashboard.php");
}

exit;
?>