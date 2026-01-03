<?php
require_once __DIR__ . '/../includes/config.php';

// Ambil proyek publik, urut terbaru
if (!$pdo) {
    die('Database connection failed');
}
$stmt = $pdo->prepare("
    SELECT p.*, u.name as author_name, u.nim
    FROM projects p
    JOIN users u ON p.user_id = u.id
    WHERE p.is_public = 1
    ORDER BY p.created_at DESC
");
$stmt->execute();
$projects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Showcase — PBL PORTOFOLIO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: { colors: { primary: '#1e40af' } }
      }
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-gray-50">
  <!-- Navbar -->
  <nav class="bg-white shadow-md fixed w-full z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <img src="../public/img/logo.png" alt="Logo Polibatam" class="h-10 w-auto mr-2">
          <span class="text-primary text-xl font-bold">PBL <span class="text-gray-800">PORTOFOLIO</span></span>
        </div>
        <div class="hidden md:flex items-center space-x-6">
          <a href="../index.php" class="text-gray-700 hover:text-primary font-medium">Beranda</a>
          <a href="showcase.php" class="text-primary font-medium border-b-2 border-primary">Showcase</a>
          <a href="../auth/login/login.php" class="bg-primary text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700">Masuk</a>
        </div>
        <button id="mobile-menu-button" class="md:hidden text-gray-600">
          <i class="fas fa-bars text-xl"></i>
        </button>
      </div>
    </div>
    <div id="mobile-menu" class="md:hidden hidden bg-white shadow-md">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <a href="/../index.php" class="block px-3 py-2 text-gray-700">Beranda</a>
        <a href="showcase/showcase.php" class="block px-3 py-2 text-primary font-medium">Showcase</a>
        <a href="../auth/login/login.php" class="block px-3 py-2 bg-primary text-white rounded-md text-center">Masuk</a>
      </div>
    </div>
  </nav>

  <!-- Header -->
  <header class="pt-20 text-center py-12 bg-gradient-to-r from-primary to-blue-700 text-white">
    <h1 class="text-3xl md:text-4xl font-bold">Showcase Proyek PBL</h1>
    <p class="mt-4 text-blue-100 max-w-2xl mx-auto">
      Kumpulan portofolio publik mahasiswa Polibatam. Setiap proyek mencerminkan pembelajaran berbasis proyek nyata.
    </p>
  </header>

  <!-- Konten -->
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if (empty($projects)): ?>
      <div class="bg-white rounded-xl shadow text-center py-16">
        <i class="fas fa-robot text-5xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-800">Belum ada proyek yang dipublikasikan</h3>
        <p class="text-gray-600 mt-2">
          Mahasiswa belum mempublikasikan portofolio mereka.
        </p>
        <a href="auth/register/register.html" class="mt-4 inline-block bg-primary text-white px-5 py-2 rounded-lg font-medium">
          Daftar & Mulai Proyek
        </a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($projects as $p): ?>
          <div class="bg-white rounded-xl shadow overflow-hidden hover:shadow-lg transition">
            <?php if ($p['screenshot']): ?>
              <div class="h-48 overflow-hidden">
                <img src="../uploads/screenshots/<?= htmlspecialchars($p['screenshot']) ?>" 
                     alt="<?= htmlspecialchars($p['title']) ?>" 
                     class="w-full h-full object-cover">
              </div>
            <?php else: ?>
              <div class="h-48 bg-gray-100 flex items-center justify-center">
                <i class="fas fa-image text-gray-400 text-4xl"></i>
              </div>
            <?php endif; ?>

            <div class="p-5">
              <h2 class="font-bold text-xl text-gray-800"><?= htmlspecialchars($p['title']) ?></h2>
              <p class="text-sm text-gray-500 mt-1">
                Oleh: <span class="font-medium"><?= htmlspecialchars($p['author_name']) ?></span>
                <?php if ($p['nim']): ?> (<?= htmlspecialchars($p['nim']) ?>) <?php endif; ?>
              </p>
              <p class="text-sm text-gray-600 mt-1">
                <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded"><?= htmlspecialchars($p['course']) ?></span>
                <span class="text-gray-400 mx-1">•</span>
                <?= htmlspecialchars($p['semester']) ?>
              </p>

              <p class="mt-3 text-gray-700 line-clamp-3">
                <?= htmlspecialchars(substr($p['description'], 0, 120)) ?>...
              </p>

              <div class="mt-4 flex justify-between items-center">
                <span class="text-xs text-gray-500">
                  <i class="far fa-clock mr-1"></i>
                  <?= date('d M Y', strtotime($p['created_at'])) ?>
                </span>
                <a href="../pages/view-project.php?id=<?= $p['id'] ?>" 
                   class="text-primary font-medium hover:underline flex items-center">
                  Lihat Detail <i class="fas fa-arrow-right ml-1 text-sm"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <footer class="bg-gray-800 text-white py-8">
    <div class="max-w-7xl mx-auto px-4 text-center">
      <p>&copy; 2026 Politeknik Negeri Batam. All rights reserved.</p>
    </div>
  </footer>

  <script>
    const btn = document.getElementById("mobile-menu-button");
    const menu = document.getElementById("mobile-menu");
    btn.addEventListener("click", () => menu.classList.toggle("hidden"));
    document.addEventListener("click", e => {
      if (!btn.contains(e.target) && !menu.contains(e.target)) menu.classList.add("hidden");
    });
  </script>
</body>
</html>