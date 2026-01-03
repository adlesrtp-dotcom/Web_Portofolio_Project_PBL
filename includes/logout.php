<?php
session_start();
session_destroy();
header("Location: ../auth/login/login.php");
exit;
?>