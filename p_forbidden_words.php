<?php
include("auth.php");
require_once "configuration.php";

if ($_SESSION['roli'] !== 'prind') {
    header("Location: login.php");
    exit();
}

$prind_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_word'])) {
    $fjala = strtolower(trim($_POST['fjala'] ?? ''));
    $lloji = trim($_POST['lloji'] ?? 'personalizuar');

    if ($fjala !== '') {
        $stmt = $conn->prepare("
            INSERT INTO fjale_ndaluara_prind (prind_id, fjala, lloji)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $prind_id, $fjala, $lloji);
        $stmt->execute();
    }

    header("Location: p_forbidden_words.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM fjale_ndaluara_prind 
        WHERE id=? AND prind_id=?
    ");
    $stmt->bind_param("ii", $id, $prind_id);
    $stmt->execute();

    header("Location: p_forbidden_words.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT * 
    FROM fjale_ndaluara_prind 
    WHERE prind_id=? 
    ORDER BY data_krijimit DESC
");
$stmt->bind_param("i", $prind_id);
$stmt->execute();
$words = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Fjalët e ndaluara</title>
    <link rel="stylesheet" href="assets/parent.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="parent-ui">

<div class="center-page">
    <div class="form-box">
        <h2>Fjalët e ndaluara</h2>
        <p class="muted">
            Këtu prindi mund të shtojë fjalë të reja që sistemi do t'i monitorojë gjatë komunikimit të fëmijës.
        </p>

        <form method="post">
            <div class="form-row">
                <label>Fjala</label>
                <input type="text" name="fjala" required placeholder="p.sh. injorant">
            </div>

            <div class="form-row">
                <label>Lloji</label>
                <select name="lloji">
                    <option value="ofendim">Ofendim</option>
                    <option value="kërcënim">Kërcënim</option>
                    <option value="dhunë">Dhunë</option>
                    <option value="gjuhë negative">Gjuhë negative</option>
                    <option value="personalizuar">Personalizuar</option>
                </select>
            </div>

            <button class="btn" type="submit" name="add_word">Shto fjalë</button>
        </form>

        <br>

        <?php if ($words->num_rows == 0): ?>
            <p class="muted">Nuk ka ende fjalë të shtuara.</p>
        <?php else: ?>
            <?php while ($w = $words->fetch_assoc()): ?>
                <div class="word-row">
                    <div>
                        <b><?= htmlspecialchars($w['fjala']) ?></b>
                        <br>
                        <small><?= htmlspecialchars($w['lloji']) ?></small>
                    </div>

                    <a class="btn btn-danger"
                       href="p_forbidden_words.php?delete=<?= (int)$w['id'] ?>"
                       onclick="return confirm('A je e sigurt që do ta fshish?')">
                       Fshi
                    </a>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <div class="bottom-actions">
            <a class="btn btn-light" href="p_dashboard.php">Shko në menu</a>
        </div>
    </div>
</div>

</body>
</html>
