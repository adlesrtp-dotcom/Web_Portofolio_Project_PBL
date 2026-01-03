<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PBL PORTOFOLIO — Politeknik Negeri Batam</title>
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Poppins', sans-serif; }
    .fade-in { animation: fadeIn 1s ease-out; }
    .fade-in-delay { animation: fadeIn 1s ease-out; animation-delay: 0.3s; }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-gray-50">
  <!-- Navbar -->
  <nav class="bg-white shadow-md fixed w-full z-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="flex items-center">
          <img src="./public/img/logo.png" alt="Logo Polibatam" class="h-10 w-auto mr-2">
          <span class="text-primary text-xl font-bold">PBL <span class="text-gray-800">PORTOFOLIO</span></span>
        </div>

        <!-- Menu Desktop -->
        <div class="hidden md:flex items-center space-x-6">
          <a href="index.php" class="text-gray-700 hover:text-primary font-medium">Beranda</a>
          <a href="./showcase/showcase.php" class="text-gray-700 hover:text-primary font-medium">Showcase</a>
          <a href="auth/login/login.php" class="bg-primary text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition">Masuk</a>
        </div>

        <!-- Menu Mobile Toggle -->
        <button id="mobile-menu-button" class="md:hidden text-gray-600 hover:text-primary focus:outline-none">
          <i class="fas fa-bars text-xl"></i>
        </button>
      </div>
    </div>

    <div id="mobile-menu" class="md:hidden hidden bg-white shadow-md">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <a href="index.php" class="block px-3 py-2 text-gray-700 hover:text-primary font-medium">Beranda</a>
        <a href="showcase.php" class="block px-3 py-2 text-gray-700 hover:text-primary font-medium">Showcase</a>
        <a href="auth/login/login.php" class="block px-3 py-2 bg-primary text-white rounded-md text-center font-medium mt-2">Masuk</a>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <main class="pt-20">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
      <div class="flex flex-col md:flex-row items-center">
        <!-- Text Content -->
        <div class="w-full md:w-1/2 fade-in">
          <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-800 mb-4">
            Portofolio Proyek <span class="text-primary">PBL</span>
          </h1>
          <p class="text-lg text-gray-600 mb-8">
            Platform digital untuk mendokumentasikan dan memamerkan hasil pembelajaran berbasis proyek (PBL) mahasiswa Polibatam.
          </p>
          <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="auth/register/register.html" class="bg-primary hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium text-center transition shadow-md">
              Daftar Sekarang
            </a>
            <a href="./showcase/showcase.php" class="bg-white border border-primary text-primary hover:bg-gray-50 px-6 py-3 rounded-lg font-medium text-center transition">
              Lihat Showcase
            </a>
          </div>
        </div>

        <!-- Illustration -->
        <div class="w-full md:w-1/2 mt-10 md:mt-0 fade-in-delay">
          <div class="relative mx-auto max-w-md">
            <img src="./public/img/logo.png" alt="Ilustrasi PBL" class="rounded-xl shadow-lg">
          </div>
        </div>
      </div>
    </section>

    <!-- Fitur -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 bg-white">
      <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-800">Fitur Utama</h2>
        <p class="text-gray-600 mt-4">Dibangun khusus untuk mendukung pembelajaran berbasis proyek</p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center p-6 hover:shadow-lg transition rounded-lg">
          <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-project-diagram text-primary text-2xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800">Manajemen Proyek</h3>
          <p class="text-gray-600 mt-2">Tambah, edit, dan kelola proyek per semester & mata kuliah.</p>
        </div>
        <div class="text-center p-6 hover:shadow-lg transition rounded-lg">
          <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-comments text-primary text-2xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800">Evaluasi Dosen</h3>
          <p class="text-gray-600 mt-2">Dosen dapat memberikan komentar & penilaian langsung.</p>
        </div>
        <div class="text-center p-6 hover:shadow-lg transition rounded-lg">
          <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-share-alt text-primary text-2xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800">Bagikan Portofolio</h3>
          <p class="text-gray-600 mt-2">Publikasikan & bagikan hasil karya ke dunia industri.</p>
        </div>
      </div>
    </section>

 
  </main>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <p>&copy; 2026 Politeknik Negeri Batam. All rights reserved.</p>
      <p class="mt-2 text-blue-200">Portofolio Proyek PBL — Belajar Melalui Proyek Nyata</p>
    </div>
  </footer>

  <script>
    const mobileMenuButton = document.getElementById("mobile-menu-button");
    const mobileMenu = document.getElementById("mobile-menu");

    mobileMenuButton.addEventListener("click", () => {
      mobileMenu.classList.toggle("hidden");
    });

    // Tutup mobile menu saat klik di luar
    document.addEventListener("click", (e) => {
      if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
        mobileMenu.classList.add("hidden");
      }
    });
  </script>
</body>
</html>