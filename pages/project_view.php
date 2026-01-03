<!-- pages/project-view.php -->
<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isset($pdo)) {
    die("Database connection failed. Please check your configuration.");
}

$id = (int) ($_GET['id'] ?? 0);
if (!$id) die("Proyek tidak ditemukan.");

// Ambil proyek + user
$stmt = $pdo->prepare("
    SELECT p.*, u.name as author_name, u.nim 
    FROM projects p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) die("Proyek tidak ditemukan.");

// Ambil komentar (jika ada)
$stmt = $pdo->prepare("
    SELECT c.*, u.name as dosen_name 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.project_id = ? AND u.role = 'dosen'
    ORDER BY c.created_at DESC
");
$stmt->execute([$id]);
$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($project['title']) ?> — Portofolio PBL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .embed-responsive { position: relative; overflow: hidden; padding-top: 56.25%; }
        .embed-responsive iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Portofolio PBL</a>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">Kembali</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-white">
            <h3 class="mb-0"><?= htmlspecialchars($project['title']) ?></h3>
            <small class="text-muted">
                Oleh: <?= htmlspecialchars($project['author_name']) ?> (<?= htmlspecialchars($project['nim']) ?>)  
                • <?= htmlspecialchars($project['course']) ?> — <?= htmlspecialchars($project['semester']) ?>
            </small>
        </div>
        <div class="card-body">
            <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>

            <?php if ($project['screenshot']): ?>
                <img src="../uploads/screenshots/<?= htmlspecialchars($project['screenshot']) ?>" 
                     class="img-fluid rounded mb-3" alt="Screenshot">
            <?php endif; ?>

            <?php if ($project['video_embed']): 
                $url = trim($project['video_embed']);
                // Konversi YouTube URL ke embed
                if (strpos($url, 'youtu.be/') !== false) {
                    $vid = substr(parse_url($url, PHP_URL_PATH), 1);
                    $embed = "https://www.youtube.com/embed/$vid";
                } elseif (strpos($url, 'youtube.com/watch') !== false) {
                    parse_str(parse_url($url, PHP_URL_QUERY), $params);
                    $vid = $params['v'] ?? '';
                    $embed = $vid ? "https://www.youtube.com/embed/$vid" : null;
                } else {
                    $embed = $url; // asumsi sudah iframe atau embed langsung
                }
                if ($embed): ?>
                <div class="embed-responsive mb-3">
                    <iframe src="<?= htmlspecialchars($embed) ?>" frameborder="0" allowfullscreen></iframe>
                </div>
            <?php endif; ?>
            <?php endif; ?>

            <?php if ($project['repo_link']): ?>
                <p>
                    <a href="<?= htmlspecialchars($project['repo_link']) ?>" target="_blank" class="btn btn-outline-dark">
                        📁 Buka Repository
                    </a>
                </p>
            <?php endif; ?>

            <!-- Komentar Dosen -->
            <hr>
            <h5>Komentar & Penilaian Dosen (<?= count($comments) ?>)</h5>
            <?php if ($_SESSION['role'] === 'dosen'): ?>
                <form method="POST" action="../actions/add-comment.php" class="mb-4">
                    <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                    <div class="mb-2">
                        <textarea name="comment" class="form-control" rows="3" placeholder="Tulis komentar..." required></textarea>
                    </div>
                    <div class="mb-2">
                        <label>Nilai (1–5)</label>
                        <select name="rating" class="form-select">
                            <option value="">— Tidak dinilai —</option>
                            <?php for ($r = 5; $r >= 1; $r--): ?>
                                <option value="<?= $r ?>"><?= $r ?> ⭐</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Kirim Komentar</button>
                </form>
            <?php endif; ?>

            <?php if (empty($comments)): ?>
                <p class="text-muted">Belum ada komentar dari dosen.</p>
            <?php else: ?>
                <div class="list-group">
                <?php foreach ($comments as $c): ?>
                    <div class="list-group-item">
                        <strong><?= htmlspecialchars($c['dosen_name']) ?></strong>
                        <?php if ($c['rating']): ?> — <span class="badge bg-warning"><?= $c['rating'] ?> ⭐</span><?php endif; ?>
                        <br>
                        <small class="text-muted"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></small>
                        <p class="mt-1 mb-0"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>