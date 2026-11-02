<?php
include '../koneksi.php'; // Pastikan koneksi berhasil

$hasil_kepuasan_jurusan = query("SELECT * FROM hasil_kepuasan_jurusan ORDER BY tahun DESC LIMIT 1")[0];
$hasil_kepuasan_unit = query("SELECT * FROM hasil_kepuasan_unit ORDER BY tahun DESC LIMIT 1")[0];

$today = date('Y-m-d');
$six_months_later = date('Y-m-d', strtotime('+6 months'));

$notif_data = [];

// Notifikasi dari akreditasi_perguruan
$query_perguruan = mysqli_query($koneksi, "
    SELECT id_perguruan, tanggal_berakhir 
    FROM akreditasi_perguruan 
    WHERE tanggal_berakhir <= '$six_months_later'
");

while ($row = mysqli_fetch_assoc($query_perguruan)) {
    $notif_data[] = [
        'type' => 'perguruan',
        'info' => "Akreditasi perguruan tinggi <strong>{$row['id_perguruan']}</strong> akan berakhir pada <strong>{$row['tanggal_berakhir']}</strong>."
    ];
}

// Notifikasi dari akreditasi
$query_prodi = mysqli_query($koneksi, "
    SELECT a.id_akreditasi, a.tanggal_berakhir, a.id_unit, a.id_prodi, 
           u.nama_unit_kerja, p.nama_program_studi 
    FROM akreditasi a
    LEFT JOIN unit u ON a.id_unit = u.id_unit
    LEFT JOIN program_studi p ON a.id_prodi = p.id_prodi
    WHERE a.tanggal_berakhir <= '$six_months_later'
");

while ($row = mysqli_fetch_assoc($query_prodi)) {
    $nama = '';

    if (!empty($row['id_unit']) && empty($row['id_prodi'])) {
        $nama = $row['nama_unit_kerja'];
    } elseif (!empty($row['id_prodi'])) {
        $nama = $row['nama_program_studi'];
    }

    $notif_data[] = [
        'type' => 'prodi',
        'info' => "Akreditasi untuk <strong>$nama</strong> akan berakhir pada <strong>{$row['tanggal_berakhir']}</strong>."
    ];
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
                    <a href="dashboard.php" class="nav-item nav-link active"><i class="fa fa-home me-2"></i>Dashboard</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-cogs me-2"></i>Kelola Layanan</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="hasil_kepuasan_jurusan.php" class="dropdown-item">Hasil Kepuasan Jurusan</a>
                            <a href="hasil_kepuasan_unit.php" class="dropdown-item">Hasil Kepuasan Unit Kerja</a>
                            <a href="layanan.php" class="dropdown-item">Menu Layanan</a>
                            <a href="isi_layanan.php" class="dropdown-item">Isi Layanan</a>
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
                <a href="../index.php" class="btn btn-link text-dark" style="position: absolute; right: 20px;">
                    <i class="fa fa-sign-out-alt"></i>
                </a>
                </div>
            </nav>
            <!-- Navbar -->

            <!-- isi -->
            <div class="container-fluid pt-4 px-4">
                <h5 class="mb-4 text-primary">Dashboard</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <!-- Notifikasi Masa Berlaku Akreditasi isi bagian sini-->
                            <?php if (!empty($notif_data)) : ?>
                            <div class="alert fade show p-4 rounded-4" role="alert" style="background-color: #0d83fd; color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>Perhatian!</strong> Berikut adalah daftar akreditasi yang akan berakhir dalam waktu kurang dari 6 bulan:
                                    </div>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($notif_data as $notif) : ?>
                                        <li><?= $notif['info']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- tambahkan backend untuk ini hasil kepuasan jurusan -->
                            <h6>Hasil Kepuasan Layanan Jurusan Polibatam</h6>
                            <br/>
                            <?php if ($hasil_kepuasan_jurusan['gambar']) { ?>
                                <img src="<?= $hasil_kepuasan_jurusan['gambar']; ?>" alt="Gambar" class="img-fluid services-img" style="border-radius: 15px;">
                            <?php } ?>
                            <p><?= nl2br($hasil_kepuasan_jurusan['text']); ?></p>
                            <br/>

                            <h6>Hasil Kepuasan Layanan Unit Kerja Polibatam</h6>
                            <br/>
                            <?php if ($hasil_kepuasan_unit['gambar']) { ?>
                                <img src="<?= $hasil_kepuasan_unit['gambar']; ?>" alt="Gambar" class="img-fluid services-img" style="border-radius: 15px;">
                            <?php } ?>
                            <p><?= nl2br($hasil_kepuasan_unit['text']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- isi -->
            
            <!-- Footer -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 text-center">
                         Made By Pusat Pengembangan dan Penjaminan Mutu (P4M).
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->

        </div>
        <!-- Content -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>