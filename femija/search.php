<?php
session_start();
require_once "../configuration.php";

if (!isset($_SESSION['user_id']) || $_SESSION['roli'] !== 'femije') {
    die("User not logged in");
}

$user_id = (int)$_SESSION['user_id'];
$term = trim($_GET['term'] ?? '');

if ($term === '') {
    echo "Shkruaj një emër...";
    exit;
}

$sql = "
    SELECT id, emri, mbiemri, foto_profil 
    FROM perdorues 
    WHERE (emri LIKE ? OR mbiemri LIKE ?) 
      AND roli='femije' 
      AND id != ? 
    ORDER BY emri
";
$stmt = $conn->prepare($sql);
$searchTerm = "%$term%";
$stmt->bind_param("ssi", $searchTerm, $searchTerm, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Kërko shok</title>
    <link rel="stylesheet" href="../assets/child.css">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="child-ui">

<div class="child-center-page">
    <div class="child-center-box">
        <h2>Rezultatet e kërkimit</h2>

        <?php if ($result->num_rows == 0): ?>
            <p class="child-muted">Nuk u gjet asnjë përdorues.</p>
        <?php endif; ?>

        <?php while ($row = $result->fetch_assoc()): ?>
            <?php $foto = !empty($row['foto_profil']) ? '../' . $row['foto_profil'] : '../uploads/default.png'; ?>

            <div class="search-result">
                <div class="child-item-main">
                    <img 
                        class="child-avatar"
                        src="<?= htmlspecialchars($foto) ?>"
                        onerror="this.onerror=null; this.src='../uploads/default.png';"
                        alt="Foto"
                    >

                    <b><?= htmlspecialchars($row['emri'] . ' ' . $row['mbiemri']) ?></b>
                </div>

                <a class="child-btn" href="send_request.php?id=<?= (int)$row['id'] ?>">
                    Shto shok
                </a>
            </div>
        <?php endwhile; ?>

        <div class="child-actions">
            <a class="child-btn child-btn-light" href="f_dashboard.php">Shko në menu</a>
        </div>
    </div>
</div>

</body>
</html>
