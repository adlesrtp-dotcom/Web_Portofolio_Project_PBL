<?php
session_start();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../pages/dashboard.php");
    exit;
}

$message = '';
$messageType = 'error'; // 'success' atau 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../includes/config.php';

    // Pastikan $pdo tersedia & bertipe PDO (hindari "Expected type 'object'. Found 'null'.")
    /** @var PDO|null $pdo */
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        error_log("Register error: PDO is not initialized. Check includes/config.php");
        $message = "Koneksi database belum siap. Silakan coba lagi nanti.";
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? '';
        $nim = trim($_POST['nim'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];

        // Validasi
        if (empty($nama)) $errors[] = "Nama wajib diisi.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email tidak valid.";
        if (empty($role) || !in_array($role, ['dosen', 'mahasiswa'])) $errors[] = "Peran tidak valid.";
        if ($role === 'mahasiswa' && empty($nim)) $errors[] = "NIM wajib diisi untuk mahasiswa.";
        if (strlen($password) < 8) $errors[] = "Password minimal 8 karakter.";

        if (!empty($errors)) {
            $message = implode('<br>', $errors);
        } else {
            try {
                // Cek duplikat email
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $message = "Email sudah terdaftar.";
                } else {
                    // Insert user baru
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        INSERT INTO users (name, email, password, role, nim)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $nama,
                        $email,
                        $hashed,
                        $role,
                        $role === 'mahasiswa' ? $nim : null
                    ]);

                    $messageType = 'success';
                    $message = "Akun berhasil dibuat! Silakan <a href='login/login.php' class='underline font-semibold'>login di sini</a>.";
                }
            } catch (Exception $e) {
                error_log("Register error: " . $e->getMessage());
                $message = "Terjadi kesalahan sistem. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hasil Pendaftaran</title>
  <script src="https://cdn.tailwindcss.com  "></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins  :wght@400;500;600&display=swap');
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-8 text-center">
    <?php if ($message): ?>
      <div class="mb-6">
        <?php if ($messageType === 'success'): ?>
          <svg class="mx-auto h-16 w-16 text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h2 class="text-2xl font-semibold text-gray-800 mb-2">Berhasil!</h2>
          <p class="text-gray-600"><?= $message ?></p>
        <?php else: ?>
          <svg class="mx-auto h-16 w-16 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h2 class="text-2xl font-semibold text-gray-800 mb-2">Gagal</h2>
          <p class="text-red-600"><?= $message ?></p>
          <a href="register/register.html" class="inline-block mt-4 text-blue-600 hover:underline">← Kembali ke form</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>