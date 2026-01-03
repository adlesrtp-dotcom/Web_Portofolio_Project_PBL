<?php
session_start();
$message = '';
$success = false;

if ($_POST) {
    // ⚠️ Ini hanya contoh — belum ada logika kirim email
    // Nanti bisa pakai PHPMailer + generate token reset di DB
    $email = trim($_POST['email']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // ✅ Contoh: cek apakah email ada di DB (opsional)
        // include '../includes/config.php';
        // $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        // $stmt->execute([$email]);
        // if (!$stmt->fetch()) {
        //     $message = "Email tidak terdaftar.";
        // } else {
            $success = true;
            $message = "Link reset kata sandi telah dikirim ke <strong>" . htmlspecialchars($email) . "</strong>. Cek folder inbox/spam Anda.";
        // }
    } else {
        $message = "Format email tidak valid.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lupa Kata Sandi — Portofolio PBL</title>
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
      <h1 class="text-2xl font-semibold">Lupa Kata Sandi?</h1>
      <p class="text-blue-100 mt-1">Kami akan bantu Anda</p>
    </div>

    <!-- Form / Pesan -->
    <div class="p-6 space-y-5">
      <?php if ($message): ?>
        <div class="<?= $success ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' ?> px-4 py-3 rounded-lg text-sm flex items-start">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
            <?php if ($success): ?>
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            <?php else: ?>
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            <?php endif; ?>
          </svg>
          <span><?= $message ?></span>
        </div>
      <?php endif; ?>

      <?php if (!$success): ?>
        <p class="text-gray-600 text-center">
          Masukkan email yang terdaftar. Kami akan kirim link untuk reset kata sandi Anda.
        </p>

        <form method="POST" class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Terdaftar</label>
            <input
              type="email"
              id="email"
              name="email"
              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
              placeholder="nama@polibatam.ac.id"
              required
            />
          </div>

          <button
            type="submit"
            class="w-full bg-primary hover:bg-blue-700 text-white font-medium py-3 rounded-lg transition shadow-md hover:shadow-lg"
          >
            Kirim Link Reset
          </button>
        </form>
      <?php endif; ?>

      <div class="text-center pt-2">
        <a href="../login/login.php" class="inline-flex items-center text-primary font-medium hover:underline">
          ← Kembali ke Login
        </a>
      </div>
    </div>
  </div>
</body>
</html>