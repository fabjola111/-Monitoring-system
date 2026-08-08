<?php
include("auth.php");
require_once "configuration.php";

if ($_SESSION['roli'] !== 'prind') {
    header("Location: login.php");
    exit();
}

$prind_id = (int)$_SESSION['user_id'];
$child_id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT id, emri, mbiemri, email, foto_profil 
    FROM perdorues 
    WHERE id=? AND prind_id=? AND roli='femije'
");
$stmt->bind_param("ii", $child_id, $prind_id);
$stmt->execute();
$child = $stmt->get_result()->fetch_assoc();

if (!$child) {
    die("Fëmija nuk u gjet ose nuk i përket profilit tuaj.");
}

$alerts = $conn->prepare("
    SELECT 
        a.*,
        m.dergues_id,
        m.marres_id,
        m.teksti,
        m.status,
        m.score,
        m.fjale_ndaluara,
        m.lloje_fjalesh,
        m.data_krijimit AS data_mesazhit,
        d.emri AS dergues_emri,
        d.mbiemri AS dergues_mbiemri,
        r.emri AS marres_emri,
        r.mbiemri AS marres_mbiemri
    FROM alarme a
    JOIN mesazhe m ON a.mesazh_id = m.id
    LEFT JOIN perdorues d ON d.id = m.dergues_id
    LEFT JOIN perdorues r ON r.id = m.marres_id
    WHERE a.femije_id = ?
      AND a.prind_id = ?
    ORDER BY a.data_krijimit DESC
    LIMIT 50
");
$alerts->bind_param("ii", $child_id, $prind_id);
$alerts->execute();
$alertsRes = $alerts->get_result();

$daily = $conn->prepare("
    SELECT 
        DATE(data_krijimit) AS dita,
        COUNT(*) AS total,
        SUM(status != 'normal') AS dyshimta,
        AVG(score) AS avg_score
    FROM mesazhe
    WHERE dergues_id = ? OR marres_id = ?
    GROUP BY DATE(data_krijimit)
    ORDER BY dita ASC
    LIMIT 14
");
$daily->bind_param("ii", $child_id, $child_id);
$daily->execute();
$dailyRes = $daily->get_result();

$labels = [];
$totals = [];
$sus = [];
$scores = [];

while ($r = $dailyRes->fetch_assoc()) {
    $labels[] = $r['dita'];
    $totals[] = (int)$r['total'];
    $sus[] = (int)$r['dyshimta'];
    $scores[] = round((float)$r['avg_score'], 1);
}

$typeQ = $conn->prepare("
    SELECT fjale_ndaluara, lloje_fjalesh
    FROM mesazhe 
    WHERE (dergues_id = ? OR marres_id = ?)
      AND status != 'normal'
");
$typeQ->bind_param("ii", $child_id, $child_id);
$typeQ->execute();
$typeRes = $typeQ->get_result();

$types = [];
$topWords = [];

while ($r = $typeRes->fetch_assoc()) {
    $fjale = trim($r['fjale_ndaluara'] ?? '');
    $lloji = trim($r['lloje_fjalesh'] ?? '');

    if ($fjale !== '') {
        $fjaleArray = explode(',', $fjale);
        $llojiArray = explode(',', $lloji);

        foreach ($fjaleArray as $index => $f) {
            $f = trim($f);

            if ($f !== '') {
                $topWords[$f] = ($topWords[$f] ?? 0) + 1;

                $llojiPerFjale = trim($llojiArray[$index] ?? $lloji);
                $label = $f;

                if ($llojiPerFjale !== '') {
                    $label .= " (" . $llojiPerFjale . ")";
                }

                $types[$label] = ($types[$label] ?? 0) + 1;
            }
        }
    }
}

arsort($topWords);
$topWords = array_slice($topWords, 0, 10, true);

$riskQ = $conn->prepare("
    SELECT COUNT(*) AS total_alerts
    FROM alarme
    WHERE femije_id = ? AND prind_id = ?
");
$riskQ->bind_param("ii", $child_id, $prind_id);
$riskQ->execute();
$riskRow = $riskQ->get_result()->fetch_assoc();

$totalAlerts = (int)($riskRow['total_alerts'] ?? 0);

if ($totalAlerts <= 5) {
    $riskLevel = "LOW";
    $riskClass = "risk-low";
} elseif ($totalAlerts <= 10) {
    $riskLevel = "MEDIUM";
    $riskClass = "risk-medium";
} else {
    $riskLevel = "HIGH";
    $riskClass = "risk-high";
}

$foto = !empty($child['foto_profil']) ? $child['foto_profil'] : 'uploads/default.png';
?>

<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <title>Raporti i Fëmijës</title>
    <link rel="stylesheet" href="assets/parent.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="parent-ui">

<div class="parent-page">

    <div class="child-profile-header">
        <img 
            class="avatar" 
            src="<?= htmlspecialchars($foto) ?>" 
            onerror="this.onerror=null; this.src='uploads/default.png';" 
            alt="Foto"
        >

        <div>
            <h2><?= htmlspecialchars($child['emri'] . ' ' . $child['mbiemri']) ?></h2>
            <p><?= htmlspecialchars($child['email']) ?></p>

            <div class="risk-box <?= $riskClass ?>">
                Niveli i rrezikut: <?= htmlspecialchars($riskLevel) ?>
                — Total alerte: <?= (int)$totalAlerts ?>
            </div>
        </div>
    </div>

    <div class="report-grid">
        <div class="chart-box">
            <canvas id="activity"></canvas>
        </div>

        <div class="chart-box">
            <canvas id="types"></canvas>
        </div>
    </div>

    <div class="glass-card">
        <h3 class="section-title">Top 10 fjalët më të përdorura</h3>

        <?php if (empty($topWords)): ?>
            <p class="muted">Nuk ka ende fjalë të ndaluara të përdorura.</p>
        <?php else: ?>
            <?php foreach ($topWords as $word => $count): ?>
                <div class="top-word">
                    <b><?= htmlspecialchars($word) ?></b>
                    — përdorur <?= (int)$count ?> herë
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <br>

    <div class="glass-card">
        <h3 class="section-title">Alertet dhe fjalët e ndaluara</h3>

        <?php if ($alertsRes->num_rows == 0): ?>
            <p class="muted">Nuk ka alerte për këtë fëmijë.</p>
        <?php endif; ?>

        <?php while ($a = $alertsRes->fetch_assoc()): ?>
            <?php
                $childSent = ((int)$a['dergues_id'] === $child_id);

                $directionText = $childSent
                    ? "Fëmija e DËRGOI këtë mesazh"
                    : "Fëmijës iu DËRGUA ky mesazh";

                $otherName = $childSent
                    ? trim(($a['marres_emri'] ?? '') . ' ' . ($a['marres_mbiemri'] ?? ''))
                    : trim(($a['dergues_emri'] ?? '') . ' ' . ($a['dergues_mbiemri'] ?? ''));

                $badgeClass = $childSent ? "badge-sent" : "badge-received";
                $alertClass = $childSent ? "sent" : "received";
            ?>

            <div class="alert <?= $alertClass ?>">
                <span class="badge <?= $badgeClass ?>">
                    <?= htmlspecialchars($directionText) ?>
                </span>

                <br><br>

                <b><?= $childSent ? "Marrësi" : "Dërguesi" ?>:</b>
                <?= htmlspecialchars($otherName ?: "-") ?>

                <br>

                <b>Niveli:</b> <?= htmlspecialchars($a['niveli']) ?>
                |
                <b>Status:</b> <?= htmlspecialchars($a['status']) ?>
                |
                <b>Score:</b> <?= (int)$a['score'] ?>

                <br>

                <b>Ofendimi / fjala e ndaluar:</b>
                <?= htmlspecialchars($a['fjale_ndaluara'] ?: "-") ?>

                <br>

                <b>Lloji:</b>
                <?= htmlspecialchars($a['lloje_fjalesh'] ?: "-") ?>

                <br>

                <b>Përshkrimi:</b>
                <?= htmlspecialchars($a['pershkrimi'] ?: "-") ?>

                <div class="message-text">
                    <b>Mesazhi:</b>
                    <?= htmlspecialchars($a['teksti']) ?>
                </div>

                <small class="muted">
                    Data e mesazhit:
                    <?= htmlspecialchars($a['data_mesazhit']) ?>
                </small>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="bottom-actions">
        <a class="btn btn-light" href="p_dashboard.php">Shko në menu</a>
    </div>

</div>

<script>
function prepareCanvas(canvasId) {
    const c = document.getElementById(canvasId);
    c.width = 520;
    c.height = 260;
    return c;
}

function drawActivityBars(canvasId, labels, total, suspect) {
    const c = prepareCanvas(canvasId);
    const x = c.getContext('2d');

    x.clearRect(0, 0, c.width, c.height);
    x.font = '14px Arial';
    x.fillStyle = '#1e3a8a';

    x.fillText('Vijueshmëria e komunikimit (14 ditët e fundit)', 20, 25);

    if (labels.length === 0) {
        x.fillStyle = '#6b7280';
        x.fillText('Nuk ka ende mesazhe për këtë fëmijë.', 20, 70);
        return;
    }

    let totalMesazhe = 0;
    let totalAlert = 0;

    total.forEach(v => totalMesazhe += Number(v));
    suspect.forEach(v => totalAlert += Number(v));

    const max = Math.max(1, totalMesazhe, totalAlert);
    const chartBottom = 200;
    const chartTop = 55;
    const chartHeight = chartBottom - chartTop;

    const barWidth = 70;
    const totalX = 120;
    const alertX = 330;

    const totalHeight = (totalMesazhe / max) * chartHeight;
    const alertHeight = (totalAlert / max) * chartHeight;

    const totalY = chartBottom - totalHeight;
    const alertY = chartBottom - alertHeight;

    x.fillStyle = '#60a5fa';
    x.fillRect(totalX, totalY, barWidth, totalHeight);
    x.fillStyle = '#1e3a8a';
    x.fillText(totalMesazhe, totalX + 28, totalY - 8);
    x.fillText('Total mesazhe', totalX - 10, 230);

    x.fillStyle = '#fb7185';
    x.fillRect(alertX, alertY, barWidth, alertHeight);
    x.fillStyle = '#1e3a8a';
    x.fillText(totalAlert, alertX + 28, alertY - 8);
    x.fillText('Mesazhe me alert', alertX - 25, 230);

    x.fillStyle = '#6b7280';
    x.fillText('Periudha: 14 ditët e fundit', 160, 252);
}

function getTypeColor(label) {
    const l = label.toLowerCase();

    if (l.includes('kërcënim') || l.includes('kercenim')) {
        return '#ef4444';
    }

    if (l.includes('dhunë') || l.includes('dhune')) {
        return '#f97316';
    }

    if (l.includes('gjuhë negative') || l.includes('gjuhe negative')) {
        return '#a855f7';
    }

    if (l.includes('ofendim')) {
        return '#38bdf8';
    }

    return '#60a5fa';
}

function drawBars(canvasId, data) {
    const c = prepareCanvas(canvasId);
    const x = c.getContext('2d');

    x.clearRect(0, 0, c.width, c.height);
    x.font = '14px Arial';
    x.fillStyle = '#1e3a8a';

    x.fillText('Fjalët/ofendimet e përdorura', 20, 25);

    let keys = Object.keys(data);
    let values = Object.values(data);
    let max = Math.max(1, ...values);

    if (keys.length === 0) {
        x.fillStyle = '#6b7280';
        x.fillText('Nuk ka të dhëna', 20, 65);
        return;
    }

    keys.forEach((k, i) => {
        let y = 55 + i * 35;

        x.fillStyle = '#1f2937';
        x.fillText(k, 20, y);

        x.fillStyle = getTypeColor(k);
        x.fillRect(220, y - 14, (data[k] / max) * 230, 20);

        x.fillStyle = '#1f2937';
        x.fillText(data[k], 465, y);
    });
}

drawActivityBars(
    'activity',
    <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>,
    <?= json_encode($totals, JSON_UNESCAPED_UNICODE) ?>,
    <?= json_encode($sus, JSON_UNESCAPED_UNICODE) ?>
);

drawBars(
    'types',
    <?= json_encode($types, JSON_UNESCAPED_UNICODE) ?>
);
</script>

</body>
</html>
