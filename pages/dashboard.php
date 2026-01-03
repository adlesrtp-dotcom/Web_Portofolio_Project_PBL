<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: ../auth/login/login.php");
    exit;
}

require_once __DIR__ . '/../includes/config.php';

if (!isset($pdo) || $pdo === null) {
    die("Database connection failed. Please check your configuration.");
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil proyek — mahasiswa lihat punya sendiri, dosen lihat semua (opsional: filter pencarian)
if ($role === 'mahasiswa') {
    $stmt = $pdo->prepare("
        SELECT * FROM projects 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $projects = $stmt->fetchAll();
} else { // dosen
    // Filter pencarian (opsional)
    $search = $_GET['search'] ?? '';
    $semester = $_GET['semester'] ?? '';
    $course = $_GET['course'] ?? '';

    $sql = "SELECT p.*, u.name as mahasiswa, u.nim 
            FROM projects p 
            JOIN users u ON p.user_id = u.id 
            WHERE u.role = 'mahasiswa'";
    $params = [];

    if ($search) {
        $sql .= " AND (u.name LIKE ? OR u.nim LIKE ? OR p.title LIKE ?)";
        $like = "%$search%";
        $params = array_merge($params, [$like, $like, $like]);
    }
    if ($semester) {
        $sql .= " AND p.semester = ?";
        $params[] = $semester;
    }
    if ($course) {
        $sql .= " AND p.course = ?";
        $params[] = $course;
    }
    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $projects = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Portofolio PBL</title>

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
    
    .card-hover {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card-hover:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .btn-gradient {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      transition: all 0.3s ease;
    }
    
    .btn-gradient:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
    }
    
    .floating-animation {
      animation: floating 3s ease-in-out infinite;
    }
    
    @keyframes floating {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
    }
    
    .line-clamp-3 {
      display: -webkit-box;
      -webkit-line-clamp: 3;
      line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
  </style>
</head>

<body class="min-h-screen">

<!-- NAVBAR -->
<nav class="glass-effect sticky top-0 z-50 shadow-lg">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold gradient-text">Portofolio PBL</h1>
          <p class="text-xs text-gray-500">Project-Based Learning</p>
        </div>
      </div>

      <div class="flex items-center gap-4">
        <div class="hidden md:flex items-center gap-3 bg-gray-50 rounded-xl px-4 py-2">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-semibold text-sm">
            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
            <p class="text-xs text-gray-500"><?= $role === 'mahasiswa' ? 'Mahasiswa' : 'Dosen' ?></p>
          </div>
        </div>
        <a href="../includes/logout.php"
           class="px-5 py-2.5 text-sm font-medium rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-all duration-300 hover:shadow-md">
          Logout
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- HERO SECTION -->
<section class="relative overflow-hidden py-12 sm:py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden">
      <div class="relative p-8 sm:p-12">
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-primary/20 to-secondary/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-gradient-to-tr from-accent/20 to-primary/20 rounded-full blur-3xl"></div>
        
        <div class="relative z-10">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div class="flex-1">
              <div class="inline-block px-4 py-1.5 bg-gradient-to-r from-primary/10 to-secondary/10 rounded-full mb-4">
                <span class="text-sm font-semibold gradient-text">
                  <?= $role === 'mahasiswa' ? '📚 Student Dashboard' : '👨‍🏫 Lecturer Dashboard' ?>
                </span>
              </div>
              <h2 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-3">
                <?= $role === 'mahasiswa' ? 'Proyek Saya' : 'Portofolio Mahasiswa' ?>
              </h2>
              <p class="text-gray-600 text-base sm:text-lg max-w-2xl">
                <?= $role === 'mahasiswa'
                  ? 'Kelola dan tampilkan hasil terbaik dari proyek PBL Anda dengan mudah.'
                  : 'Evaluasi, berikan feedback, dan nilai proyek mahasiswa secara efisien.' ?>
              </p>
              
              <?php if ($role === 'mahasiswa'): ?>
                <a href="add-project.php"
                   class="inline-flex items-center gap-2 mt-6 btn-gradient text-white px-6 py-3.5 rounded-xl font-semibold shadow-lg">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Tambah Proyek Baru
                </a>
              <?php endif; ?>
            </div>
            
            <div class="floating-animation">
              <div class="w-32 h-32 sm:w-40 sm:h-40 bg-gradient-to-br from-primary to-secondary rounded-3xl shadow-2xl flex items-center justify-center transform rotate-6">
                <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
            </div>
          </div>
          
          <!-- Stats untuk mahasiswa -->
          <?php if ($role === 'mahasiswa'): ?>
            <div class="grid grid-cols-3 gap-4 mt-8">
              <div class="bg-white/50 backdrop-blur rounded-xl p-4 text-center">
                <p class="text-2xl sm:text-3xl font-bold gradient-text"><?= count($projects) ?></p>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Total Proyek</p>
              </div>
              <div class="bg-white/50 backdrop-blur rounded-xl p-4 text-center">
                <p class="text-2xl sm:text-3xl font-bold text-green-600"><?= count(array_filter($projects, fn($p) => $p['video_embed'])) ?></p>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Video Demo</p>
              </div>
              <div class="bg-white/50 backdrop-blur rounded-xl p-4 text-center">
                <p class="text-2xl sm:text-3xl font-bold text-purple-600"><?= date('Y') ?></p>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">Tahun Ajaran</p>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CONTENT -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">

<?php if (empty($projects)): ?>
  <div class="glass-effect rounded-3xl shadow-xl p-12 text-center">
    <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
      <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
    </div>
    <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Proyek</h3>
    <p class="text-gray-500 max-w-md mx-auto">
      <?= $role === 'mahasiswa'
        ? 'Mulai perjalanan PBL Anda dengan menambahkan proyek pertama sekarang.'
        : 'Tidak ada data proyek yang sesuai dengan filter Anda.' ?>
    </p>
  </div>
<?php else: ?>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($projects as $p): ?>
      <div class="glass-effect rounded-2xl shadow-lg overflow-hidden card-hover">
        
        <!-- Project Image -->
        <div class="relative h-48 bg-gradient-to-br from-primary/10 to-secondary/10 overflow-hidden">
          <?php if ($p['screenshot']): ?>
            <img src="../uploads/screenshots/<?= htmlspecialchars($p['screenshot']) ?>"
                 class="w-full h-full object-cover">
          <?php else: ?>
            <div class="w-full h-full flex items-center justify-center">
              <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
          <?php endif; ?>
          
          <!-- Video Badge -->
          <?php if ($p['video_embed']): ?>
            <div class="absolute top-3 right-3 bg-red-500 text-white px-3 py-1.5 rounded-full text-xs font-semibold shadow-lg flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
              </svg>
              Video
            </div>
          <?php endif; ?>
        </div>

        <!-- Project Content -->
        <div class="p-6">
          <h3 class="font-bold text-lg text-gray-800 mb-2 line-clamp-2">
            <?= htmlspecialchars($p['title']) ?>
          </h3>

          <?php if ($role === 'dosen'): ?>
            <div class="flex items-center gap-2 mb-3 text-sm">
              <div class="w-7 h-7 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-xs font-semibold">
                <?= strtoupper(substr($p['mahasiswa'], 0, 1)) ?>
              </div>
              <div>
                <p class="font-medium text-gray-700"><?= htmlspecialchars($p['mahasiswa']) ?></p>
                <p class="text-xs text-gray-500"><?= htmlspecialchars($p['nim']) ?></p>
              </div>
            </div>
          <?php endif; ?>

          <!-- Tags -->
          <div class="flex flex-wrap gap-2 mb-4">
            <span class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 font-medium">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
              </svg>
              <?= htmlspecialchars($p['course']) ?>
            </span>
            <span class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-full bg-purple-50 text-purple-700 font-medium">
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
              </svg>
              <?= htmlspecialchars($p['semester']) ?>
            </span>
          </div>

          <!-- Description -->
          <p class="text-sm text-gray-600 mb-5 line-clamp-3">
            <?= htmlspecialchars($p['description']) ?>
          </p>

          <!-- Actions -->
          <div class="flex items-center justify-between pt-4 border-t border-gray-100">
            <a href="view-project.php?id=<?= $p['id'] ?>"
               class="inline-flex items-center gap-1.5 text-sm font-semibold gradient-text hover:opacity-80 transition">
              Lihat Detail
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </a>

            <?php if ($role === 'mahasiswa'): ?>
              <div class="flex gap-2">
                <a href="edit-project.php?id=<?= $p['id'] ?>" 
                   class="p-2 text-gray-500 hover:text-primary hover:bg-primary/5 rounded-lg transition"
                   title="Edit">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </a>
                <button onclick="confirmDelete(<?= $p['id'] ?>)"
                        class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition"
                        title="Hapus">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>

</main>

<!-- Footer -->
<footer class="glass-effect mt-12 py-6">
  <div class="max-w-7xl mx-auto px-4 text-center">
    <p class="text-sm text-gray-600">
      © <?= date('Y') ?> Portofolio PBL. Sistem Manajemen Project-Based Learning
    </p>
  </div>
</footer>

<script>
function confirmDelete(id) {
  if (confirm("Yakin ingin menghapus proyek ini? Tindakan ini tidak dapat dibatalkan.")) {
    window.location.href = `delete-project.php?id=${id}`;
  }
}

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});
</script>

</body>
</html>