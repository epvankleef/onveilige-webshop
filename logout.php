<?php
require_once 'config.php';

logAction("User logged out: " . ($_SESSION['username'] ?? 'unknown'));

session_destroy();
header("Location: index.php?message=" . urlencode("Je bent uitgelogd!"));
exit();
?>