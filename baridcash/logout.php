<?php
// ===== logout.php =====
session_start();
$_SESSION = [];      // vider les données de session
session_destroy();   // détruire la session
header("Location: login.php");
exit();
?>


























