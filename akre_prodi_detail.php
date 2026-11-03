<?php
include "koneksi.php";

// 1. Ambil ID Prodi dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
  header("Location: akre_prodi.php");
  exit;
}

$id_prodi = mysqli_real_escape_string($koneksi, $_GET['id']);

// 2. Ambil Data Identitas Prodi (Termasuk Email KPS)
$query_prodi = "SELECT * FROM program_studi WHERE id_prodi = '$id_prodi'";
$result_prodi = mysqli_query($koneksi, $query_prodi);
$data_prodi = mysqli_fetch_assoc($result_prodi);

if (!$data_prodi) {
  echo "<script>alert('Data prodi tidak ditemukan!'); window.location='akre_prodi.php';</script>";
  exit;
}

// 3. Ambil Semua Riwayat Akreditasi Prodi tersebut
$query_riwayat = "
    SELECT a.*, l.nama_lembaga, ja.jenis_akreditasi 
    FROM akreditasi a
    LEFT JOIN lembaga l ON a.id_lembaga = l.id_lembaga
    LEFT JOIN jenis_akreditasi ja ON a.id_jenis = ja.id_jenis
    WHERE a.id_prodi = '$id_prodi' AND a.akreditasi = 'program studi'
    ORDER BY a.tanggal_berakhir DESC
";
$result_riwayat = mysqli_query($koneksi, $query_riwayat);

$hari_ini = date('Y-m-d');

// AMBIL EMAIL KPS DARI TABEL PROGRAM STUDI
$email_kps_tampil = !empty($data_prodi['email_kps']) ? $data_prodi['email_kps'] : 'Belum diatur oleh Admin';

// Variabel default untuk info atas
$tipe_akreditasi_tampil = 'Nasional';
$lembaga_terbaru = 'BAN-PT / LAM';

if (mysqli_num_rows($result_riwayat) > 0) {
    $riwayat_terbaru = mysqli_fetch_assoc($result_riwayat);
    
    // Ambil Tipe Akreditasi
    if(isset($riwayat_terbaru['jenis_akreditasi'])){
        $tipe_akreditasi_tampil = $riwayat_terbaru['jenis_akreditasi'];
    }
    
    // Ambil Nama Lembaga Terbaru
    if(!empty($riwayat_terbaru['nama_lembaga'])){
        $lembaga_terbaru = $riwayat_terbaru['nama_lembaga'];
    }
    
    mysqli_data_seek($result_riwayat, 0); 
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Detail Riwayat Akreditasi - <?= htmlspecialchars($data_prodi['nama_program_studi']) ?></title>

  <link href="assets/img/logo/polibatam.svg" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    html, body { height: 100%; }
    body {
      display: flex;
      flex-direction: column;
      background-color: #f0f6ff;
      background-image: linear-gradient(180deg, #eaf3ff 0%, #f8f9fa 100%);
      background-attachment: fixed;
    }
    .main { flex: 1 0 auto; }
    .main-container { margin-top: 100px; padding-bottom: 50px; }
    .btn-back { background: #fff; color: #495057; border: 1px solid #dee2e6; font-weight: 500; transition: 0.3s; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02); }
    .btn-back:hover { background: #0d83fd; color: #fff; border-color: #0d83fd; transform: translateY(-2px); }
    .content-card { background: #fff; border-radius: 16px; box-shadow: 0 6px 25px rgba(13, 131, 253, 0.05); border: 1px solid rgba(13, 131, 253, 0.1); padding: 30px; margin-bottom: 24px; }
    .detail-head { border-left: 5px solid #0d83fd; padding-left: 20px; }
    
    /* CSS BADGE DISAMAKAN DENGAN EFEK KOTAK KPS */
    .badge-custom { 
      font-size: 13px; 
      padding: 6px 14px; 
      border-radius: 8px; 
      background: #e6f2ff; 
      color: #0a6fd8; 
      font-weight: 600; 
      margin-bottom: 10px; 
      display: inline-block; 
      margin-right: 5px;
      border: 1px solid transparent; /* Tambahan agar sama dengan kps-box yang punya border */
      transition: 0.3s; /* Sama persis dengan kps-box */
      cursor: default;
    }

    /* Efek saat disentuh (Hover) - Persis seperti kps-box */
    .badge-custom:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 15px rgba(13, 131, 253, 0.08);
    }
    /* AKHIR CSS BADGE */

    .detail-head h2 { font-size: 32px; font-weight: 800; color: #1e2b4c; margin-bottom: 8px; }
    .detail-head p { color: #6c757d; font-size: 15px; margin: 0; }
    .kps-box { background: #f4f8fe; border-radius: 12px; padding: 20px; border: 1px solid #d0e3fa; display: flex; align-items: flex-start; gap: 15px; transition: 0.3s; }
    .kps-box:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(13, 131, 253, 0.08); }
    .kps-icon { width: 40px; height: 40px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0d83fd; font-size: 20px; box-shadow: 0 2px 10px rgba(13, 131, 253, 0.1); }
    .kps-box .label { font-size: 13px; color: #6c757d; margin-bottom: 4px; }
    .kps-box .mail { font-weight: 700; color: #1e2b4c; font-size: 16px; }
    .kps-box .note { font-size: 13px; color: #1a7f4b; margin-top: 4px; font-weight: 500; }
    .table-custom { vertical-align: middle; margin-bottom: 0; }
    .table-custom thead th { background-color: #f8f9fa; color: #1e2b4c; font-weight: 700; padding: 16px; border-bottom: 2px solid #dee2e6; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
    .table-custom tbody td { padding: 16px; border-bottom: 1px solid #e9ecef; color: #495057; font-size: 15px; }
    .table-hover tbody tr:hover { background-color: #f4f8fe; }
  </style>
</head>

<body class="service-details-page">

  <!-- Navbar -->
  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <img src="assets/img/logo/Polibatam.svg" alt="Logo">
        <h1 class="sitename">P4M Polibatam</h1>
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
            <a href="" class="active"><span>Akreditasi</span><i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li><a href="akre_perguruan.php">Perguruan Tinggi</a></li>
              <li><a href="akre_unit.php">Unit Kerja</a></li>
              <li><a href="akre_prodi.php" class="active">Program Studi</a></li>
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
    <div class="container main-container">

      <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
        <a href="akre_prodi.php" class="btn btn-back btn-sm rounded-pill px-3 py-2">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Akreditasi
        </a>
        <span class="text-muted small fw-medium">/ Detail Riwayat Akreditasi</span>
      </div>

      <div class="content-card" data-aos="fade-up" data-aos-delay="100">
        <div class="row align-items-center">
          <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="detail-head">
              <div>
                <span class="badge-custom">Jurusan <?= htmlspecialchars($data_prodi['nama_jurusan']) ?></span>
                <span class="badge-custom"><?= htmlspecialchars($tipe_akreditasi_tampil) ?></span>
              </div>
              <h2><?= htmlspecialchars($data_prodi['nama_program_studi']) ?></h2>
              <!-- LEMBAGA DIUBAH JADI KAPITAL SEMUA PAKAI strtoupper() -->
              <p>Lembaga Pengakreditasi: <b><?= strtoupper(htmlspecialchars($lembaga_terbaru)) ?></b></p>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="kps-box">
              <div class="kps-icon"><i class="bi bi-envelope-paper-fill"></i></div>
              <div>
                <div class="label">Email KPS / Kaprodi (Reminder Feature)</div>
                <div class="mail">
                  <?= htmlspecialchars($email_kps_tampil) ?>
                </div>
                <div class="note"><i class="bi bi-check-circle-fill me-1"></i> Notif email otomatis H-1 tahun aktif</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="content-card" data-aos="fade-up" data-aos-delay="200">
        <div class="mb-4">
          <h5 class="fw-bold mb-1">Riwayat Akreditasi Per Periode</h5>
          <p class="text-muted small mb-0">Daftar rekam jejak akreditasi dan berkas sertifikat/SK resmi</p>
        </div>

        <div class="table-responsive rounded-3 border">
          <table class="table table-custom table-hover">
            <thead>
              <tr>
                <th width="5%" class="text-center">No</th>
                <th class="text-center">Tanggal Mulai</th>
                <th class="text-center">Tanggal Berakhir</th>
                <th>Peringkat</th>
                <th>Lembaga</th>
                <th class="text-center">Sertifikat / SK</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $no = 1;
              if (mysqli_num_rows($result_riwayat) > 0):
                while ($riwayat = mysqli_fetch_assoc($result_riwayat)):
              ?>
                  <tr>
                    <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                    <td class="text-center text-dark fw-medium"><?= date('d M Y', strtotime($riwayat['tanggal_mulai'])) ?></td>
                    <td class="text-center text-dark fw-medium"><?= date('d M Y', strtotime($riwayat['tanggal_berakhir'])) ?></td>
                    <td><span class="fw-medium"><?= htmlspecialchars($riwayat['peringkat']) ?></span></td>
                    <td><?= htmlspecialchars($riwayat['nama_lembaga'] ?? '-') ?></td>
                    <td class="text-center">
                      <?php if (!empty($riwayat['sertifikat']) && strpos($riwayat['sertifikat'], 'email:') !== 0): ?>
                          <a href="admin/<?= htmlspecialchars($riwayat['sertifikat']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                              <i class="bi bi-file-earmark-text-fill me-1"></i> Lihat Sertifikat
                          </a>
                      <?php else: ?>
                        <span class="text-muted small fst-italic">Belum ada file</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="text-center py-5">
                    <div class="text-muted mb-2"><i class="bi bi-folder-x fs-1"></i></div>
                    <h6 class="fw-bold text-dark">Belum Ada Riwayat</h6>
                    <p class="small text-muted mb-0">Belum ada riwayat akreditasi untuk program studi ini.</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>

  <!-- Footer -->
  <footer id="footer" class="footer">
    <div class="container copyright text-center mt-4">
      <p><strong class="px-1 sitename">Made By Pusat Penjaminan Mutu dan Pengembangan Pembelajaran</strong></p>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script>
    AOS.init({
      duration: 600,
      once: true,
      easing: 'ease-out-cubic'
    });
  </script>
  <script src="assets/js/main.js"></script>
</body>

</html>