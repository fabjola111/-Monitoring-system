<?php
session_start();

require_once "configuration.php";
require_once "mailer.php";

if (!isset($_POST['signup'])) {
    header("Location: signup.php");
    exit;
}

$emri = trim($_POST['emri'] ?? '');
$mbiemri = trim($_POST['mbiemri'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm = trim($_POST['confirm_pass'] ?? '');
$roli = trim($_POST['roli'] ?? '');

if (
    empty($emri) ||
    empty($mbiemri) ||
    empty($email) ||
    empty($password) ||
    empty($confirm) ||
    empty($roli)
) {
    die("Ju lutem plotësoni të gjitha të dhënat!");
}


if (!preg_match('/^[a-zA-ZëËçÇ\s]{2,50}$/u', $emri)) {
    showMessage("Emri nuk është i vlefshëm.");
}

if (!preg_match('/^[a-zA-ZëËçÇ\s]{2,50}$/u', $mbiemri)) {
    showMessage("Mbiemri nuk është i vlefshëm.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    showMessage("Email-i nuk është i vlefshëm.");
}

if (strlen($password) < 8) {
    $_SESSION['error'] = "Fjalëkalimi duhet të ketë të paktën 8 karaktere.";
header("Location: signup.php");
exit;
}

if ($password !== $confirm) {
    showMessage("Konfirmimi nuk është i njëjtë me fjalëkalimin!");
}

if ($roli !== "prind" && $roli !== "femije") {
   showMessage("Roli nuk është i vlefshëm.");
}

$stmtCheck = $conn->prepare("SELECT id FROM perdorues WHERE email=? LIMIT 1");
$stmtCheck->bind_param("s", $email);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();

if ($resCheck->num_rows > 0) {
    $_SESSION['error'] = "Ky email ekziston tashmë. Përdorni një tjetër.";
header("Location: signup.php");
exit;
}

$prindi_id = NULL;
$ditelindja = NULL;

if ($roli === "femije") {
    $prindi_id = (int)($_POST['prind_id'] ?? 0);
    $ditelindja = trim($_POST['ditelindja'] ?? '');

    if ($prindi_id <= 0) {
       showMessage("Fëmija duhet të vendosë ID-në e prindit për t’u regjistruar!");
    }

    if (empty($ditelindja)) {
       showMessage("Fëmija duhet të vendosë datëlindjen.");
    }

    $birthDate = DateTime::createFromFormat('Y-m-d', $ditelindja);
    $errors = DateTime::getLastErrors();

    if (!$birthDate || $errors['warning_count'] > 0 || $errors['error_count'] > 0) {
        showMessage("Datëlindja nuk është e vlefshme.");
    }

    $today = new DateTime();

    if ($birthDate > $today) {
        showMessage("Datëlindja nuk mund të jetë në të ardhmen.");
    }

    $age = $today->diff($birthDate)->y;

    if ($age > 15) {
        showMessage("Ky program mund të përdoret vetëm nga fëmijët deri në 15 vjeç.");
    }

    $stmt = $conn->prepare("SELECT id FROM perdorues WHERE id=? AND roli='prind'");
    $stmt->bind_param("i", $prindi_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 0) {
        showMessage("Prindi nuk ekziston. Kontrolloni ID-në e prindit!");
    }
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "
    INSERT INTO perdorues 
    (emri, mbiemri, email, password, roli, prind_id, ditelindja) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssis",
    $emri,
    $mbiemri,
    $email,
    $password_hash,
    $roli,
    $prindi_id,
    $ditelindja
);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;

    $code = str_pad((string)random_int(0, 999999), 6, "0", STR_PAD_LEFT);
    $code_hash = hash('sha256', $code);
    $expires_at = date('Y-m-d H:i:s', time() + 600);

    $stmt2 = $conn->prepare("
        INSERT INTO email_verifications 
        (user_id, code_hash, expires_at) 
        VALUES (?, ?, ?)
    ");
    $stmt2->bind_param("iss", $user_id, $code_hash, $expires_at);
    $stmt2->execute();

    $subject = "Verifikimi i Emailit";
    $body = "
        <p>Kodi juaj i verifikimit është: <b>$code</b></p>
        <p>Skadon pas 10 minutash.</p>
    ";

    $mailResult = sendMail($email, $subject, $body);

    if ($mailResult !== true) {
     showMessage("Kodi nuk u dërgua: " . $mailResult);
    }

    $_SESSION['PENDING_EMAIL_VERIFY_USER_ID'] = $user_id;
    $_SESSION['PENDING_EMAIL_VERIFY_EMAIL'] = $email;

    header("Location: verify_email.php");
    exit;
} else {
    showMessage("Gabim gjatë regjistrimit: " . $conn->error);
}
?>