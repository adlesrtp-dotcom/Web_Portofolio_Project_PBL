<?php
// =====================
// DATA PROJECT (contoh)
// =====================
$projects = [
    [
        "name" => "Raffi",
        "title" => "Web Portfolio",
        "year" => "2024 / 2025",
        "description" => "Website portfolio mahasiswa",
        "file" => "#",
        "youtube" => "#"
    ],
    [
        "name" => "Andi",
        "title" => "Sistem Informasi",
        "year" => "2023 / 2024",
        "description" => "Aplikasi manajemen data",
        "file" => "#",
        "youtube" => "#"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background-color: #f6f9fc; }
        .sidebar {
            height: 100vh;
            background: #ffffff;
            border-right: 2px solid #eaeaea;
        }
        .sidebar a {
            display: block;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }
        .sidebar a:hover {
            color: white;
            border-radius: 6px;
        }
        .topbar {
            background: #11a9dc;
            color: white;
            padding: 15px;
            font-size: 22px;
            font-weight: bold;
        }
        .table thead {
            background: #11a9dc;
            color: white;
        }
        .table-hover tbody tr {
            cursor: pointer;
        }
        .btn-custom {
            min-width: 100px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-3">
            <h5 class="mb-4"><i class="fa-solid fa-layer-group"></i> Menu</h5>
            <a href="home.php" class="active"><i class="fa fa-home"></i> Home</a>
            <a href="project.php"><i class="fa fa-folder"></i> Project</a>
            <a href="manage_project.php"><i class="fa fa-gear"></i> Manage Project</a>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Log Out</a>
        </div>

        <!-- CONTENT -->
        <div class="col-md-10 p-0">
            <div class="topbar">Welcome</div>

            <div class="p-4">
                <div class="card shadow-sm">
                    <div class="card-body">

                        <h5 class="mb-3">Project List</h5>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Project Title</th>
                                        <th>Academic Year</th>
                                        <th>Description</th>
                                        <th>File</th>
                                        <th>Youtube</th>
                                    </tr>
                                </thead>
                                <tbody>

                                <?php foreach ($projects as $project): ?>
                                    <tr onclick="openDetail()">
                                        <td><?= $project['name']; ?></td>
                                        <td><?= $project['title']; ?></td>
                                        <td><?= $project['year']; ?></td>
                                        <td><?= $project['description']; ?></td>
                                        <td>
                                            <a href="<?= $project['file']; ?>" class="btn btn-sm btn-outline-primary">
                                                Download
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= $project['youtube']; ?>" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-brands fa-youtube"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <a href="edit_project.php" class="btn btn-warning btn-custom me-2">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="delete_project.php" class="btn btn-danger btn-custom">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
    <button class="next-btn"
                onclick="window.location.href='manage_project.php'">
                Next
            </button>
    
</div>


<script>
    function openDetail() {
        window.location.href = "detail.php";
    }
</script>

</body>
</html>
