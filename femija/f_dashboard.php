<?php
include("../configuration.php");
include("../auth.php");

if ($_SESSION['roli'] != 'femije') {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$sql = "SELECT COUNT(*) as total FROM notifications WHERE user_id=? AND is_read=0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$count = (int)($res['total'] ?? 0);

$latestMessagesQ = $conn->prepare("
    SELECT 
        m.teksti,
        m.data_krijimit,
        p.emri,
        p.mbiemri
    FROM mesazhe m
    JOIN perdorues p ON p.id = m.dergues_id
    WHERE m.marres_id = ?
    ORDER BY m.data_krijimit DESC
    LIMIT 2
");
$latestMessagesQ->bind_param("i", $user_id);
$latestMessagesQ->execute();
$latestMessages = $latestMessagesQ->get_result();

$recentChatsQ = $conn->prepare("
    SELECT 
        p.id,
        p.emri,
        p.mbiemri,
        p.foto_profil,
        MAX(m.data_krijimit) AS last_message_time
    FROM mesazhe m
    JOIN perdorues p 
        ON p.id = CASE 
            WHEN m.dergues_id = ? THEN m.marres_id
            ELSE m.dergues_id
        END
    WHERE m.dergues_id = ? OR m.marres_id = ?
    GROUP BY p.id, p.emri, p.mbiemri, p.foto_profil
    ORDER BY last_message_time DESC
    LIMIT 3
");
$recentChatsQ->bind_param("iii", $user_id, $user_id, $user_id);
$recentChatsQ->execute();
$recentChats = $recentChatsQ->get_result();
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Menu Fëmije</title>
    <link rel="stylesheet" href="../assets/child.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="child-ui">

<div class="child-page">
    <h1 class="child-title">Mirësevjen <?= htmlspecialchars($_SESSION['emri']) ?></h1>

    <div class="child-menu-wrapper">
        <button class="child-menu-button">☰</button>

        <div class="child-menu-box">
            <a href="notifications.php">Mesazhe / Njoftime (<?= $count ?>)</a>
            <a href="requests.php">Shiko kërkesat</a>
            <a href="friends_list.php">Shiko shokët</a>
            <a href="../profile.php">Profili juaj</a>
            <a href="../logout.php">Dil</a>
        </div>
    </div>

    <div class="child-dashboard-grid">
        <div class="child-glass">
            <h3 class="child-section-title">Kërko shokë</h3>

            <form class="child-search-form" method="GET" action="search.php">
                <input class="child-input" type="text" name="term" placeholder="Shkruaj emrin..." required>
                <button class="child-btn" type="submit">Kërko</button>
            </form>

            <div class="child-list">
                <h3 class="child-section-title">2 mesazhet e fundit</h3>

                <?php if ($latestMessages->num_rows == 0): ?>
                    <p class="child-muted">Nuk ke ende mesazhe të ardhura.</p>
                <?php endif; ?>

                <?php while ($m = $latestMessages->fetch_assoc()): ?>
                    <div class="child-item">
                        <div>
                            <b><?= htmlspecialchars($m['emri'] . ' ' . $m['mbiemri']) ?></b><br>
                            <?= htmlspecialchars($m['teksti']) ?><br>
                            <small class="child-muted"><?= htmlspecialchars($m['data_krijimit']) ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="child-glass">
            <h3 class="child-section-title">3 bisedat e fundit</h3>

            <?php if ($recentChats->num_rows == 0): ?>
                <p class="child-muted">Nuk ke ende biseda me shokë.</p>
            <?php endif; ?>

            <?php while ($chat = $recentChats->fetch_assoc()): ?>
                <?php $foto = '../' . ($chat['foto_profil'] ?: 'uploads/default.png'); ?>

                <div class="child-item">
                    <div class="child-item-main">
                        <img 
                            class="child-avatar" 
                            src="<?= htmlspecialchars($foto) ?>" 
                            onerror="this.onerror=null; this.src='../uploads/default.png';"
                            alt="Foto"
                        >

                        <div>
                            <b><?= htmlspecialchars($chat['emri'] . ' ' . $chat['mbiemri']) ?></b><br>
                            <small class="child-muted">Biseda e fundit: <?= htmlspecialchars($chat['last_message_time']) ?></small>
                        </div>
                    </div>

                    <a class="child-btn child-btn-light" href="messages.php?friend_id=<?= (int)$chat['id'] ?>">
    Hap
</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

</body>
</html>