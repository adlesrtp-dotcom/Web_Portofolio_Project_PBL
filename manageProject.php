<?php
// manage_project.php
// Contoh PHP sederhana untuk memproses form

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pbl_title = $_POST['pbl_title'] ?? '';
    $academic_year = $_POST['academic_year'] ?? '';
    $description = $_POST['description'] ?? '';
    $file_link = $_POST['file_link'] ?? '';
    $youtube_url = $_POST['youtube_url'] ?? '';

    // Di sini bisa ditambahkan validasi / simpan ke database
    // Contoh sederhana: tampilkan pesan
    if ($pbl_title && $academic_year) {
        $message = 'Data project berhasil dikirim.';
    } else {
        $message = 'Judul PBL dan Tahun Akademik wajib diisi.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Project</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: #e9e9e9;
        }
        .sidebar {
            background: #ffffff;
            height: 100vh;
            border-right: 2px solid #000;
        }
        .sidebar a {
            display: block;
            padding: 12px;
            font-weight: 500;
            color: #000;
            text-decoration: none;
        }
        .sidebar a.active {
            font-weight: bold;
        }
        .topbar {
            background: #11a9dc;
            height: 70px;
            display: flex;
            align-items: center;
            padding-left: 30px;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        .main-content {
            background: white;
            min-height: calc(100vh - 70px);
            padding: 40px;
        }
        .form-control {
            background: #11a9dc;
            border: none;
            color: white;
        }
        .form-control::placeholder {
            color: #e9f7ff;
        }
        label {
            font-weight: 600;
        }
        .btn-custom {
            width: 140px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-3">
            <h5 class="mb-4"><i class="fa-solid fa-layer-group"></i> Menu</h5>
            <a href="#"><i class="fa fa-home"></i> Home</a>
            <a href="#"><i class="fa fa-folder"></i> Project</a>
            <a href="#" class="active"><i class="fa fa-gear"></i> Manage Project</a>
            <a href="#"><i class="fa fa-sign-out-alt"></i> Log Out</a>
            
        </div>

        <!-- CONTENT -->
        <div class="col-md-10 p-0">
            <div class="topbar">Welcome</div>

            <div class="main-content">

                <?php if ($message): ?>
                    <div class="alert alert-info">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-3">
                            <label>PBL Title</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" name="pbl_title" class="form-control" placeholder="Enter PBL title" value="<?= htmlspecialchars($pbl_title ?? '') ?>">
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-md-3">
                            <label>Academic Year</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" name="academic_year" class="form-control" placeholder="2024 / 2025" value="<?= htmlspecialchars($academic_year ?? '') ?>">
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-md-3">
                            <label>Description</label>
                        </div>
                        <div class="col-md-9">
                            <textarea name="description" class="form-control" rows="3" placeholder="Project description"><?= htmlspecialchars($description ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="row mb-4 align-items-center">
                        <div class="col-md-3">
                            <label>File Link</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" name="file_link" class="form-control" placeholder="File URL" value="<?= htmlspecialchars($file_link ?? '') ?>">
                        </div>
                    </div>

                    <div class="row mb-5 align-items-center">
                        <div class="col-md-3">
                            <label>URL Youtube</label>
                        </div>
                        <div class="col-md-9">
                            <input type="text" name="youtube_url" class="form-control" placeholder="Youtube link" value="<?= htmlspecialchars($youtube_url ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between px-5">
                        <button type="reset" class="btn btn-warning btn-custom">Cancel</button>
                        <button type="submit" class="btn btn-info text-white btn-custom">Submit</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

</body>
</html>
