<?php
include("auth.php");
require_once "configuration.php";

if ($_SESSION['roli'] !== 'prind') {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();

header("Location: p_dashboard.php");
exit();
?>