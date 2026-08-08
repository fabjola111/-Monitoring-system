<?php
include("auth.php");
require_once "configuration.php";

if ($_SESSION['roli'] !== 'prind') {
    header("Location: femija/f_dashboard.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$notifQ = $conn->prepare("
    SELECT id, user_id, message, type, is_read, created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$notifQ->bind_param("i", $user_id);
$notifQ->execute();
$notifications = $notifQ->get_result();


$unreadQ = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE user_id = ?
      AND is_read = 0
");
$unreadQ->bind_param("i", $user_id);
$unreadQ->execute();
$unreadRow = $unreadQ->get_result()->fetch_assoc();
$unreadCount = (int)($unreadRow['total'] ?? 0);


$stmt = $conn->prepare("
    SELECT 
        p.id, 
        p.emri, 
        p.mbiemri, 
        p.email, 
        p.foto_profil,
        COUNT(a.id) AS total_alerts
    FROM perdorues p
    LEFT JOIN alarme a 
        ON a.femije_id = p.id 
       AND a.prind_id = ?
    WHERE p.prind_id = ? 
      AND p.roli = 'femije'
    GROUP BY p.id, p.emri, p.mbiemri, p.email, p.foto_profil
    ORDER BY p.emri
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$children = $stmt->get_result();


$stats = $conn->prepare("
    SELECT 
        COUNT(DISTINCT p.id) AS total_children,
        COUNT(DISTINCT a.id) AS total_alerts,
        COUNT(DISTINCT m.id) AS total_messages
    FROM perdorues p
    LEFT JOIN alarme a 
        ON a.femije_id = p.id 
       AND a.prind_id = ?
    LEFT JOIN mesazhe m 
        ON m.dergues_id = p.id 
        OR m.marres_id = p.id
    WHERE p.prind_id = ? 
      AND p.roli = 'femije'
");
$stats->bind_param("ii", $user_id, $user_id);
$stats->execute();
$statsRow = $stats->get_result()->fetch_assoc();

$totalChildren = (int)($statsRow['total_children'] ?? 0);
$totalAlerts = (int)($statsRow['total_alerts'] ?? 0);
$totalMessages = (int)($statsRow['total_messages'] ?? 0);
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Prindi</title>
    <link rel="stylesheet" href="assets/parent.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="parent-ui">

<div class="parent-page">
    <h1 class="welcome-title">Mirësevjen <?= htmlspecialchars($_SESSION['emri']) ?></h1>

    <div class="parent-menu">
        <button class="menu-button" type="button">☰</button>

        <div class="menu-dropdown">
            <a href="profile.php">Profili juaj / ID prindi</a>
            <a href="p_forbidden_words.php">Menaxho fjalët e ndaluara</a>
            <a href="p_mark_notifications_read.php">Njoftime të reja: <?= (int)$unreadCount ?></a>
            <a href="logout.php">Dil</a>
        </div>
    </div>

    <div class="dashboard-layout">
        <div class="glass-card">
            <h3 class="section-title">Njoftimet e fundit</h3>

            <?php $countNotif = 0; ?>

            <?php while ($n = $notifications->fetch_assoc()): ?>
                <?php if ($countNotif >= 2) break; ?>

                <div class="notification <?= ((int)$n['is_read'] === 0) ? 'unread' : '' ?>">
                    <b><?= htmlspecialchars($n['type']) ?></b>
                    <br>
                    <?= htmlspecialchars($n['message']) ?>
                    <br>
                    <small><?= htmlspecialchars($n['created_at']) ?></small>
                </div>

                <?php $countNotif++; ?>
            <?php endwhile; ?>

            <?php if ($countNotif === 0): ?>
                <p class="muted">Nuk ka njoftime.</p>
            <?php endif; ?>
        </div>

        <div class="stats-column">
            <div class="stat-card">
                <div class="stat-label">Fëmijë të lidhur</div>
                <div class="stat-number"><?= (int)$totalChildren ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Mesazhe totale</div>
                <div class="stat-number"><?= (int)$totalMessages ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Alerte totale</div>
                <div class="stat-number"><?= (int)$totalAlerts ?></div>
            </div>
        </div>
    </div>

    <div class="children-section">
        <h3 class="section-title">Fëmijët që përdorin programin</h3>

        <?php if ($children->num_rows == 0): ?>
            <div class="glass-card">
                <p>
                    Nuk ka ende fëmijë të lidhur me profilin tuaj.
                    Jepini fëmijës ID-në tuaj që shfaqet te profili.
                </p>
            </div>
        <?php endif; ?>

        <?php while ($row = $children->fetch_assoc()): ?>
            <?php
                $foto = $row['foto_profil'] ?: 'uploads/default.png';
                $childAlerts = (int)$row['total_alerts'];

                if ($childAlerts <= 5) {
                    $riskLevel = "LOW";
                    $riskClass = "risk-low";
                } elseif ($childAlerts <= 10) {
                    $riskLevel = "MEDIUM";
                    $riskClass = "risk-medium";
                } else {
                    $riskLevel = "HIGH";
                    $riskClass = "risk-high";
                }
            ?>

            <div class="child-card">
                <img 
                    class="avatar" 
                    src="<?= htmlspecialchars($foto) ?>" 
                    onerror="this.onerror=null; this.src='uploads/default.png';" 
                    alt="Foto profili"
                >

                <div style="flex:1">
                    <b><?= htmlspecialchars($row['emri'] . ' ' . $row['mbiemri']) ?></b>
                    <br>

                    <small><?= htmlspecialchars($row['email']) ?></small>
                    <br><br>

                    <span class="risk <?= $riskClass ?>">
                        Risk: <?= htmlspecialchars($riskLevel) ?> — <?= (int)$childAlerts ?> alerte
                    </span>
                </div>

                <a class="btn" href="p_child_report.php?id=<?= (int)$row['id'] ?>">
                    Shiko
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
