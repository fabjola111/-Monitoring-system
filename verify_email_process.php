<?php
session_start();

require_once "configuration.php";
require_once "mailer.php";

if (!isset($_SESSION['PENDING_EMAIL_VERIFY_USER_ID'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['PENDING_EMAIL_VERIFY_USER_ID'];
$email = $_SESSION['PENDING_EMAIL_VERIFY_EMAIL'] ?? '';

function dergoKodTeRi(mysqli $conn, int $user_id, string $email) {
    $code = str_pad((string)random_int(0, 999999), 6, "0", STR_PAD_LEFT);
    $code_hash = hash('sha256', $code);
    $expires_at = date('Y-m-d H:i:s', time() + 600);

    $stmt = $conn->prepare("
        INSERT INTO email_verifications 
        (user_id, code_hash, expires_at) 
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("iss", $user_id, $code_hash, $expires_at);
    $stmt->execute();

    $subject = "Verifikimi i Emailit";
    $body = "
        <p>Kodi juaj i verifikimit është: <b>$code</b></p>
        <p>Skadon pas 10 minutash.</p>
    ";

    return sendMail($email, $subject, $body);
}

if (isset($_POST['resend'])) {

    if (empty($email)) {
        $_SESSION['error'] = "Email nuk u gjet. Regjistrohu përsëri.";
        header("Location: signup.php");
        exit;
    }

    $result = dergoKodTeRi($conn, $user_id, $email);

    if ($result === true) {
        $_SESSION['success'] = "Kodi u ridërgua në email.";
    } else {
        $_SESSION['verify_error'] = "Kodi nuk u dërgua: " . $result;
    }

    header("Location: verify_email.php");
    exit;
}

if (!isset($_POST['verify'])) {
    header("Location: verify_email.php");
    exit;
}

$code = trim($_POST['code'] ?? '');

if ($code === '') {
    $_SESSION['verify_error'] = "Shkruaj kodin.";
    header("Location: verify_email.php");
    exit;
}

$code_hash = hash('sha256', $code);

$stmt = $conn->prepare("
    SELECT id, code_hash, expires_at
    FROM email_verifications
    WHERE user_id=?
    ORDER BY id DESC
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows !== 1) {
    $_SESSION['verify_error'] = "Kodi nuk u gjet. Shtyp Ridërgo kodin.";
    header("Location: verify_email.php");
    exit;
}

$row = $res->fetch_assoc();

if (strtotime($row['expires_at']) < time()) {
    $_SESSION['verify_error'] = "Kodi ka skaduar. Shtyp Ridërgo kodin.";
    header("Location: verify_email.php");
    exit;
}

if (!hash_equals($row['code_hash'], $code_hash)) {
    $_SESSION['verify_error'] = "Kodi është gabim.";
    header("Location: verify_email.php");
    exit;
}

$up = $conn->prepare("UPDATE perdorues SET is_email_verified=1 WHERE id=?");
$up->bind_param("i", $user_id);
$up->execute();

$del = $conn->prepare("DELETE FROM email_verifications WHERE user_id=?");
$del->bind_param("i", $user_id);
$del->execute();

unset($_SESSION['PENDING_EMAIL_VERIFY_USER_ID']);
unset($_SESSION['PENDING_EMAIL_VERIFY_EMAIL']);

$_SESSION['success'] = "Emaili u verifikua me sukses. Tani mund të logohesh.";
header("Location: login.php");
exit;
?>