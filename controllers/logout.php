<?php
require_once '../config/database.php';
startSessionSafely();
session_destroy();
header('Location: ../views/login.php');
exit();
?>