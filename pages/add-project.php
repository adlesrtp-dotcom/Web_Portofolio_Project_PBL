<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login/login.php");
    exit;
}

$message = '';
$success = false;

// Sticky values (biar input tidak hilang saat validasi error)
$title = '';
$desc = '';
$semester = '';
$course = '';
$repo = '';
$video = '';
$is_public = 0;

if ($_POST) {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $repo = trim($_POST['repo_link'] ?? '');
    $video = trim($_POST['video_embed'] ?? '');
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    // Validasi
    $errors = [];
    if (empty($title)) $errors[] = "Judul wajib diisi.";
    if (empty($desc)) $errors[] = "Deskripsi wajib diisi.";
    if (empty($semester)) $errors[] = "Semester wajib diisi.";
    if (empty($course)) $errors[] = "Mata kuliah wajib diisi.";

    if (!empty($errors)) {
        $message = implode('<br>', $errors);
    } else {
        // Upload screenshot (opsional)
        $screenshot = null;

        if (!empty($_FILES['screenshot']['name'])) {
            // Batas 5MB
            $maxBytes = 5 * 1024 * 1024;
            $fileSize = (int)($_FILES['screenshot']['size'] ?? 0);
            if ($fileSize > $maxBytes) {
                $message = "Ukuran gambar terlalu besar. Maksimal 5MB.";
            } else {
                $ext = strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));
                $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $allowedExt, true)) {
                    $message = "Format gambar tidak didukung. Gunakan JPG/PNG/WEBP.";
                } else {
                    // Validasi MIME (lebih aman daripada ekstensi saja)
                    $tmp = $_FILES['screenshot']['tmp_name'] ?? '';
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $tmp ? $finfo->file($tmp) : '';
                    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!$mime || !in_array($mime, $allowedMime, true)) {
                        $message = "File yang diunggah bukan gambar yang valid.";
                    } else {
                        $uploadDir = __DIR__ . '/../uploads/screenshots/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0775, true);
                        }

                        $screenshot = bin2hex(random_bytes(16)) . '.' . $ext;
                        $target = $uploadDir . $screenshot;

                        if (!move_uploaded_file($tmp, $target)) {
                            $message = "Gagal mengunggah gambar.";
                        }
                    }
                }
            }
        }

        if (empty($message)) {
            try {
                if (!isset($pdo) || !($pdo instanceof PDO)) {
                    throw new Exception("Koneksi database gagal. Periksa includes/config.php");
                }

                $stmt = $pdo->prepare("
                    INSERT INTO projects (user_id, title, description, semester, course, repo_link, video_embed, screenshot, is_public)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    (int)$_SESSION['user_id'],
                    $title,
                    $desc,
                    $semester,
                    $course,
                    $repo ?: null,
                    $video ?: null,
                    $screenshot,
                    (int)$is_public
                ]);

                $success = true;
                $message = "Proyek berhasil ditambahkan!";
            } catch (PDOException $e) {
                error_log("Add project error: " . $e->getMessage());
                $message = "Terjadi kesalahan sistem.";
            } catch (Exception $e) {
                error_log("Add project error: " . $e->getMessage());
                $message = "Terjadi kesalahan sistem.";
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
  <title>Tambah Proyek — Portofolio PBL</title>

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
    
    body { 
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      background-attachment: fixed;
    }
    
    .glass-effect {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .input-field {
      transition: all 0.3s ease;
    }
    
    .input-field:focus {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
    }
    
    .btn-gradient {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      transition: all 0.3s ease;
    }
    
    .btn-gradient:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
    }
    
    .file-upload-wrapper {
      position: relative;
      overflow: hidden;
      display: inline-block;
      width: 100%;
    }
    
    .file-upload-input {
      position: absolute;
      font-size: 100px;
      opacity: 0;
      right: 0;
      top: 0;
      cursor: pointer;
    }
    
    .file-upload-label {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 3rem 1rem;
      border: 2px dashed #cbd5e1;
      border-radius: 0.75rem;
      background: #f8fafc;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .file-upload-label:hover {
      border-color: #3b82f6;
      background: #eff6ff;
      transform: scale(1.01);
    }
    
    .form-section {
      animation: fadeInUp 0.5s ease forwards;
      opacity: 0;
    }
    
    .form-section:nth-child(1) { animation-delay: 0.1s; }
    .form-section:nth-child(2) { animation-delay: 0.2s; }
    .form-section:nth-child(3) { animation-delay: 0.3s; }
    .form-section:nth-child(4) { animation-delay: 0.4s; }
    .form-section:nth-child(5) { animation-delay: 0.5s; }
    .form-section:nth-child(6) { animation-delay: 0.6s; }
    .form-section:nth-child(7) { animation-delay: 0.7s; }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .progress-step {
      transition: all 0.3s ease;
    }
    
    .progress-step.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
  </style>
</head>

<body class="min-h-screen pb-12">

<!-- NAVBAR -->
<nav class="glass-effect sticky top-0 z-50 shadow-lg mb-8">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <a href="dashboard.php" 
         class="flex items-center gap-2 text-gray-700 hover:text-primary font-semibold transition group">
        <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Dashboard
      </a>

      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-2 text-sm text-gray-600">
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-semibold text-xs">
            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
          </div>
          <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
        </div>
        <a href="../includes/logout.php"
           class="px-4 py-2 text-sm font-medium rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
          Logout
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
  <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden">
    <div class="relative p-8 sm:p-10">
      <!-- Decorative elements -->
      <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-primary/20 to-secondary/20 rounded-full blur-3xl"></div>
      <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-accent/20 to-primary/20 rounded-full blur-3xl"></div>
      
      <div class="relative z-10">
        <div class="inline-block px-4 py-1.5 bg-gradient-to-r from-primary/10 to-secondary/10 rounded-full mb-4">
          <span class="text-sm font-semibold gradient-text">✨ Create New Project</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-3">Tambah Proyek Baru</h1>
        <p class="text-gray-600 text-base sm:text-lg max-w-2xl">
          Dokumentasikan proyek PBL Anda dengan lengkap dan profesional untuk membangun portofolio yang impressive.
        </p>
        
        <!-- Progress Indicator -->
        <div class="flex items-center gap-2 mt-6 flex-wrap">
          <div class="progress-step active px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
            1. Info Dasar
          </div>
          <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
          <div class="progress-step px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
            2. Akademik
          </div>
          <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
          <div class="progress-step px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
            3. Media
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CONTENT -->
<main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

  <?php if ($message): ?>
    <div class="mb-6 glass-effect rounded-2xl shadow-lg overflow-hidden animate-[fadeInUp_0.5s_ease]">
      <div class="p-5 flex items-start gap-3
        <?= $success
          ? 'bg-gradient-to-r from-green-50 to-emerald-50'
          : 'bg-gradient-to-r from-red-50 to-rose-50' ?>">
        <?php if ($success): ?>
          <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        <?php else: ?>
          <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        <?php endif; ?>
        <div class="flex-1">
          <p class="font-semibold <?= $success ? 'text-green-800' : 'text-red-800' ?>">
            <?= $success ? 'Berhasil Ditambahkan!' : 'Terjadi Kesalahan' ?>
          </p>
          <p class="text-sm mt-1 <?= $success ? 'text-green-700' : 'text-red-700' ?>">
            <?= $message ?>
          </p>
          <?php if ($success): ?>
            <a href="dashboard.php" class="inline-flex items-center gap-1 text-sm font-medium text-green-700 hover:text-green-800 mt-2">
              Lihat di Dashboard
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="space-y-6" id="projectForm">
    
    <!-- Informasi Dasar -->
    <div class="glass-effect rounded-2xl shadow-lg p-6 sm:p-8 form-section">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Informasi Dasar</h2>
          <p class="text-sm text-gray-500">Detail utama tentang proyek Anda</p>
        </div>
      </div>

      <div class="space-y-5">
        <!-- Judul -->
        <div>
          <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            Judul Proyek
            <span class="text-red-500">*</span>
          </label>
          <input type="text" name="title" required
            value="<?= htmlspecialchars($title) ?>"
            placeholder="Contoh: Sistem Informasi Perpustakaan Digital"
            class="input-field w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition">
          <p class="text-xs text-gray-500 mt-2">Gunakan nama yang deskriptif dan mudah dipahami</p>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
            Deskripsi Proyek
            <span class="text-red-500">*</span>
          </label>
          <textarea name="description" rows="5" required
            class="input-field w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition resize-none"><?= htmlspecialchars($desc) ?></textarea>
          <div class="flex justify-between items-center mt-2">
            <p class="text-xs text-gray-500">Minimal 100 karakter untuk deskripsi yang informatif</p>
            <span class="text-xs text-gray-400" id="charCount">0 karakter</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Informasi Akademik -->
    <div class="glass-effect rounded-2xl shadow-lg p-6 sm:p-8 form-section">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Informasi Akademik</h2>
          <p class="text-sm text-gray-500">Semester dan mata kuliah terkait</p>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-5">
        <!-- Semester -->
        <div>
          <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Semester
            <span class="text-red-500">*</span>
          </label>
          <select name="semester" required
            class="input-field w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition bg-white">
            <option value="">Pilih Semester</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>
              <?php $opt = "Semester " . $i; ?>
              <option value="<?= htmlspecialchars($opt) ?>" <?= ($semester === $opt) ? 'selected' : '' ?>>
                <?= htmlspecialchars($opt) ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>

        <!-- Mata Kuliah -->
        <div>
          <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path d="M12 14l9-5-9-5-9 5 9 5z"/>
              <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
            </svg>
            Mata Kuliah
            <span class="text-red-500">*</span>
          </label>
          <input type="text" name="course" required
            value="<?= htmlspecialchars($course) ?>"
            placeholder="Contoh: Pemrograman Web / Basis Data / Rekayasa Perangkat Lunak"
            class="input-field w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 outline-none transition">
        </div>
      </div>
    </div>

    <!-- Link Repository -->
    <div class="glass-effect rounded-2xl shadow-lg p-6 sm:p-8 form-section">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Repository & Source Code</h2>
          <p class="text-sm text-gray-500">Link ke kode sumber proyek Anda</p>
        </div>
      </div>

      <div>
        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
          <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
          Link Repository (GitHub, GitLab, Bitbucket)
        </label>
        <input type="url" name="repo_link"
          value="<?= htmlspecialchars($repo) ?>"
          placeholder="https://github.com/username/project-name"
          class="input-field w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-green-500 focus:ring-4 focus:ring-green-500/10 outline-none transition">
        <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Opsional - Bantu reviewer melihat kualitas kode Anda
        </p>
      </div>
    </div>

    <!-- Video Demo -->
    <div class="glass-effect rounded-2xl shadow-lg p-6 sm:p-8 form-section">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Video Demo</h2>
          <p class="text-sm text-gray-500">Tunjukkan proyek Anda dalam aksi</p>
        </div>
      </div>

      <div>
        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
          <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
          </svg>
          Link / Embed Video
        </label>
        <textarea name="video_embed" rows="3"
          placeholder="Contoh: https://www.youtube.com/watch?v=... atau embed iframe"
          class="input-field w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-red-500 focus:ring-4 focus:ring-red-500/10 outline-none transition resize-none font-mono text-sm"><?= htmlspecialchars($video) ?></textarea>
        <p class="text-xs text-gray-500 mt-2">
          Opsional - Video demo membantu dosen/reviewer memahami flow & fitur proyek.
        </p>
      </div>
    </div>

    <!-- Screenshot -->
    <div class="glass-effect rounded-2xl shadow-lg p-6 sm:p-8 form-section">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Screenshot Proyek</h2>
          <p class="text-sm text-gray-500">Gambar preview atau tangkapan layar</p>
        </div>
      </div>

      <div class="file-upload-wrapper">
        <input type="file" name="screenshot" accept="image/*" class="file-upload-input" id="fileInput">
        <label for="fileInput" class="file-upload-label">
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
          </svg>
          <div>
            <p class="text-sm font-semibold text-gray-700">Klik untuk upload screenshot</p>
            <p class="text-xs text-gray-500 mt-1">PNG, JPG, WEBP hingga 5MB</p>
          </div>
        </label>
      </div>
      
      <div class="mt-4 p-4 bg-blue-50 rounded-xl border border-blue-200">
        <div class="flex gap-3">
          <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="text-sm text-blue-800">
            <p class="font-semibold mb-1">Tips untuk Screenshot yang Bagus:</p>
            <ul class="space-y-1 text-xs">
              <li>• Gunakan resolusi tinggi dan tampilan yang bersih</li>
              <li>• Capture halaman utama atau fitur unggulan</li>
              <li>• Pastikan UI terlihat jelas dan profesional</li>
              <li>• Hindari screenshot dengan data pribadi</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Visibility -->
    <div class="glass-effect rounded-2xl shadow-lg p-6 sm:p-8 form-section">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-bold text-gray-800">Pengaturan Visibilitas</h2>
          <p class="text-sm text-gray-500">Kontrol siapa yang bisa melihat proyek</p>
        </div>
      </div>

      <label class="flex items-start gap-3 p-4 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-200 cursor-pointer hover:border-amber-300 transition">
        <input type="checkbox" name="is_public" id="public"
          <?= $is_public ? 'checked' : '' ?>
          class="mt-1 w-5 h-5 text-primary rounded border-gray-300 focus:ring-primary">
        <div class="flex-1">
          <p class="font-semibold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Publikasikan sebagai portofolio publik
          </p>
          <p class="text-sm text-gray-600 mt-1">Proyek dapat dilihat oleh siapa saja yang memiliki link. Bagus untuk portofolio profesional!</p>
        </div>
      </label>
    </div>

    <!-- Action Buttons -->
    <div class="glass-effect rounded-2xl shadow-lg p-6 form-section">
      <div class="flex flex-col sm:flex-row items-center gap-4">
        <button type="submit"
          class="btn-gradient text-white px-8 py-3.5 rounded-xl font-semibold shadow-lg flex items-center gap-2 w-full sm:w-auto justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Simpan Proyek
        </button>
        
        <a href="dashboard.php" 
           class="px-8 py-3.5 rounded-xl font-semibold border-2 border-gray-300 text-gray-700 hover:bg-gray-50 transition w-full sm:w-auto text-center">
          Batal
        </a>
        
        <div class="ml-auto hidden sm:flex items-center gap-2 text-sm text-gray-500">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>Semua perubahan akan tersimpan</span>
        </div>
      </div>
    </div>

  </form>
</main>

<script>
// Character counter for description
const descField = document.querySelector('textarea[name="description"]');
const charCount = document.getElementById('charCount');

descField?.addEventListener('input', (e) => {
  const length = e.target.value.length;
  charCount.textContent = `${length} karakter`;
  
  if (length >= 100) {
    charCount.classList.add('text-green-600', 'font-semibold');
    charCount.classList.remove('text-gray-400');
  } else {
    charCount.classList.remove('text-green-600', 'font-semibold');
    charCount.classList.add('text-gray-400');
  }
});

// File upload preview
document.getElementById('fileInput')?.addEventListener('change', function(e) {
  const fileName = e.target.files[0]?.name;
  const fileSize = e.target.files[0]?.size;
  
  if (fileName) {
    const label = document.querySelector('.file-upload-label');
    const sizeMB = (fileSize / (1024 * 1024)).toFixed(2);
    
    label.innerHTML = `
      <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div>
        <p class="text-sm font-semibold text-green-700">${fileName}</p>
        <p class="text-xs text-gray-500 mt-1">${sizeMB} MB • Klik untuk mengganti</p>
      </div>
    `;
    label.style.borderColor = '#10b981';
    label.style.background = '#f0fdf4';
  }
});

// Form validation warning
let formChanged = false;
const form = document.getElementById('projectForm');
const inputs = form.querySelectorAll('input, textarea, select');

inputs.forEach(input => {
  input.addEventListener('change', () => {
    formChanged = true;
  });
});

window.addEventListener('beforeunload', (e) => {
  if (formChanged) {
    e.preventDefault();
    e.returnValue = '';
  }
});

form.addEventListener('submit', () => {
  formChanged = false;
});

// Progress step highlighting based on scroll
const sections = document.querySelectorAll('.form-section');
const progressSteps = document.querySelectorAll('.progress-step');

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const index = Array.from(sections).indexOf(entry.target);
      if (index < 3 && progressSteps[index]) {
        progressSteps.forEach(step => step.classList.remove('active'));
        progressSteps[index].classList.add('active');
      }
    }
  });
}, { threshold: 0.5 });

sections.forEach(section => observer.observe(section));
</script>

</body>
</html>