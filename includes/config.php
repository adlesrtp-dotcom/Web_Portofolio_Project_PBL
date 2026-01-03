<?php
// File: includes/config.php
$host = 'localhost';
$dbname = 'pbl_portofolio';
$username = 'root';
$password = '';

$pdo = null;
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    $pdo = null;
}