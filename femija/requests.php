<?php
session_start();
require_once "../configuration.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT fr.id, p.emri, p.mbiemri, p.foto_profil 
    FROM friend_requests fr 
    JOIN perdorues p ON fr.sender_id = p.id 
    WHERE fr.receiver_id = ? 
      AND fr.status = 'pending' 
    ORDER BY fr.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rez = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Kërkesat</title>
    <link rel="stylesheet" href="../assets/child.css">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="child-ui">

<div class="child-center-page">
    <div class="child-center-box">
        <h2>Kërkesat e ardhura</h2>

        <?php if ($rez->num_rows == 0): ?>
            <p class="child-muted">Nuk ke asnjë kërkesë për momentin.</p>
        <?php endif; ?>

        <?php while ($row = $rez->fetch_assoc()): ?>
            <?php $foto = !empty($row['foto_profil']) ? '../' . $row['foto_profil'] : '../uploads/default.png'; ?>

            <div class="child-item">
                <div class="child-item-main">
                    <img 
                        class="child-avatar"
                        src="<?= htmlspecialchars($foto) ?>"
                        onerror="this.onerror=null; this.src='../uploads/default.png';"
                        alt="Foto"
                    >

                    <div>
                        <b><?= htmlspecialchars($row['emri'] . ' ' . $row['mbiemri']) ?></b><br>
                        <small class="child-muted">Të ka dërguar kërkesë për shok.</small>
                    </div>
                </div>

                <div>
                    <a class="child-btn child-btn-accept" href="accept.php?id=<?= (int)$row['id'] ?>">
                        Prano
                    </a>

                    <a class="child-btn child-btn-reject" href="reject.php?id=<?= (int)$row['id'] ?>">
                        Refuzo
                    </a>
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
