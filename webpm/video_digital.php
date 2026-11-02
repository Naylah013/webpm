<?php
include "koneksi.php";
$tahun_filter = isset($_GET['tahun']) && $_GET['tahun'] != '' ? $_GET['tahun'] : '';
if ($tahun_filter) {
  $program_video = query("SELECT * FROM program_video WHERE tahun = '$tahun_filter'");
} else {
  $program_video = query("SELECT * FROM program_video");
}
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
        <h2>Video Digital</h2>
        <p>Hibah pembuatan konten video digital dalam penerapan pembelajaran kombinasi.</p>      
      </div><!-- End Section Title -->
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">
      <div class="container">
        <div class="row gy-5">

          <!-- Sisi Kiri -->
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="service-box" style="border-radius: 15px;">
            <h4>Panduan</h4>
            <div class="download-catalog">
            <?php
            $panduan = mysqli_query($koneksi, "SELECT * FROM panduan ORDER BY tanggal DESC LIMIT 1");
            $buku_terbaru = mysqli_fetch_assoc($panduan);
            ?>

            <?php if (!empty($buku_terbaru['buku'])): ?>
            <a href="admin/<?= $buku_terbaru['buku']; ?>" target="_blank">
                <i class="bi bi-filetype-pdf"></i><span>Lihat Panduan</span>
            </a>
            <?php else: ?>
            <p class="text-muted">Tidak ada file panduan</p>
            <?php endif; ?>
            </div>
            <form method="GET" action="">
            <label for="tahun" class="form-label mt-3"><strong>Pilih Tahun Program :</strong></label>
            <select name="tahun" id="tahun" class="form-select" onchange="this.form.submit()">
                <?php
                $tahun_result = mysqli_query($koneksi, "SELECT DISTINCT tahun FROM program_video ORDER BY tahun DESC");
                while ($tahun = mysqli_fetch_assoc($tahun_result)) {
                $selected = (isset($_GET['tahun']) && $_GET['tahun'] == $tahun['tahun']) ? 'selected' : '';
                echo "<option value='{$tahun['tahun']}' $selected>{$tahun['tahun']}</option>";
                }
                ?>
            </select>
            </form>
            </div>
            <div class="help-box d-flex flex-column justify-content-center align-items-center" style="border-radius: 15px;">
            <i class="bi bi-headset help-icon"></i>
            <h4>Ada Pertanyaan?</h4>
            <p class="d-flex align-items-center mt-2 mb-0"><i class="bi bi-telephone me-2"></i> <span>+62 778 469856 469860 ext. 1036</span></p>
            <p class="d-flex align-items-center mt-1 mb-0"><i class="bi bi-envelope me-2"></i> <a href="mailto:p4m@polibatam.ac.id">p4m@polibatam.ac.id</a></p>
            </div>
          </div>

          <!-- Sisi Kanan -->
            <div class="col-lg-8 ps-lg-5" data-aos="fade-up" data-aos-delay="200">
            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-custom">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Penerima</th>
                        <th>Mata Kuliah</th>
                        <th>Penjelasan</th>
                        <th>Akses</th>
                    </tr>
               </thead>
                <tbody>
                    <?php if (!empty($program_video)): ?>
                        <?php $id=1; ?>
                        <?php foreach ($program_video as $row): ?>
                            <tr>
                                <td><?php echo $id; ?></td>
                                <td><?php echo $row["nama_penerima"]; ?></td>
                                <td><?php echo $row["mata_kuliah"]; ?></td>
                                <td><?php echo $row["text"]; ?></td>
                                <td>
                                <?php if (!empty($row['link_akses'])): ?>
                                    <a href="<?php echo $row['link_akses']; ?>" target="_blank" class="btn btn-icon btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No Access</span>
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
      </div>
    </section>

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