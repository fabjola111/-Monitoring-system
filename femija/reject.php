<?php
session_start();
require_once "../configuration.php";
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
$user_id = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT sender_id FROM friend_requests WHERE id=? AND receiver_id=? AND status='pending'");
$stmt->bind_param("ii", $id, $user_id); $stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
if(!$row) die("Kërkesa nuk u gjet.");
$sender_id = (int)$row['sender_id'];
$upd = $conn->prepare("UPDATE friend_requests SET status='rejected' WHERE id=?");
$upd->bind_param("i", $id); $upd->execute();
$mesazh = "Ftesa jote u refuzua"; $type = "rejected";
$stmtN = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
$stmtN->bind_param("iss", $sender_id, $mesazh, $type); $stmtN->execute();
header("Location: requests.php"); exit();
?>
