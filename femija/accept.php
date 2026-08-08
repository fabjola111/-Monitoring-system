<?php
session_start();
require_once "../configuration.php";
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
$user_id = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM friend_requests WHERE id=? AND receiver_id=? AND status='pending'");
$stmt->bind_param("ii", $id, $user_id); $stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if(!$row) die("Kërkesa nuk u gjet.");
$user1 = (int)$row['sender_id']; $user2 = (int)$row['receiver_id'];
$a = min($user1,$user2); $b = max($user1,$user2);
$conn->query("UPDATE friend_requests SET status='accepted' WHERE id=$id");
$stmtF = $conn->prepare("INSERT IGNORE INTO friends (user1, user2) VALUES (?, ?)");
$stmtF->bind_param("ii", $a, $b); $stmtF->execute();
$emri_pranues = $_SESSION['emri'];
$mesazh = "📩 Ftesa jote u pranua nga $emri_pranues"; $type = "accepted";
$stmtN = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
$stmtN->bind_param("iss", $user1, $mesazh, $type); $stmtN->execute();
header("Location: requests.php"); exit();
?>
