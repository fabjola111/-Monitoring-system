<?php
session_start();
require_once "../configuration.php";
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
$sender = (int)$_SESSION['user_id']; $receiver = (int)($_GET['id'] ?? 0);
if($sender === $receiver) die("Nuk lejohet të dërgosh kërkesë vetes");
$stmtFriend = $conn->prepare("SELECT id FROM friends WHERE (user1=? AND user2=?) OR (user1=? AND user2=?)");
$stmtFriend->bind_param("iiii", $sender, $receiver, $receiver, $sender); $stmtFriend->execute();
if($stmtFriend->get_result()->num_rows > 0) die("Ky përdorues është tashmë në listën e shokëve.");
$stmt_check = $conn->prepare("SELECT id FROM friend_requests WHERE sender_id=? AND receiver_id=? AND status='pending'");
$stmt_check->bind_param("ii", $sender, $receiver); $stmt_check->execute();
if($stmt_check->get_result()->num_rows == 0){
    $stmt_insert = $conn->prepare("INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
    $stmt_insert->bind_param("ii", $sender, $receiver); $stmt_insert->execute();
    $mesazh = "📩 " . $_SESSION['emri'] . " të ka dërguar një ftesë për shok"; $type = "friend_request";
    $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $stmt_notif->bind_param("iss", $receiver, $mesazh, $type); $stmt_notif->execute();
}
header("Location: f_dashboard.php"); exit();
?>
