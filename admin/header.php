<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>P4M Polibatam</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

    <style>
    .logo-image {
        height: 40px; 
        width: auto; 
    }
    </style>

</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">

        <!-- Sidebar -->
        <div class="sidebar pe-4 pb-3 bg-light">
            <nav class="navbar bg-light navbar-light">
            <a href="#" class="navbar-brand mx-5 mb-3">
                <span style="font-size: 24px; font-weight: bold; color: #4AC4F3;">
                    <img src="../assets/img/logo/polibatam.svg" alt="" class="logo-image" /> Polibatam
                </span>
            </a>
                <div class="navbar-nav w-100">
                    <a href="dashboard.php" class="nav-item nav-link"><i class="fa fa-home me-2"></i>Dashboard</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-cogs me-2"></i>Kelola Layanan</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="layanan.php" class="dropdown-item">Menu Layanan</a>
                            <a href="isi_layanan.php" class="dropdown-item">Isi Layanan</a>
                            <a href="pedadogik.php" class="dropdown-item">Pedadogik</a>
                            <a href="isi_pedadogik.php" class="dropdown-item">Isi Pedadogik</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-user me-2"></i>Kelola Data</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="unit.php" class="dropdown-item">Unit Kerja</a>
                            <a href="prodi.php" class="dropdown-item">Program Studi</a>
                            <a href="lembaga.php" class="dropdown-item">Lembaga</a>
                            <a href="jenis_akre.php" class="dropdown-item">Jenis Akreditasi</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-graduation-cap me-2"></i>Kelola Akreditasi</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="akreditasi.php" class="dropdown-item">Akreditasi</a>
                            <a href="akre_perguruan.php" class="dropdown-item">Perguruan Tinggi</a>
                            <a href="akre_unit.php" class="dropdown-item">Unit Kerja</a>
                            <a href="akre_prodi.php" class="dropdown-item">Program Studi</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-concierge-bell me-2"></i>Kelola Program</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="panduan.php" class="dropdown-item">Panduan Program</a>
                            <a href="buku_isbn.php" class="dropdown-item">Buku ISBN</a>
                            <a href="video_digital.php" class="dropdown-item">Video Digital</a>
                        </div>
                    </div>
                    <a href="berita.php" class="nav-item nav-link">
                        <i class="fa fa-clipboard-list me-2"></i>Kelola Berita
                    </a>
                </div>
            </nav>
        </div>
        <!-- Sidebar -->

        <!-- Content -->
        <div class="content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-2 shadow-sm">
                <a href="#" class="sidebar-toggler flex-shrink-0"><i class="fa fa-bars"></i></a>
                <div class="navbar-nav align-items-center ms-auto">
                <a href="?logout=true" class="btn btn-link text-dark" style="position: absolute; right: 20px;" title="Logout" 
                onclick="return confirm('Yakin ingin logout?');">
                    <i class="fa fa-sign-out-alt"></i>
                </a>
                </div>
            </nav>
            <!-- Navbar -->
