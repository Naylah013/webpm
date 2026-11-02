<?php
include "koneksi.php";
$akreditasi_perguruan = query("SELECT ap.*, l.nama_lembaga 
    FROM akreditasi_perguruan ap 
    JOIN lembaga l ON ap.id_lembaga = l.id_lembaga");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>P4M Polibatam</title>
  <meta name="description" content="">
  <meta name="keywords" content="">
  <link href="assets/img/logo/polibatam.svg" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


  <style>
    .table-custom {
      background-color: white;
      color: #333;
      width: 100%;
    }
    .table-custom thead th {
      background-color: #0d83fd;
      color: white;
      text-align: center;
    }
    .table-custom td, .table-custom th {
      text-align: center;
      vertical-align: middle;
    }
    .table-custom .btn-icon {
      padding: 0;
      margin: 0;
      border: none;
      background: transparent;
      font-size: 16px;
    }
    .table-custom .btn-icon i {
      font-size: 18px;
      margin: 0;
    }
    .table-custom .btn-warning i {
      color: #ffc107; 
    }
    .table-custom .btn-danger i {
      color: #dc3545; 
    }
    .table-custom .btn-icon:hover {
      color: #0d83fd;
    }
    .pagination-custom .page-link {
      color: #0d83fd;
    }
    .pagination-custom .page-item.active .page-link {
      background-color: #0d83fd;
      border-color: #0d83fd;
    }

    .search-bar-wrapper {
      display: flex;
      justify-content: flex-start;
      margin-bottom: 20px;
    }

    .search-bar-wrapper form {
      display: flex;
      align-items: center; 
    }

    .search-bar {
      border: 1px solid #0d83fd;
      border-radius: 20px;
      padding: 5px 15px;
      width: 250px;
      margin-right: -40px; 
    }

    .btn-cari {
      background-color: #0d83fd;
      color: white;
      border-radius: 20px;
      padding: 5px 15px;
      border: none;
      margin-left: -8px;
    }

    .btn-cari i {
      font-size: 16px;
    }

    .showing-info {
      font-size: 14px;
      color: #0d83fd;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>

</head>

<body class="service-details-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <!-- navbar -->
      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="assets/img/logo/Polibatam.svg"><h1 class="sitename">P4M Polibatam</h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="index.php#features">Aplikasi</a></li>
          <li class="dropdown">
            <a href=""><span>Layanan</span><i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="hasil_kepuasan.php">Hasil Kepuasan</a></li>
                <li><a href="standar_layanan.php">Standar Layanan Polibatam</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a href=""><span>Akreditasi</span><i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="akre_perguruan.php">Perguruan Tinggi</a></li>
                <li><a href="akre_unit.php">Unit Kerja</a></li>
                <li><a href="akre_prodi.php">Program Studi</a></li>
            </ul>
          </li>
          <li class="dropdown">
            <a href=""><span>Program Fasilitasi P4M</span><i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="buku_isbn.php">Buku ISBN</a></li>
                <li><a href="video_digital.php">Video Digital</a></li>
            </ul>
          </li>
          <li><a href="berita.php">Berita</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
       </nav>
      <a class="btn-getstarted" href="login.php">Login</a>

    </div>
  </header>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2>Akreditasi Perguruan Tinggi</h2>
          <p>Halaman ini menyediakan data Akreditasi Politeknik Negeri Batam.</p>
        </div><!-- End Section Title -->
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">

      <div class="container">
        <!-- bagian isi tabel -->

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                            <thead>
                                <tr>
                                <th>No</th>
                                <th>Peringkat</th>
                                <th>Lembaga</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Berakhir</th>
                                <th>Sertifikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($akreditasi_perguruan)): ?>
                                <?php $id=1; ?>
                                <?php foreach ($akreditasi_perguruan as $row): ?>
                                <tr>
                                    <td><?php echo $id; ?></td>
                                    <td><?php echo $row["peringkat"]; ?></td>
                                    <td><?php echo $row["nama_lembaga"]; ?></td>
                                    <td><?php echo $row["tanggal_mulai"]; ?></td>
                                    <td><?php echo $row["tanggal_berakhir"]; ?></td>
                                    <td>
                                    <?php if (!empty($row['sertifikat'])): ?>
                                        <a href="admin/<?php echo $row['sertifikat']; ?>" target="_blank" class="btn btn-icon btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No File</span>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                                <?php $id++; ?>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="10">Tidak ada data yang tersedia.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                            </table>
                        </div>

      </div>

    </section><!-- /Service Details Section -->

  </main>

  <footer id="footer" class="footer">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">P4M Polibatam</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Lantai 3 Gedung Utama jalan Ahmad Yani, Tlk. Tering Kec. Batam Kota 29461</p>
            <p class="mt-3"><strong>Nomor:</strong> <span>+62 778 469856 469860 ext. 1036</span></p>
            <p><strong>Email:</strong> <span>p4m@polibatam.ac.id</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href="https://www.youtube.com/@penjaminanmutupoliteknikne6754"><i class="bi bi-youtube"></i></a>
            <a href="https://www.polibatam.ac.id/"><i class="bi bi-globe"></i></a> 
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Tautan</h4>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php#features">Aplikasi</a></li>
            <li><a href="hasil_kepuasan.php">Layanan</a></li>
            <li><a href="akre_perguruan.php">Akreditasi</a></li>
            <li><a href="buku_isbn.php">Program Fasilitasi P4M</a></li>
            <li><a href="berita.php">Berita</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Layanan</h4>
          <ul>
            <li><a href="hasil_kepuasan.php">Hasil Kepuasan</a></li>
            <li><a href="standar_layanan.php">Standar Layanan Polibatam</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Akreditasi</h4>
          <ul>
            <li><a href="akre_perguruan.php">Perguruan Tinggi</a></li>
            <li><a href="akre_unit.php">Unit Kerja</a></li>
            <li><a href="akre_prodi.php">Program Studi</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Program Fasilitasi P4M</h4>
          <ul>
            <li><a href="buku_isbn.php">Buku ISBN</a></li>
            <li><a href="video_digital.php">Video Digital</a></li>
          </ul>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p><strong class="px-1 sitename">Made By Pusat Penjaminan Mutu dan Pengembangan Pembelajaran</strong></p>
    </div>

  </footer>

  <!-- Scroll -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/js/main.js"></script>

</body>

</html>