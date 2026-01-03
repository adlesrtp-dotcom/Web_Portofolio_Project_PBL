<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Guard PDO (hindari "Expected type 'object'. Found 'null'.")
/** @var PDO|null $pdo */
if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    die("Gagal terhubung ke database. Periksa konfigurasi.");
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(404);
    die("Proyek tidak ditemukan.");
}

// Ambil proyek + user
$stmt = $pdo->prepare("
    SELECT p.*, u.name as author_name, u.nim, u.role as author_role
    FROM projects p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    http_response_code(404);
    die("Proyek tidak ditemukan.");
}

// Cek akses: publik/milik sendiri/dosen
$canView = (
    (int)$project['is_public'] === 1
    || (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$project['user_id'])
    || (isset($_SESSION['role']) && $_SESSION['role'] === 'dosen')
);

if (!$canView) {
    http_response_code(403);
    die("Akses ditolak.");
}

// Ambil komentar (hanya dari dosen)
$stmt = $pdo->prepare("
    SELECT c.*, du.name as dosen_name
    FROM comments c
    JOIN users du ON c.user_id = du.id AND du.role = 'dosen'
    WHERE c.project_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tambah komentar (hanya dosen)
$message = '';
$messageType = 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'dosen') {
    $comment = trim($_POST['comment'] ?? '');
    $rating = isset($_POST['rating']) && $_POST['rating'] !== '' ? (int)$_POST['rating'] : null;

    if ($comment === '') {
        $messageType = 'error';
        $message = "Komentar tidak boleh kosong.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (project_id, user_id, comment, rating) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, (int)$_SESSION['user_id'], $comment, $rating]);

            $messageType = 'success';
            $message = "Komentar berhasil dikirim.";

            // Refresh komentar
            $stmt = $pdo->prepare("
                SELECT c.*, du.name as dosen_name
                FROM comments c
                JOIN users du ON c.user_id = du.id AND du.role = 'dosen'
                WHERE c.project_id = ?
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$id]);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Comment insert error: " . $e->getMessage());
            $messageType = 'error';
            $message = "Gagal mengirim komentar.";
        }
    }
}

// Video embed normalization (YouTube/Vimeo/iframe)
$embedSrc = '';
if (!empty($project['video_embed'])) {
    $raw = trim((string)$project['video_embed']);

    // Jika user paste iframe: ambil atribut src
    if (stripos($raw, '<iframe') !== false) {
        if (preg_match('/src\s*=\s*"([^"]+)"/i', $raw, $m) || preg_match("/src\s*=\s*'([^']+)'/i", $raw, $m)) {
            $raw = trim($m[1]);
        }
    }

    if (strpos($raw, 'youtu.be/') !== false) {
        $vid = ltrim((string)parse_url($raw, PHP_URL_PATH), '/');
        if ($vid) $embedSrc = "https://www.youtube.com/embed/" . rawurlencode($vid);
    } elseif (strpos($raw, 'youtube.com/watch') !== false) {
        parse_str((string)parse_url($raw, PHP_URL_QUERY), $params);
        if (!empty($params['v'])) $embedSrc = "https://www.youtube.com/embed/" . rawurlencode($params['v']);
    } elseif (strpos($raw, 'vimeo.com/') !== false) {
        $vid = basename((string)parse_url($raw, PHP_URL_PATH));
        if ($vid) $embedSrc = "https://player.vimeo.com/video/" . rawurlencode($vid);
    } elseif (preg_match('#^https?://#i', $raw)) {
        // fallback: hanya izinkan URL http(s) sebagai src iframe
        $embedSrc = $raw;
    }
}

$isOwner = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$project['user_id'];
$isDosen = (isset($_SESSION['role']) && $_SESSION['role'] === 'dosen');

$visibilityLabel = ((int)$project['is_public'] === 1) ? 'Publik' : 'Privat';
$visibilityClass = ((int)$project['is_public'] === 1)
    ? 'bg-green-50 text-green-700 border-green-200'
    : 'bg-gray-50 text-gray-700 border-gray-200';

$authorRoleLabel = ($project['author_role'] === 'dosen') ? 'Dosen' : 'Mahasiswa';
$authorRoleClass = ($project['author_role'] === 'dosen')
    ? 'bg-purple-50 text-purple-700 border-purple-200'
    : 'bg-blue-50 text-blue-700 border-blue-200';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($project['title']) ?> — Portofolio PBL</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#3b82f6',
            secondary: '#8b5cf6',
            accent: '#06b6d4',
          }
        }
      }
    }
  </script>

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    body { font-family: 'Inter', sans-serif; }
    .glass { background: rgba(255,255,255,0.92); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.35); }
    .embed-responsive { position: relative; padding-top: 56.25%; }
    .embed-responsive iframe { position: absolute; inset: 0; width: 100%; height: 100%; }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">

<!-- NAVBAR -->
<nav class="glass sticky top-0 z-50 shadow-md">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
    <a href="<?= isset($_SESSION['user_id']) ? 'dashboard.php' : 'javascript:history.back()' ?>"
       class="inline-flex items-center gap-2 text-gray-700 hover:text-primary font-semibold transition">
      <span class="text-lg">←</span> Kembali
    </a>

    <div class="ml-auto flex items-center gap-3">
      <?php if (isset($_SESSION['user_id'])): ?>
        <div class="hidden sm:flex items-center gap-2 text-sm text-gray-700">
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-xs">
            <?= htmlspecialchars(strtoupper(substr((string)($_SESSION['user_name'] ?? 'U'), 0, 1))) ?>
          </div>
          <span class="font-medium"><?= htmlspecialchars((string)$_SESSION['user_name']) ?></span>
        </div>
        <a href="../includes/logout.php"
           class="px-4 py-2 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition text-sm font-semibold">
          Logout
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- HERO -->
<header class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
  <div class="glass rounded-3xl shadow-2xl overflow-hidden">
    <div class="relative p-8 sm:p-10">
      <div class="absolute -top-24 -right-24 w-72 h-72 bg-white/20 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-white/20 rounded-full blur-3xl"></div>

      <div class="relative">
        <div class="flex flex-wrap items-center gap-2 mb-4">
          <span class="px-3 py-1.5 rounded-full border text-xs font-semibold <?= $visibilityClass ?>">
            <?= htmlspecialchars($visibilityLabel) ?>
          </span>
          <span class="px-3 py-1.5 rounded-full border text-xs font-semibold <?= $authorRoleClass ?>">
            <?= htmlspecialchars($authorRoleLabel) ?>
          </span>
          <span class="px-3 py-1.5 rounded-full border text-xs font-semibold bg-white/70 text-gray-700 border-white/60">
            <?= htmlspecialchars($project['course']) ?>
          </span>
          <span class="px-3 py-1.5 rounded-full border text-xs font-semibold bg-white/70 text-gray-700 border-white/60">
            <?= htmlspecialchars($project['semester']) ?>
          </span>
        </div>

        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight">
          <?= htmlspecialchars($project['title']) ?>
        </h1>

        <div class="mt-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div class="text-sm text-gray-700">
            <span class="font-semibold">Dibuat oleh:</span>
            <span class="font-medium"><?= htmlspecialchars($project['author_name']) ?></span>
            <?php if (!empty($project['nim'])): ?>
              <span class="text-gray-500">(<?= htmlspecialchars($project['nim']) ?>)</span>
            <?php endif; ?>
          </div>

          <?php if ($isOwner): ?>
            <div class="flex gap-2">
              <a href="edit-project.php?id=<?= (int)$project['id'] ?>"
                 class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 transition text-sm font-semibold">
                Edit
              </a>
              <a href="delete-project.php?id=<?= (int)$project['id'] ?>"
                 onclick="return confirm('Yakin hapus proyek ini?')"
                 class="px-4 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 transition text-sm font-semibold">
                Hapus
              </a>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
</header>

<!-- CONTENT -->
<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <?php if ($message): ?>
    <div class="glass rounded-2xl shadow-lg overflow-hidden">
      <div class="p-4 border-l-4 <?= $messageType === 'success' ? 'border-green-500' : 'border-red-500' ?>
           <?= $messageType === 'success' ? 'bg-green-50/60' : 'bg-red-50/60' ?>">
        <p class="font-semibold <?= $messageType === 'success' ? 'text-green-800' : 'text-red-800' ?>">
          <?= htmlspecialchars($message) ?>
        </p>
      </div>
    </div>
  <?php endif; ?>

  <!-- PROJECT DETAIL -->
  <section class="glass rounded-3xl shadow-xl p-6 sm:p-8">
    <h2 class="text-lg font-extrabold text-gray-900 mb-4">Deskripsi Proyek</h2>
    <div class="text-gray-800 leading-relaxed whitespace-pre-line">
      <?= htmlspecialchars((string)$project['description']) ?>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
      <?php if (!empty($project['screenshot'])): ?>
        <div class="overflow-hidden rounded-2xl border border-white/40 shadow-sm bg-white/60">
          <img
            src="../uploads/screenshots/<?= htmlspecialchars((string)$project['screenshot']) ?>"
            alt="Screenshot proyek"
            class="w-full h-full object-cover"
            loading="lazy"
          />
        </div>
      <?php endif; ?>

      <?php if ($embedSrc): ?>
        <div class="rounded-2xl overflow-hidden border border-white/40 shadow-sm bg-white/60">
          <div class="embed-responsive">
            <iframe src="<?= htmlspecialchars($embedSrc) ?>" frameborder="0" allowfullscreen></iframe>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($project['repo_link'])): ?>
      <div class="mt-6">
        <a href="<?= htmlspecialchars((string)$project['repo_link']) ?>" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gray-900 text-white hover:bg-gray-800 transition font-semibold">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"/>
          </svg>
          Buka Repository
        </a>
      </div>
    <?php endif; ?>
  </section>

  <!-- KOMENTAR DOSEN -->
  <section class="glass rounded-3xl shadow-xl p-6 sm:p-8">
    <div class="flex items-center justify-between gap-4 mb-6">
      <h2 class="text-lg font-extrabold text-gray-900">Komentar & Penilaian</h2>
      <span class="text-sm font-semibold text-gray-700 px-3 py-1.5 rounded-full bg-white/60 border border-white/40">
        <?= count($comments) ?> komentar
      </span>
    </div>

    <?php if ($isDosen): ?>
      <form method="POST" class="mb-6 p-4 sm:p-5 rounded-2xl bg-white/70 border border-white/40 space-y-3">
        <textarea name="comment" rows="3" required
          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition"
          placeholder="Tulis komentar untuk mahasiswa..."></textarea>

        <div class="grid sm:grid-cols-2 gap-3">
          <select name="rating"
            class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition">
            <option value="">— Tidak dinilai —</option>
            <?php for ($r = 5; $r >= 1; $r--): ?>
              <option value="<?= $r ?>"><?= $r ?> ⭐</option>
            <?php endfor; ?>
          </select>

          <button type="submit"
            class="w-full px-5 py-3 rounded-xl bg-primary hover:bg-blue-600 text-white font-semibold transition">
            Kirim Komentar
          </button>
        </div>
      </form>
    <?php endif; ?>

    <?php if (empty($comments)): ?>
      <div class="p-5 rounded-2xl bg-white/60 border border-white/40 text-gray-700">
        Belum ada komentar dari dosen.
      </div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($comments as $c): ?>
          <div class="p-5 rounded-2xl bg-white/70 border border-white/40">
            <div class="flex flex-wrap items-center gap-2">
              <strong class="text-gray-900"><?= htmlspecialchars((string)$c['dosen_name']) ?></strong>

              <?php if (!empty($c['rating'])): ?>
                <span class="text-yellow-600 text-sm font-semibold">
                  <?= str_repeat('★', (int)$c['rating']) . str_repeat('☆', 5 - (int)$c['rating']) ?>
                </span>
              <?php endif; ?>

              <span class="ml-auto text-xs text-gray-500">
                <?= htmlspecialchars(date('d M Y H:i', strtotime((string)$c['created_at']))) ?>
              </span>
            </div>

            <p class="mt-2 text-gray-800 leading-relaxed whitespace-pre-line">
              <?= htmlspecialchars((string)$c['comment']) ?>
            </p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</main>
</body>
</html>
