<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login/login.php");
    exit;
}

if (!$pdo) {
    $_SESSION['error'] = "Koneksi database gagal.";
    header("Location: dashboard.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    $_SESSION['error'] = "ID proyek tidak valid.";
    header("Location: dashboard.php");
    exit;
}

// Pastikan hanya pemilik yang bisa hapus
$stmt = $pdo->prepare("SELECT user_id FROM projects WHERE id = ?");
$stmt->execute([$id]);
$owner_id = $stmt->fetchColumn();

if (!$owner_id || $owner_id != $_SESSION['user_id']) {
    $_SESSION['error'] = "Akses ditolak.";
    header("Location: dashboard.php");
    exit;
}

// Hapus file screenshot jika ada
$stmt = $pdo->prepare("SELECT screenshot FROM projects WHERE id = ?");
$stmt->execute([$id]);
$screenshot = $stmt->fetchColumn();
if ($screenshot && file_exists(__DIR__ . "/../uploads/screenshots/$screenshot")) {
    unlink(__DIR__ . "/../uploads/screenshots/$screenshot");
}

// Hapus dari DB
$stmt = $pdo->prepare("DELETE FROM comments WHERE project_id = ?");
$stmt->execute([$id]);

$stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['success'] = "Proyek berhasil dihapus.";
header("Location: dashboard.php");
exit;
?>