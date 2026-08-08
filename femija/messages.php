<?php
session_start();
require_once "../configuration.php";
require_once "../includes/lsf_analyzer.php";

if (!isset($_SESSION['user_id']) || $_SESSION['roli'] !== 'femije') {
    header("Location: ../login.php");
    exit();
}

$my_id = (int)$_SESSION['user_id'];
$friend_id = (int)($_GET['friend_id'] ?? 0);

if (!$friend_id) {
    die("Shoku nuk është i specifikuar.");
}

$check = $conn->prepare("SELECT id FROM friends WHERE (user1=? AND user2=?) OR (user1=? AND user2=?)");
$check->bind_param("iiii", $my_id, $friend_id, $friend_id, $my_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    die("Mund të komunikosh vetëm me shokët e pranuar.");
}

$stmt = $conn->prepare("SELECT emri, mbiemri, foto_profil FROM perdorues WHERE id=? AND roli='femije'");
$stmt->bind_param("i", $friend_id);
$stmt->execute();
$friend = $stmt->get_result()->fetch_assoc();

if (!$friend) {
    die("Përdoruesi nuk u gjet.");
}

$friend_name = $friend['emri'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');

    if ($message === '') {
        die("Mesazhi nuk mund të jetë bosh.");
    }

    if (mb_strlen($message, 'UTF-8') > 500) {
        die("Mesazhi është shumë i gjatë.");
    }

    $analysis = lsf_analyze_message($message);
    $words = implode(', ', $analysis['found_words']);
    $types = implode(', ', $analysis['word_types']);

    $stmt = $conn->prepare("INSERT INTO mesazhe (dergues_id, marres_id, teksti, status, score, fjale_ndaluara, lloje_fjalesh, lsf_json, is_read) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param(
        "iississs",
        $my_id,
        $friend_id,
        $message,
        $analysis['status'],
        $analysis['score'],
        $words,
        $types,
        $analysis['analysis_json']
    );
    $stmt->execute();

    $message_id = $conn->insert_id;

    $stmt_name = $conn->prepare("SELECT emri, prind_id FROM perdorues WHERE id=?");
    $stmt_name->bind_param("i", $my_id);
    $stmt_name->execute();
    $me = $stmt_name->get_result()->fetch_assoc();

    $notif_msg = $me['emri'] . " të ka dërguar një mesazh.";
    $notif_type = "new_message";

    $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $notif_stmt->bind_param("iss", $friend_id, $notif_msg, $notif_type);
    $notif_stmt->execute();

    if ($analysis['status'] !== 'normal' && !empty($me['prind_id'])) {
        $niveli = $analysis['status'] === 'ofendues' ? 'high' : 'medium';
        $pershkrimi = "Mesazh me fjalë të ndaluara: " . ($words ?: 'analizë sintaksore e dyshimtë');
        $prind_id = (int)$me['prind_id'];

        $alarm = $conn->prepare("INSERT INTO alarme (mesazh_id, femije_id, prind_id, niveli, pershkrimi) VALUES (?, ?, ?, ?, ?)");
        $alarm->bind_param("iiiss", $message_id, $my_id, $prind_id, $niveli, $pershkrimi);
        $alarm->execute();

        $parentMsg = "Alert: fëmija " . $me['emri'] . " përdori komunikim të papërshtatshëm (" . $analysis['status'] . ").";
        $typeAlert = "alert_komunikimi";

        $pn = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
        $pn->bind_param("iss", $prind_id, $parentMsg, $typeAlert);
        $pn->execute();
    }

    header("Location: messages.php?friend_id=" . $friend_id);
    exit();
}

$update = $conn->prepare("UPDATE mesazhe SET is_read=1 WHERE dergues_id=? AND marres_id=?");
$update->bind_param("ii", $friend_id, $my_id);
$update->execute();

$stmt = $conn->prepare("SELECT * FROM mesazhe WHERE (dergues_id=? AND marres_id=?) OR (dergues_id=? AND marres_id=?) ORDER BY data_krijimit ASC");
$stmt->bind_param("iiii", $my_id, $friend_id, $friend_id, $my_id);
$stmt->execute();
$messages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($friend_name) ?></title>
    <link rel="stylesheet" href="../assets/child.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="child-ui">

<div class="chat-page">
    <div class="chat-title">
        <img 
            class="child-avatar" 
            src="<?= htmlspecialchars(!empty($friend['foto_profil']) ? '../' . $friend['foto_profil'] : '../uploads/default.png') ?>"
            onerror="this.onerror=null; this.src='../uploads/default.png';"
            alt="Foto"
        >

        <h2><?= htmlspecialchars($friend_name) ?></h2>
    </div>

    <div class="chat-shell">
        <div class="chat-box">
            <?php while ($msg = $messages->fetch_assoc()): ?>
                <?php $mine = ((int)$msg['dergues_id'] === $my_id); ?>

                <div class="msg <?= $mine ? 'me' : 'friend' ?>">
                    <b><?= $mine ? 'Ti' : htmlspecialchars($friend_name) ?>:</b>
                    <?= htmlspecialchars($msg['teksti']) ?>
                </div>
            <?php endwhile; ?>
        </div>

        <form class="chat-form" method="post">
            <input 
                type="text" 
                name="message" 
                placeholder="Shkruaj mesazh..." 
                maxlength="500"
                required
            >
            <button type="submit">Dërgo</button>
        </form>
    </div>

    <div class="child-actions">
        <a class="child-btn child-btn-light" href="f_dashboard.php">Shko në menu</a>
    </div>
</div>

</body>
</html>