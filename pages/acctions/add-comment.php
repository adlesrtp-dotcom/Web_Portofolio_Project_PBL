<?php
session_start();
require_once '../includes/config.php';

if ($_SESSION['role'] !== 'dosen') die("Akses ditolak.");

if (!$pdo) die("Database connection failed.");

$project_id = (int) $_POST['project_id'];
$comment = trim($_POST['comment']);
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;

$stmt = $pdo->prepare("INSERT INTO comments (project_id, user_id, comment, rating) VALUES (?, ?, ?, ?)");
$stmt->execute([$project_id, $_SESSION['user_id'], $comment, $rating]);

header("Location: ../pages/project-view.php?id=$project_id");
exit;
?>