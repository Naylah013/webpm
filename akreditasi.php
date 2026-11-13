<?php
include "koneksi.php";

// ==========================================
// 1. QUERY UNTUK STATISTIK & DIAGRAM
// ==========================================

// Hitung Perguruan Tinggi
$q_pt = mysqli_query($koneksi, "SELECT COUNT(DISTINCT id_lembaga) AS total FROM akreditasi_perguruan");
$total_pt = mysqli_fetch_assoc($q_pt)['total'] ?? 0;

// Hitung Unit Kerja
$q_unit = mysqli_query($koneksi, "SELECT COUNT(DISTINCT id_unit) AS total FROM akreditasi WHERE akreditasi = 'unit kerja'");
$total_unit = mysqli_fetch_assoc($q_unit)['total'] ?? 0;

// Hitung Program Studi UNIK (Nasional & Internasional)
$q_prodi = mysqli_query($koneksi, "
    SELECT 
        COUNT(DISTINCT CASE WHEN LOWER(ja.jenis_akreditasi) = 'internasional' THEN a1.id_prodi END) AS total_int,
        COUNT(DISTINCT CASE WHEN LOWER(ja.jenis_akreditasi) != 'internasional' OR ja.jenis_akreditasi IS NULL THEN a1.id_prodi END) AS total_nas
    FROM akreditasi a1
    LEFT JOIN jenis_akreditasi ja ON a1.id_jenis = ja.id_jenis
    WHERE a1.akreditasi = 'program studi'
");
$d_prodi = mysqli_fetch_assoc($q_prodi);
$total_prodi_nas = $d_prodi['total_nas'] ?? 0;
$total_prodi_int = $d_prodi['total_int'] ?? 0;
$total_semua_prodi = $total_prodi_nas + $total_prodi_int;
$total_semua_data = $total_pt + $total_unit + $total_semua_prodi;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Akreditasi Polibatam</title>
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
  
  <!-- Library Chart.js untuk Diagram -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    /* Background Gradasi Putih ke Biru Muda */
    body {
      background: linear-gradient(180deg, #eef7ff 0%, #ffffff 25%, #f4f7f6 100%);
      background-attachment: fixed;
    }

    /* Bagian Judul (Page Title) - Disesuaikan agar tidak tertutup navbar */
    .page-header {
      padding: 140px 0 40px 0; /* Padding atas diperbesar agar turun ke bawah navbar */
      text-align: center;
    }
    .page-header h2 {
      color: #0f172a;
      font-weight: 800;
      letter-spacing: -0.5px;
    }

    /* Kotak Pembungkus Besar (Statistik & Donat) */
    .dashboard-wrapper {
      background: #ffffff;
      border-radius: 24px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.04);
      padding: 2.5rem;
      margin-bottom: 3rem;
      border: 1px solid #edf2f7;
    }

    /* Container Diagram */
    .chart-wrapper {
      position: relative; 
      width: 160px; 
      height: 160px; 
      margin: 0 auto;
    }

    /* Kotak Kecil Statistik */
    .stat-card-small {
      transition: all 0.3s ease;
      border-radius: 16px;
      border: 1px solid #edf2f7;
      background: #fafbfc;
    }
    .stat-card-small:hover {
      background-color: #ffffff !important;
      border-color: #0d83fd !important;
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(13, 131, 253, 0.1) !important;
    }

    /* Efek Kotak Besar Navigasi */
    .card-nav {
      transition: all 0.3s ease-in-out;
      border-radius: 24px;
      border: 1px solid #edf2f7;
      background: #ffffff;
      overflow: hidden;
      position: relative;
    }
    /* Garis warna di atas kotak */
    .card-nav::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 6px;
    }
    .card-nav.pt-card::before { background: #0d83fd; }
    .card-nav.unit-card::before { background: #198754; }
    .card-nav.prodi-card::before { background: #0dcaf0; }

    .card-nav:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.08) !important;
    }
    .card-nav .icon-box {
      transition: all 0.3s ease;
    }
    .card-nav:hover .icon-box {
      transform: scale(1.1) rotate(5deg);
    }

    /* Responsive Border untuk Dashboard */
    @media (min-width: 992px) {
      .border-end-lg {
        border-right: 1px solid #edf2f7;
      }
    }
  </style>
</head>

<body class="index-page">

  <!-- HEADER / NAVBAR -->
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

  <!-- KONTEN UTAMA -->
  <main class="main">
    
    <!-- Page Header (Judul yang sudah diperbaiki) -->
    <div class="page-header">
      <div class="container">
        <h2>Akreditasi Polibatam</h2>
        <p class="text-muted mb-0">Ringkasan data dan navigasi daftar akreditasi Politeknik Negeri Batam.</p>
      </div>
    </div>

    <!-- SECTION STATISTIK (DASHBOARD) -->
    <section class="section pt-0" data-aos="fade-up">
      <div class="container">
        
        <div class="dashboard-wrapper">
          <div class="row align-items-center">
            
            <!-- KIRI: Diagram Donat -->
            <div class="col-lg-3 text-center mb-4 mb-lg-0 border-end-lg">
              <div class="chart-wrapper">
                <canvas id="donutChartAkreditasi"></canvas>
                <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                  <h2 class="mb-0 fw-bold" style="color: #1e2b4c;"><?= $total_semua_data ?></h2>
                  <small class="text-muted fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">TOTAL DATA</small>
                </div>
              </div>
            </div>

            <!-- KANAN: 4 Kotak Kecil -->
            <div class="col-lg-9 ps-lg-5">
              <div class="row row-cols-2 row-cols-md-4 g-3">
                
                <!-- PT -->
                <div class="col">
                  <div class="stat-card-small p-3 h-100 d-flex flex-column justify-content-between">
                    <div class="mb-3">
                      <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="bi bi-building fs-5"></i>
                      </div>
                    </div>
                    <div>
                      <h3 class="fw-bold text-dark mb-1"><?= $total_pt ?></h3>
                      <small class="text-muted fw-bold" style="font-size: 11px;">PERGURUAN TINGGI</small>
                    </div>
                  </div>
                </div>

                <!-- Unit -->
                <div class="col">
                  <div class="stat-card-small p-3 h-100 d-flex flex-column justify-content-between">
                    <div class="mb-3">
                      <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="bi bi-diagram-3 fs-5"></i>
                      </div>
                    </div>
                    <div>
                      <h3 class="fw-bold text-dark mb-1"><?= $total_unit ?></h3>
                      <small class="text-muted fw-bold" style="font-size: 11px;">UNIT KERJA</small>
                    </div>
                  </div>
                </div>

                <!-- Prodi Nasional -->
                <div class="col">
                  <div class="stat-card-small p-3 h-100 d-flex flex-column justify-content-between">
                    <div class="mb-3">
                      <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="bi bi-mortarboard fs-5"></i>
                      </div>
                    </div>
                    <div>
                      <h3 class="fw-bold text-dark mb-1"><?= $total_prodi_nas ?></h3>
                      <small class="text-muted fw-bold" style="font-size: 11px;">PRODI NASIONAL</small>
                    </div>
                  </div>
                </div>

                <!-- Prodi Internasional -->
                <div class="col">
                  <div class="stat-card-small p-3 h-100 d-flex flex-column justify-content-between">
                    <div class="mb-3">
                      <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="bi bi-globe fs-5"></i>
                      </div>
                    </div>
                    <div>
                      <h3 class="fw-bold text-dark mb-1"><?= $total_prodi_int ?></h3>
                      <small class="text-muted fw-bold" style="font-size: 11px;">PRODI INTERNASIONAL</small>
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>
        </div>

      </div>
    </section>

    <!-- SECTION 3 KOTAK BESAR NAVIGASI -->
    <section class="section pt-0 pb-5">
      <div class="container">
        <div class="row g-4 justify-content-center">
          
          <!-- Kotak 1: Perguruan Tinggi -->
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <a href="akre_perguruan.php" class="text-decoration-none">
              <div class="card h-100 card-nav pt-card text-center p-4">
                <div class="card-body d-flex flex-column align-items-center justify-content-center mt-2">
                  <div class="icon-box mb-3 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-building fs-1"></i>
                  </div>
                  <h4 class="card-title fw-bold text-dark mb-2">Perguruan Tinggi</h4>
                  <p class="text-muted small px-2">Lihat informasi dan sertifikat akreditasi tingkat Perguruan Tinggi.</p>
                  <span class="btn btn-outline-primary rounded-pill px-4 mt-3 fw-bold">Lihat Data <i class="bi bi-arrow-right ms-1"></i></span>
                </div>
              </div>
            </a>
          </div>

          <!-- Kotak 2: Unit Kerja -->
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
            <a href="akre_unit.php" class="text-decoration-none">
              <div class="card h-100 card-nav unit-card text-center p-4">
                <div class="card-body d-flex flex-column align-items-center justify-content-center mt-2">
                  <div class="icon-box mb-3 bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-diagram-3 fs-1"></i>
                  </div>
                  <h4 class="card-title fw-bold text-dark mb-2">Unit Kerja</h4>
                  <p class="text-muted small px-2">Lihat informasi dan sertifikat akreditasi tingkat Unit Kerja.</p>
                  <span class="btn btn-outline-success rounded-pill px-4 mt-3 fw-bold">Lihat Data <i class="bi bi-arrow-right ms-1"></i></span>
                </div>
              </div>
            </a>
          </div>

          <!-- Kotak 3: Program Studi -->
          <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <a href="akre_prodi.php" class="text-decoration-none">
              <div class="card h-100 card-nav prodi-card text-center p-4">
                <div class="card-body d-flex flex-column align-items-center justify-content-center mt-2">
                  <div class="icon-box mb-3 bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-mortarboard fs-1"></i>
                  </div>
                  <h4 class="card-title fw-bold text-dark mb-2">Program Studi</h4>
                  <p class="text-muted small px-2">Lihat daftar akreditasi Program Studi Nasional dan Internasional.</p>
                  <span class="btn btn-outline-info rounded-pill px-4 mt-3 fw-bold">Lihat Data <i class="bi bi-arrow-right ms-1"></i></span>
                </div>
              </div>
            </a>
          </div>

        </div>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
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

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

  <!-- SCRIPT UNTUK MENGGAMBAR DIAGRAM DONAT -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      AOS.init({ duration: 600, once: true, easing: 'ease-out-cubic' });

      const dataStatistik = [
        <?= $total_pt ?>, 
        <?= $total_unit ?>, 
        <?= $total_prodi_nas ?>, 
        <?= $total_prodi_int ?>
      ];

      const ctx = document.getElementById('donutChartAkreditasi').getContext('2d');
      new Chart(ctx, {
        type: 'doughnut', 
        data: {
          labels: ['Perguruan Tinggi', 'Unit Kerja', 'Prodi Nasional', 'Prodi Internasional'],
          datasets: [{
            data: dataStatistik,
            backgroundColor: [
              '#0d83fd', // Primary
              '#198754', // Success
              '#0dcaf0', // Info
              '#ffc107'  // Warning
            ],
            borderWidth: 4, 
            borderColor: '#fff',
            hoverOffset: 8
          }]
        },
        options: { 
          responsive: true, 
          maintainAspectRatio: false, 
          cutout: '75%', 
          plugins: { 
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(0,0,0,0.8)',
              padding: 12,
              titleFont: { size: 14, weight: 'bold' },
              bodyFont: { size: 13 },
              callbacks: {
                label: function(context) {
                  let label = context.label || '';
                  if (label) { label += ': '; }
                  if (context.parsed !== null) {
                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                    let percentage = Math.round((context.parsed / total) * 100) + '%';
                    label += context.parsed + ' (' + percentage + ')';
                  }
                  return label;
                }
              }
            }
          } 
        }
      });
    });
  </script>
</body>
</html>