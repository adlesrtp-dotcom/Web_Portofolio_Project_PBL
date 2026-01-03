<?php
require_once __DIR__ . './includes/config.php';

if ($pdo) {
    echo "✅ Koneksi database berhasil!<br>";
    
    // Test query users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $result = $stmt->fetch();
    echo "Total users: " . $result['total'];
} else {
    echo "❌ Koneksi database gagal!";
}