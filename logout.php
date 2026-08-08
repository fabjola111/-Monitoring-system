<?php
session_start();

$_SESSION = [];

setcookie("remember_user_id", "", time() - 3600, "/");
setcookie("remember_user_role", "", time() - 3600, "/");

session_destroy();

header("Location: login.php");
exit;
?>