<?php
session_start();
require_once "../configuration.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$update = $conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
$update->bind_param("i", $user_id);
$update->execute();
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Mesazhe / Njoftime</title>
    <link rel="stylesheet" href="../assets/child.css">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="child-ui">

<div class="child-center-page">
    <div class="child-center-box">
        <h2>Mesazhe / Njoftime</h2>

        <?php if ($result->num_rows == 0): ?>
            <p class="child-muted">Nuk ke asnjë njoftim.</p>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="child-item">
                <div>
                    <?= htmlspecialchars($row['message']) ?><br>
                    <?php if (!empty($row['created_at'])): ?>
                        <small class="child-muted"><?= htmlspecialchars($row['created_at']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>

        <div class="child-actions">
            <a class="child-btn child-btn-light" href="f_dashboard.php">Shko në menu</a>
        </div>
    </div>
</div>

</body>
</html>
