<?php
session_start();
require_once "../configuration.php";

if (!isset($_SESSION['user_id']) || $_SESSION['roli'] !== 'femije') {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$sql = "
    SELECT 
        p.id, 
        p.emri, 
        p.mbiemri, 
        p.foto_profil,
        SUM(CASE WHEN m.marres_id=? AND m.is_read=0 THEN 1 ELSE 0 END) AS unread_count
    FROM perdorues p
    JOIN friends f ON (p.id=f.user1 OR p.id=f.user2)
    LEFT JOIN mesazhe m ON (m.dergues_id=p.id AND m.marres_id=?)
    WHERE (f.user1=? OR f.user2=?) 
      AND p.id!=?
    GROUP BY p.id, p.emri, p.mbiemri, p.foto_profil
    ORDER BY p.emri
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Shokët e tu</title>
    <link rel="stylesheet" href="../assets/child.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="child-ui">

<div class="child-center-page">
    <div class="child-center-box">
        <h2>Shokët e tu</h2>

        <?php if ($result->num_rows == 0): ?>
            <p class="child-muted">Nuk ke asnjë shok për momentin.</p>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>
            <?php $foto = '../' . ($row['foto_profil'] ?: 'uploads/default.png'); ?>

            <div class="child-friend-card">
                <div class="child-item-main">
                    <img 
                        class="child-avatar" 
                        src="<?= htmlspecialchars($foto) ?>" 
                        onerror="this.onerror=null; this.src='../uploads/default.png';"
                        alt="Foto"
                    >

                    <div>
                        <b><?= htmlspecialchars($row['emri'] . ' ' . $row['mbiemri']) ?></b><br>

                        <?php if ((int)$row['unread_count'] > 0): ?>
                            <span class="child-unread">
                                <?= (int)$row['unread_count'] ?> mesazh i ri
                            </span>
                        <?php else: ?>
                            <small class="child-muted">Shok aktiv në listë</small>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <a class="child-friend-link" href="messages.php?friend_id=<?= (int)$row['id'] ?>">
                        Flisni
                    </a>

                    <form 
                        method="post" 
                        action="remove_friends.php" 
                        style="display:inline;"
                        onsubmit="return confirm('A je i/e sigurt që do ta fshish këtë shok?')"
                    >
                        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                        <button type="submit" class="child-btn child-btn-danger">
                            Fshi
                        </button>
                    </form>
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