<?php
session_start();
include "koneksi.php";

/* proteksi halaman */
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

/* ambil judul PBL terakhir mahasiswa */
$id = $_SESSION['id'];
$query = mysqli_query(
    $koneksi,
    "SELECT judul 
     FROM projects 
     WHERE mahasiswa_id='$id'
     ORDER BY created_at DESC
     LIMIT 1"
);

$data = mysqli_fetch_assoc($query);
$judul_pbl = $data ? $data['judul'] : "Belum Upload Project";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard PBL</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, sans-serif;
        }

        body {
            background: #6b6b6b;
            display: flex;
            justify-content: center;
            padding: 30px;
        }

        .container {
            width: 1100px;
            min-height: 600px;
            background: #fff;
            border: 4px solid #0099dd;
        }

        .header {
            background: #00a3e0;
            color: white;
            padding: 20px;
            font-size: 26px;
            font-weight: bold;
        }

        .main {
            display: flex;
            min-height: 500px;
        }

        .sidebar {
            width: 250px;
            border-right: 3px solid #000;
            padding: 25px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li {
            font-size: 20px;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .sidebar a {
            text-decoration: none;
            color: black;
        }

        .sidebar a:hover {
            color: #00a3e0;
        }

        .sidebar .active {
            font-weight: bold;
        }

        .content {
            flex: 1;
            padding: 40px;
            position: relative;
        }

        .cards {
            display: flex;
            gap: 40px;
        }

        .card {
            width: 180px;
            height: 140px;
            background: #00a3e0;
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            padding: 15px;
        }

        .next-btn {
            position: absolute;
            bottom: 30px;
            right: 40px;
            background: #00a3e0;
            color: #000;
            border: none;
            padding: 14px 35px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 12px;
            cursor: pointer;
        }

        .next-btn:hover {
            background: #008cc4;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- HEADER -->
    <div class="header">
        Welcome, <?= $_SESSION['username']; ?>
    </div>

    <!-- MAIN -->
    <div class="main">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <ul>
                <li class="active"><a href="home.php">Home ▶</a></li>
                <li><a href="project.php">Project</a></li>
                <li><a href="manage_project.php">Manage Project</a></li>
                <li><a href="logout.php">Log Out</a></li>
            </ul>
        </div>

        <!-- CONTENT -->
        <div class="content">
            <div class="cards">
                <!-- SATU TITLE PBL -->
                <div class="card">
                    <?= htmlspecialchars($judul_pbl); ?>
                </div>
            </div>

            <button class="next-btn" onclick="window.location.href='project.php'">
                Next
            </button>
        </div>

    </div>
</div>

</body>
</html>
