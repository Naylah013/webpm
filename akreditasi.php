<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Akreditasi - P4M Polibatam</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons & Fonts -->
  <link href="assets/img/logo/polibatam.svg" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  
  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  
  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="index-page">

  <!-- ========================================== -->
  <!-- HEADER / NAVBAR (SUDAH DIPERBAIKI) -->
  <!-- ========================================== -->
  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
      
      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="assets/img/logo/polibatam.svg" alt="Logo"><h1 class="sitename">P4M Polibatam</h1>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="index.php#features">Aplikasi</a></li>
          <li class="dropdown">
            <a href=""><span>Layanan</span><i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
                <li><a href="https://e-kuesioner.polibatam.ac.id/polibatam">Kuesioner Kepuasan Layanan</a></li>
                <li><a href="standar_layanan.php">Standar Layanan Polibatam</a></li>
                <li><a href="layanan_pedadogik.php">Layanan Pedadogik</a></li>
            </ul>
          </li>
          
          <!-- MENU AKREDITASI (1 KLIK) -->
          <li><a href="akreditasi.php" class="active">Akreditasi</a></li>

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

  <!-- ========================================== -->
  <!-- KONTEN UTAMA -->
  <!-- ========================================== -->
  <main class="main">
    
    <!-- Page Title -->
    <div class="page-title light-background" style="margin-top: 80px; padding: 60px 0;">
      <div class="container text-center">
        <h2 class="fw-bold">Akreditasi P4M Polibatam</h2>
        <p class="text-muted">Pilih kategori data akreditasi yang ingin Anda lihat di bawah ini.</p>
      </div>
    </div>

    <!-- Section 3 Kotak (Kartu) -->
    <section class="section pt-5">
      <div class="container">
        <div class="row g-4 justify-content-center">
          
          <!-- Kotak 1: Perguruan Tinggi -->
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <a href="akre_perguruan.php" class="text-decoration-none">
              <div class="card h-100 border-0 shadow-sm text-center p-4 card-hover">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                  <div class="icon-box mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-building fs-1"></i>
                  </div>
                  <h4 class="card-title fw-bold text-dark mt-3">Perguruan Tinggi</h4>
                  <p class="text-muted small">Lihat informasi dan sertifikat akreditasi tingkat Perguruan Tinggi.</p>
                  <span class="btn btn-outline-primary rounded-pill px-4 mt-3">Lihat Data <i class="bi bi-arrow-right ms-1"></i></span>
                </div>
              </div>
            </a>
          </div>

          <!-- Kotak 2: Unit Kerja -->
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <a href="akre_unit.php" class="text-decoration-none">
              <div class="card h-100 border-0 shadow-sm text-center p-4 card-hover">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                  <div class="icon-box mb-3 bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-diagram-3 fs-1"></i>
                  </div>
                  <h4 class="card-title fw-bold text-dark mt-3">Unit Kerja</h4>
                  <p class="text-muted small">Lihat informasi dan sertifikat akreditasi tingkat Unit Kerja.</p>
                  <span class="btn btn-outline-success rounded-pill px-4 mt-3">Lihat Data <i class="bi bi-arrow-right ms-1"></i></span>
                </div>
              </div>
            </a>
          </div>

          <!-- Kotak 3: Program Studi -->
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <a href="akre_prodi.php" class="text-decoration-none">
              <div class="card h-100 border-0 shadow-sm text-center p-4 card-hover">
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                  <div class="icon-box mb-3 bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-mortarboard fs-1"></i>
                  </div>
                  <h4 class="card-title fw-bold text-dark mt-3">Program Studi</h4>
                  <p class="text-muted small">Lihat daftar akreditasi Program Studi Nasional dan Internasional.</p>
                  <span class="btn btn-outline-info rounded-pill px-4 mt-3">Lihat Data <i class="bi bi-arrow-right ms-1"></i></span>
                </div>
              </div>
            </a>
          </div>

        </div>
      </div>
    </section>

  </main>

  <!-- ========================================== -->
  <!-- FOOTER -->
  <!-- ========================================== -->
  <footer id="footer" class="footer">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="logo d-flex align-items-center">
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
            <li><a href="https://e-kuesioner.polibatam.ac.id/polibatam">Layanan</a></li>
            <li><a href="akreditasi.php">Akreditasi</a></li>
            <li><a href="buku_isbn.php">Program Fasilitasi P4M</a></li>
            <li><a href="berita.php">Berita</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Layanan</h4>
          <ul>
            <li><a href="https://e-kuesioner.polibatam.ac.id/polibatam">Kuesioner Kepuasan Layanan</a></li>
            <li><a href="standar_layanan.php">Standar Layanan Polibatam</a></li>
            <li><a href="layanan_pedadogik.php">Layanan Pedadogik</a></li>
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

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

  <!-- CSS Tambahan untuk Efek Kotak (Hover) -->
  <style>
    .card-hover {
      transition: all 0.3s ease-in-out;
      border-radius: 15px;
    }
    .card-hover:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    .card-hover .icon-box {
      transition: all 0.3s ease;
    }
    .card-hover:hover .icon-box {
      transform: scale(1.1);
    }
  </style>

</body>
</html>