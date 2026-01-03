<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../pages/dashboard.php");
    exit;
}

$message = '';

if ($_POST) {
    require_once __DIR__ . '/../../includes/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $json = json_decode(file_get_contents('php://input'), true);
    if ($json) {
        $_POST = $json;
    }
}

    if (!$pdo) {
        $message = "Gagal terhubung ke database. Hubungi administrator.";
    } else {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
/* if ($user && password_verify($password, $user['password'])) {
    // Debug: Cek data sebelum redirect
    echo "<pre>";
    echo "Login berhasil!\n";
    echo "Session ID: " . session_id() . "\n";
    echo "User ID: " . $user['id'] . "\n";
    echo "User Name: " . $user['name'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    echo "</pre>";
    exit; 
    
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    
    header("Location: ../../pages/dashboard.php");
    exit;
} */
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: ../../pages/dashboard.php");
                exit;
            } else {
                $message = "Email atau kata sandi salah.";
            }
        } catch (Exception $e) {
            $message = "Terjadi kesalahan. Silakan coba lagi.";
        }
        
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — Portofolio PBL</title>
  <!-- Tailwind CSS via CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#1e40af',
            secondary: '#dbeafe',
          }
        }
      }
    }
  </script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
    body { font-family: 'Poppins', sans-serif; }
    .fade-in { animation: fadeIn 0.4s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
  </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden fade-in">
    <!-- Header -->
    <div class="bg-primary text-white text-center py-6 px-6">
      <img src="../../public/img/logo.png" alt="Logo Polibatam" class="mx-auto h-16 w-auto mb-2">
      <h1 class="text-2xl font-semibold">Masuk ke Akun</h1>
      <p class="text-blue-100 mt-1">Portofolio Proyek PBL</p>
    </div>

    <!-- Form -->
    <form method="POST" class="p-6 space-y-5">
      <?php if ($message): ?>
        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm flex items-start">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-blue focus:ring-2 focus:ring-primary focus:border-transparent transition"
          placeholder="nama@polibatam.ac.id"
          required
        />
      </div>

      <!-- Password -->
      <div>
        <div class="flex justify-between items-center mb-1">
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <a href="../lupapassword/lupa_pw.php" class="text-sm text-primary hover:underline">Lupa password?</a>
        </div>
        <input
          type="password"
          id="password"
          name="password"
          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
          required
        />
      </div>

      <!-- Submit -->
      <button
        type="submit"
        class="w-full bg-primary hover:bg-blue-700 text-white font-medium py-3 rounded-lg transition shadow-md hover:shadow-lg"
      >
        Masuk
      </button>

      <div class="text-center text-sm text-gray-600 pt-2 border-t">
        Belum punya akun?  
        <a href="../register/register.html" class="text-primary font-medium hover:underline ml-1">Daftar sekarang</a>
      </div>
    </form>
  </div>
</body>
</html>