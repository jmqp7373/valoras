<?php
require_once __DIR__ . '/../../config/database.php';
startSessionSafely();
session_destroy();
header('Location: ../../views/login/login.php');
exit();
?>