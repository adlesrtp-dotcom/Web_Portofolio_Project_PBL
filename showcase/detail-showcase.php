<?php
require_once __DIR__ . '/../includes/config.php';

if (!isset($pdo) || $pdo === null) {
    http_response_code(500);
    die("Database connection error.");
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    http_response_code(400);
    die("ID proyek tidak valid.");
}

// Ambil proyek publik
$stmt = $pdo->prepare("
    SELECT p.*, u.name as author_name, u.nim
    FROM projects p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ? AND p.is_public = 1
");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    die("Proyek tidak ditemukan atau tidak dipublikasikan.");
}

// Ambil komentar dosen
$stmt = $pdo->prepare("
    SELECT c.*, du.name as dosen_name, du.role as dosen_role
    FROM comments c
    JOIN users du ON c.user_id = du.id AND du.role = 'dosen'
    WHERE c.project_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$id]);
$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($project['title']) ?> — Showcase PBL</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { theme: { extend: { colors: { primary: '#1e40af' } } } }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Poppins', sans-serif; }
    .embed-responsive { position: relative; overflow: hidden; padding-top: 56.25%; }
    .embed-responsive iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
  </style>
</head>
<body class="bg-gray-50">

  <!-- Navbar -->
  <nav class="bg-white shadow-md fixed w-full z-10">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center">
      <a href="index.php" class="flex items-center">
        <img src="public/img/logo.png" alt="Logo Polibatam" class="h-8 w-auto mr-2">
        <span class="text-primary font-bold">PBL PORTOFOLIO</span>
      </a>
      <div class="ml-auto hidden md:flex space-x-4">
        <a href="index.php" class="text-gray-700 hover:text-primary font-medium">Beranda</a>
        <a href="showcase.php" class="text-primary font-medium border-b-2 border-primary">Showcase</a>
        <a href="auth/login/login.php" class="bg-primary text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700">Masuk</a>
      </div>
    </div>
  </nav>

  <!-- Header -->
  <header class="pt-20 bg-gradient-to-r from-primary to-blue-700 text-white">
    <div class="max-w-7xl mx-auto px-4 py-12">
      <div class="flex flex-col md:flex-row md:items-end justify-between">
        <div>
          <h1 class="text-3xl md:text-4xl font-bold"><?= htmlspecialchars($project['title']) ?></h1>
          <p class="mt-2 text-blue-100">
            Oleh: <span class="font-medium"><?= htmlspecialchars($project['author_name']) ?></span>
            <?php if ($project['nim']): ?> · <?= htmlspecialchars($project['nim']) ?><?php endif; ?>
            • <?= htmlspecialchars($project['course']) ?> — <?= htmlspecialchars($project['semester']) ?>
          </p>
        </div>
        <a href="showcase.php" class="mt-4 md:mt-0 inline-flex items-center text-white hover:underline">
          ← Kembali ke Showcase
        </a>
      </div>
    </div>
  </header>

  <!-- Konten Utama -->
  <main class="max-w-7xl mx-auto px-4 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2">
        <!-- Deskripsi -->
        <section class="bg-white rounded-xl shadow p-6 mb-8">
          <h2 class="text-xl font-semibold text-gray-800 mb-3">Deskripsi</h2>
          <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
        </section>

        <!-- Screenshot -->
        <?php if ($project['screenshot']): ?>
          <section class="bg-white rounded-xl shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-3">Tangkapan Layar</h2>
            <div class="overflow-hidden rounded-lg border">
              <img src="uploads/screenshots/<?= htmlspecialchars($project['screenshot']) ?>" 
                   alt="Screenshot <?= htmlspecialchars($project['title']) ?>" 
                   class="w-full h-auto">
            </div>
          </section>
        <?php endif; ?>

        <!-- Video Demo -->
        <?php if ($project['video_embed']): 
          $url = trim($project['video_embed']);
          $embed = '';
          if (strpos($url, 'youtu.be/') !== false) {
              $vid = substr(parse_url($url, PHP_URL_PATH), 1);
              $embed = "https://www.youtube.com/embed/$vid";
          } elseif (strpos($url, 'youtube.com/watch') !== false) {
              parse_str(parse_url($url, PHP_URL_QUERY), $params);
              $vid = $params['v'] ?? '';
              $embed = $vid ? "https://www.youtube.com/embed/$vid" : '';
          } elseif (strpos($url, 'vimeo.com/') !== false) {
              $vid = basename(parse_url($url, PHP_URL_PATH));
              $embed = "https://player.vimeo.com/video/$vid";
          } else {
              $embed = $url; 
          }
          if ($embed):
        ?>
          <section class="bg-white rounded-xl shadow p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-3">Video Demo</h2>
            <div class="embed-responsive rounded-lg overflow-hidden shadow">
              <iframe src="<?= htmlspecialchars($embed) ?>" 
                      frameborder="0" 
                      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                      allowfullscreen></iframe>
            </div>
          </section>
        <?php endif; endif; ?>

        <!-- Repository -->
        <?php if ($project['repo_link']): ?>
          <section class="bg-white rounded-xl shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-3">Kode Sumber</h2>
            <a href="<?= htmlspecialchars($project['repo_link']) ?>" 
               target="_blank"
               class="inline-flex items-center bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-lg font-medium">
              <i class="fab fa-github mr-2"></i>
              Lihat di GitHub/GitLab
            </a>
            <p class="mt-2 text-sm text-gray-600">
              Klik tombol di atas untuk mengakses kode sumber proyek.
            </p>
          </section>
        <?php endif; ?>
      </div>

      <!-- Kolom Kanan: Info & Komentar -->
      <div>
        <!-- Info Proyek -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Proyek</h2>
          <div class="space-y-3 text-sm">
            <div>
              <span class="text-gray-500">Mata Kuliah</span>
              <p class="font-medium"><?= htmlspecialchars($project['course']) ?></p>
            </div>
            <div>
              <span class="text-gray-500">Semester</span>
              <p class="font-medium"><?= htmlspecialchars($project['semester']) ?></p>
            </div>
            <div>
              <span class="text-gray-500">Dibuat pada</span>
              <p class="font-medium"><?= date('d M Y', strtotime($project['created_at'])) ?></p>
            </div>
            <?php if ($project['updated_at'] && $project['updated_at'] !== $project['created_at']): ?>
              <div>
                <span class="text-gray-500">Diperbarui</span>
                <p class="font-medium"><?= date('d M Y', strtotime($project['updated_at'])) ?></p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Komentar Dosen -->
        <div class="bg-white rounded-xl shadow p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Komentar & Penilaian</h2>
            <span class="inline-flex items-center text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
              <?= count($comments) ?> komentar
            </span>
          </div>

          <?php if (empty($comments)): ?>
            <p class="text-gray-500 text-center py-4">
              <i class="far fa-comment-slash text-2xl mb-2"></i><br>
              Belum ada komentar dari dosen.
            </p>
          <?php else: ?>
            <div class="space-y-4">
            <?php foreach ($comments as $c): ?>
              <div class="border-l-4 border-primary pl-4 py-2">
                <div class="flex items-center">
                  <span class="font-medium"><?= htmlspecialchars($c['dosen_name']) ?></span>
                  <?php if ($c['rating']): ?>
                    <span class="ml-2 text-yellow-600"><?= str_repeat('★', $c['rating']) ?></span>
                  <?php endif; ?>
                  <span class="ml-auto text-xs text-gray-500"><?= date('d M Y', strtotime($c['created_at'])) ?></span>
                </div>
                <p class="mt-2 text-gray-700"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
              </div>
            <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <footer class="bg-gray-800 text-white py-6">
    <div class="max-w-7xl mx-auto px-4 text-center">
      <p>&copy; 2026 Politeknik Negeri Batam — Portofolio Proyek PBL</p>
    </div>
  </footer>

</body>
</html>