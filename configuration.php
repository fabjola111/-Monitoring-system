<?php
/*

$host = "sql103.infinityfree.com";
$user = "......";
$pass = "......";
$db   = ".......";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Lidhja dështoi: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
*/

$host = "localhost";
$user = "root";
$pass = "";
$db   = "monitor_tst";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Lidhja dështoi: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function showMessage($message, $back = "javascript:history.back()", $type = "error") {
    $_SESSION['message'] = $message;
    $_SESSION['back'] = $back;
    $_SESSION['type'] = $type;

    header("Location: message.php");
    exit;
}
?>
