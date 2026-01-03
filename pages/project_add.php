<!-- pages/project-add.php -->
<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$message = '';

if (!$pdo) {
    $message = "Koneksi database gagal.";
}

if ($_POST) {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $semester = trim($_POST['semester']);
    $course = trim($_POST['course']);
    $repo = trim($_POST['repo_link']);
    $video = trim($_POST['video_embed']);
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    // Upload screenshot
    $screenshot = null;
    if (!empty($_FILES['screenshot']['name'])) {
        $ext = pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION);
        $screenshot = uniqid() . '.' . strtolower($ext);
        $target = '../uploads/screenshots/' . $screenshot;
        if (!move_uploaded_file($_FILES['screenshot']['tmp_name'], $target)) {
            $message = "Gagal upload gambar.";
        }
    }

    if (empty($message) && $pdo) {
        $stmt = $pdo->prepare("
            INSERT INTO projects (user_id, title, description, semester, course, repo_link, video_embed, screenshot, is_public)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'], $title, $desc, $semester, $course, $repo, $video, $screenshot, $is_public
        ]);
        header("Location: dashboard.php?msg=Proyek berhasil ditambahkan.");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Proyek — Portofolio PBL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">← Kembali ke Dashboard</a>
    </div>
</nav>

<div class="container mt-4">
    <h2>Tambah Proyek Baru</h2>
    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Judul Proyek *</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Deskripsi *</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Semester *</label>
                <select name="semester" class="form-select" required>
                    <option value="">Pilih...</option>
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option value="Semester <?= $i ?>">Semester <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Mata Kuliah *</label>
                <input type="text" name="course" class="form-control" placeholder="e.g., Pemrograman Web" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Link Repository (GitHub/GitLab) — opsional</label>
            <input type="url" name="repo_link" class="form-control" placeholder="https://github.com/...">
        </div>

        <div class="mb-3">
            <label class="form-label">Embed Video Demo (YouTube/Vimeo) — opsional</label>
            <textarea name="video_embed" class="form-control" rows="2" 
                placeholder="Contoh: https://youtu.be/abc123 atau embed code"></textarea>
            <small class="form-text text-muted">Boleh URL YouTube/Vimeo, atau kode embed iframe.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Screenshot (opsional)</label>
            <input type="file" name="screenshot" class="form-control" accept="image/*">
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="is_public" id="public" class="form-check-input">
            <label for="public" class="form-check-label">Publikasikan portofolio ini (bisa dilihat orang lain via link)</label>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Proyek</button>
    </form>
</div>
</body>
</html>