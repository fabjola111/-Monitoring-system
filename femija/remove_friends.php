<?php
session_start();
require_once "../configuration.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$friend_id = (int)($_POST['id'] ?? 0);

if ($friend_id <= 0) {
    die("Shoku nuk është i vlefshëm.");
}

if ($friend_id === $user_id) {
    die("Nuk mund të fshish veten nga shokët.");
}


$check = $conn->prepare("
    SELECT id FROM friends 
    WHERE (user1 = ? AND user2 = ?) 
       OR (user1 = ? AND user2 = ?)
    LIMIT 1
");
$check->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows === 0) {
    die("Ky përdorues nuk është në listën tënde të shokëve.");
}


$sql_user = "SELECT emri FROM perdorues WHERE id=?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_row = $result_user->fetch_assoc();

if (!$user_row) {
    die("Përdoruesi nuk u gjet.");
}

$emri_useri_ti = $user_row['emri'];


$sql_delete = "DELETE FROM friends 
               WHERE (user1 = ? AND user2 = ?) 
               OR (user1 = ? AND user2 = ?)";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
$stmt_delete->execute();


$message = "⛔ $emri_useri_ti të ka hequr nga shokët!";
$insert_notif = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
$insert_notif->bind_param("is", $friend_id, $message);
$insert_notif->execute();


header("Location: friends_list.php");
exit();
?>