<?php
include "koneksi.php";

$hari_ini = date('Y-m-d');
$filterJurusan = "";
if (isset($_GET['nama_jurusan']) && $_GET['nama_jurusan'] != "") {
    $selectedJurusan = mysqli_real_escape_string($koneksi, $_GET['nama_jurusan']);
    $filterJurusan = "AND ps.nama_jurusan = '$selectedJurusan'";
}

// =====================================================================
// QUERY AMBIL DATA (Hanya Ambil 1 Data Terbaru per Jenis Akreditasi)
// =====================================================================
$query_tabel = "
    SELECT * FROM (
        SELECT 
            a1.id_akreditasi,
            a1.id_prodi,
            a1.id_lembaga,
            a1.id_jenis,
            a1.peringkat,
            a1.tanggal_mulai,
            a1.tanggal_berakhir,
            ps.nama_program_studi, 
            ps.nama_jurusan, 
            ja.jenis_akreditasi, 
            l.nama_lembaga,
            ROW_NUMBER() OVER (
                PARTITION BY a1.id_prodi, 
                CASE 
                    WHEN LOWER(ja.jenis_akreditasi) = 'internasional' THEN 'int'
                    WHEN LOWER(ja.jenis_akreditasi) LIKE '%tersertifikasi%' THEN 'cert'
                    ELSE 'nas'
                END 
                ORDER BY a1.tanggal_berakhir DESC
            ) as rn
        FROM akreditasi a1
        INNER JOIN program_studi ps ON a1.id_prodi = ps.id_prodi
        LEFT JOIN jenis_akreditasi ja ON a1.id_jenis = ja.id_jenis
        LEFT JOIN lembaga l ON a1.id_lembaga = l.id_lembaga
        WHERE a1.akreditasi = 'program studi' $filterJurusan
    ) AS subquery
    WHERE rn = 1
    ORDER BY nama_jurusan ASC, jenis_akreditasi ASC
";
$result_tabel = mysqli_query($koneksi, $query_tabel);

$data_nasional = [];
$data_internasional = [];
$data_tersertifikasi = [];

$total_semua_nas = 0; $total_unggul_nas = 0; $total_baik_sekali_nas = 0; $total_baik_nas = 0; $total_expire_nas = 0; $total_terakreditasi_nas = 0;
$total_semua_int = 0; $total_unggul_int = 0; $total_baik_sekali_int = 0; $total_baik_int = 0; $total_expire_int = 0; $total_terakreditasi_int = 0;
$total_semua_cert = 0; $total_unggul_cert = 0; $total_baik_sekali_cert = 0; $total_baik_cert = 0; $total_expire_cert = 0; $total_terakreditasi_cert = 0;

while ($row = mysqli_fetch_assoc($result_tabel)) {
    $tipe = isset($row['jenis_akreditasi']) ? strtolower($row['jenis_akreditasi']) : 'nasional';
    
    // Tentukan Tipe
    if (strpos($tipe, 'internasional') !== false) {
        $kategori_tipe = 'int';
    } elseif (strpos($tipe, 'tersertifikasi') !== false) {
        $kategori_tipe = 'cert';
    } else {
        $kategori_tipe = 'nas';
    }

    // Hitung Expire
    $tgl_berakhir_obj = new DateTime($row['tanggal_berakhir']);
    $tgl_sekarang_obj = new DateTime($hari_ini);
    $selisih = $tgl_sekarang_obj->diff($tgl_berakhir_obj)->days;
    $is_expire = ($tgl_berakhir_obj >= $tgl_sekarang_obj && $selisih <= 365);
    $row['is_expire'] = $is_expire;

    // Hitung Kategori Predikat
    $predikat_lower = strtolower($row['peringkat']);
    $kategori = 'terakreditasi';
    if (strpos($predikat_lower, 'unggul') !== false) $kategori = 'unggul';
    elseif (strpos($predikat_lower, 'baik sekali') !== false) $kategori = 'baik sekali';
    elseif (strpos($predikat_lower, 'baik') !== false) $kategori = 'baik';
    $row['kategori'] = $kategori;

    // Masukkan ke Array & Hitung Statistik
    if ($kategori_tipe == 'int') {
        $data_internasional[] = $row;
        $total_semua_int++;
        if ($kategori == 'unggul') $total_unggul_int++;
        elseif ($kategori == 'baik sekali') $total_baik_sekali_int++;
        elseif ($kategori == 'baik') $total_baik_int++;
        else $total_terakreditasi_int++;
        if ($is_expire) $total_expire_int++;
    } elseif ($kategori_tipe == 'cert') {
        $data_tersertifikasi[] = $row;
        $total_semua_cert++;
        if ($kategori == 'unggul') $total_unggul_cert++;
        elseif ($kategori == 'baik sekali') $total_baik_sekali_cert++;
        elseif ($kategori == 'baik') $total_baik_cert++;
        else $total_terakreditasi_cert++;
        if ($is_expire) $total_expire_cert++;
    } else {
        $data_nasional[] = $row;
        $total_semua_nas++;
        if ($kategori == 'unggul') $total_unggul_nas++;
        elseif ($kategori == 'baik sekali') $total_baik_sekali_nas++;
        elseif ($kategori == 'baik') $total_baik_nas++;
        else $total_terakreditasi_nas++;
        if ($is_expire) $total_expire_nas++;
    }
}

// Persentase Nasional
$pct_unggul_nas = ($total_semua_nas > 0) ? round(($total_unggul_nas / $total_semua_nas) * 100) : 0;
$pct_baik_sekali_nas = ($total_semua_nas > 0) ? round(($total_baik_sekali_nas / $total_semua_nas) * 100) : 0;
$pct_baik_nas = ($total_semua_nas > 0) ? round(($total_baik_nas / $total_semua_nas) * 100) : 0;
$pct_terakreditasi_nas = ($total_semua_nas > 0) ? round(($total_terakreditasi_nas / $total_semua_nas) * 100) : 0;

// Persentase Internasional
$pct_unggul_int = ($total_semua_int > 0) ? round(($total_unggul_int / $total_semua_int) * 100) : 0;
$pct_baik_sekali_int = ($total_semua_int > 0) ? round(($total_baik_sekali_int / $total_semua_int) * 100) : 0;
$pct_baik_int = ($total_semua_int > 0) ? round(($total_baik_int / $total_semua_int) * 100) : 0;
$pct_terakreditasi_int = ($total_semua_int > 0) ? round(($total_terakreditasi_int / $total_semua_int) * 100) : 0;

// Persentase Tersertifikasi
$pct_unggul_cert = ($total_semua_cert > 0) ? round(($total_unggul_cert / $total_semua_cert) * 100) : 0;
$pct_baik_sekali_cert = ($total_semua_cert > 0) ? round(($total_baik_sekali_cert / $total_semua_cert) * 100) : 0;
$pct_baik_cert = ($total_semua_cert > 0) ? round(($total_baik_cert / $total_semua_cert) * 100) : 0;
$pct_terakreditasi_cert = ($total_semua_cert > 0) ? round(($total_terakreditasi_cert / $total_semua_cert) * 100) : 0;

// Daftar jurusan
$listJurusan = [];
$qJur = mysqli_query($koneksi, "SELECT DISTINCT nama_jurusan FROM program_studi ORDER BY nama_jurusan ASC");
while ($j = mysqli_fetch_assoc($qJur)) {
    $listJurusan[] = $j['nama_jurusan'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Akreditasi Program Studi - P4M Polibatam</title>
  <link href="assets/img/logo/polibatam.svg" rel="icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

  <style>
    /* UI/UX Diperbagus agar tidak terlihat numpuk */
    body { background-color: #f8f9fa; }
    .nav-tabs-custom { border-bottom: 2px solid #e9ecef; margin-bottom: 25px; }
    .nav-tabs-custom .nav-link { border: none; color: #6c757d; font-weight: 600; padding: 12px 20px; position: relative; background: transparent; transition: 0.3s; }
    .nav-tabs-custom .nav-link:hover { color: #0d83fd; }
    .nav-tabs-custom .nav-link.active { color: #0d83fd; background: transparent; }
    .nav-tabs-custom .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background-color: #0d83fd; }
    
    .modern-card { background: #fff; border-radius: 16px; border: 1px solid #e9ecef; box-shadow: 0 8px 25px rgba(0,0,0,0.04); height: 100%; transition: 0.3s; overflow: hidden; }
    .modern-card .card-header { background: #fff; border-bottom: 1px solid #e9ecef; padding: 20px 25px; }
    
    .stat-card { background: #fff; border-radius: 12px; border: 1px solid #e9ecef; padding: 15px; transition: 0.3s; cursor: pointer; height: 100%; display: flex; flex-direction: column; justify-content: center; }
    .stat-card:hover { box-shadow: 0 8px 15px rgba(0,0,0,0.08); transform: translateY(-3px); border-color: #0d83fd; }
    .stat-card .stat-label { font-size: 13px; color: #6c757d; display: flex; align-items: center; gap: 5px; margin-bottom: 8px; white-space: nowrap; }
    .stat-card .stat-number { font-size: 24px; font-weight: 700; color: #1e2b4c; line-height: 1; margin-bottom: 4px; }
    .stat-card .stat-desc { font-size: 12px; color: #adb5bd; }
    .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .card-warning { background-color: #fff9e6; border-color: #ffeeba; }
    .card-warning .stat-number, .card-warning .stat-label, .card-warning .stat-desc { color: #856404; }
    
    .pill-filter { display: inline-block; padding: 8px 16px; border-radius: 50px; background: #fff; border: 1px solid #dee2e6; color: #495057; font-size: 14px; font-weight: 500; cursor: pointer; transition: 0.2s; }
    .pill-filter:hover { border-color: #0d83fd; color: #0d83fd; }
    .pill-filter.active { background: #0d83fd; border-color: #0d83fd; color: #fff; box-shadow: 0 4px 10px rgba(13,131,253,0.3); }
    
    /* Tabel dirapikan agar lega dan profesional */
    .table-custom { vertical-align: middle; margin-bottom: 0; border-color: #edf2f7; }
    .table-custom thead th { background-color: #f8fafc; color: #475569; font-weight: 600; border-bottom: 1px solid #e2e8f0; padding: 16px 20px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    .table-custom tbody td { padding: 16px 20px; border-bottom: 1px solid #edf2f7; color: #334155; vertical-align: middle; font-size: 14px; }
    .table-custom tbody tr { transition: 0.2s; }
    .table-custom tbody tr:hover { background-color: #f8fbff; }
    
    /* Gaya predikat dibuat teks biasa tanpa kotak warna */
    .predikat-text { font-weight: 600; color: #1e293b; }

    /* Efek Cahaya (Glow) pada Diagram */
    .chart-container {
      position: relative;
      width: 150px;
      height: 150px;
      margin: 0 auto;
      filter: drop-shadow(0px 0px 10px rgba(13, 131, 253, 0.4)); /* Efek cahaya kebiruan */
      transition: filter 0.3s ease;
    }
    .chart-container:hover {
      filter: drop-shadow(0px 0px 15px rgba(13, 131, 253, 0.7)); /* Cahaya makin terang saat di-hover */
    }
  </style>
</head>

<body class="service-details-page">

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

  <main class="main">
    <div class="page-title light-background" style="margin-top: 65px; padding-top: 80px; padding-bottom: 5px;">
      <div class="container">
        <div class="container section-title" data-aos="fade-up">
          <h2>Akreditasi Program Studi</h2>
          <p>Halaman ini menyediakan data Akreditasi Program Studi.</p>
        </div>
      </div>
    </div>

    <section id="service-details" class="service-details section">
      <div class="container" data-aos="fade-up">

        <div class="mb-4" data-aos="fade-up">
            <a href="akreditasi.php" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Menu Akreditasi
            </a>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nasional-tab" data-bs-toggle="tab" data-bs-target="#nasional" type="button" role="tab">Nasional</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="internasional-tab" data-bs-toggle="tab" data-bs-target="#internasional" type="button" role="tab">Internasional</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tersertifikasi-tab" data-bs-toggle="tab" data-bs-target="#tersertifikasi" type="button" role="tab">Tersertifikasi</button>
          </li>
        </ul>

        <div class="tab-content" id="myTabContent">

          <!-- ================= TAB NASIONAL ================= -->
          <div class="tab-pane fade show active" id="nasional" role="tabpanel">
            <div class="row g-3 mb-4 align-items-center">
              <div class="col-lg-3">
                <div class="chart-container">
                  <canvas id="donutChartNasional"></canvas>
                </div>
              </div>

              <div class="col-lg-9">
                <div class="d-flex justify-content-end mb-2">
                  <button onclick="exportExcel('nasional')" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                  </button>
                </div>

                <div class="row g-2">
                  <div class="col">
                    <div class="stat-card" id="stat-card-nas-unggul" onclick="toggleFilterFromCard('nasional', 'unggul')">
                      <div class="stat-label"><span class="dot" style="background:#6BE5E8;"></span> Unggul</div>
                      <div class="stat-number"><?= $total_unggul_nas ?></div>
                      <div class="stat-desc"><?= $pct_unggul_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" id="stat-card-nas-baik sekali" onclick="toggleFilterFromCard('nasional', 'baik sekali')">
                      <div class="stat-label"><span class="dot" style="background:#3DB4D3;"></span> Baik Sekali</div>
                      <div class="stat-number"><?= $total_baik_sekali_nas ?></div>
                      <div class="stat-desc"><?= $pct_baik_sekali_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" id="stat-card-nas-baik" onclick="toggleFilterFromCard('nasional', 'baik')">
                      <div class="stat-label"><span class="dot" style="background:#2B87BB;"></span> Baik</div>
                      <div class="stat-number"><?= $total_baik_nas ?></div>
                      <div class="stat-desc"><?= $pct_baik_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" id="stat-card-nas-terakreditasi" onclick="toggleFilterFromCard('nasional', 'terakreditasi')">
                      <div class="stat-label"><span class="dot" style="background:#5E3463;"></span> Terakreditasi</div>
                      <div class="stat-number"><?= $total_terakreditasi_nas ?></div>
                      <div class="stat-desc"><?= $pct_terakreditasi_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card card-warning" id="stat-card-nas-expire" onclick="setFilter('nasional', 'expire', '1')">
                      <div class="stat-label">Masa Habis Berlaku</div>
                      <div class="stat-number"><?= $total_expire_nas ?></div>
                      <div class="stat-desc">Reminder KPS</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h6 class="fw-bold mb-2">Pilih Jurusan (Filter)</h6>
              <div class="d-flex flex-wrap gap-2" id="filter-pills-nas">
                <button class="pill-filter active" onclick="setFilter('nasional', 'jurusan', 'Semua Jurusan')">Semua Jurusan</button>
                <?php foreach ($listJurusan as $jur): ?>
                  <button class="pill-filter" onclick="setFilter('nasional', 'jurusan', '<?= htmlspecialchars($jur, ENT_QUOTES) ?>')"><?= htmlspecialchars($jur) ?></button>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="modern-card">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h6 class="fw-bold mb-1" id="judul-tabel-nas">Daftar Prodi (Nasional)</h6>
                  <p class="text-muted small mb-0">Hanya menampilkan data akreditasi terbaru yang masih aktif</p>
                </div>
                <input type="text" id="search-input-nas" class="form-control form-control-sm" placeholder="Cari nama prodi..." style="width: 250px; border-radius: 20px;" onkeyup="searchTable('nasional')">
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-custom table-hover" id="tabelNasional">
                    <thead>
                      <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="30%">Program Studi</th>
                        <th class="text-center">Predikat</th>
                        <th class="text-center">Lembaga</th>
                        <th class="text-center">Berlaku Sampai</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($data_nasional) > 0): ?>
                        <?php $no = 1; foreach($data_nasional as $prodi): ?>
                          <tr class="prodi-row-nas" data-jurusan="<?= htmlspecialchars($prodi['nama_jurusan']) ?>" data-kategori="<?= $prodi['kategori'] ?>" data-expire="<?= $prodi['is_expire'] ? '1' : '0' ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                              <div class="fw-bold text-dark"><?= htmlspecialchars($prodi['nama_program_studi']) ?></div>
                              <div class="text-muted small"><?= htmlspecialchars($prodi['nama_jurusan']) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="predikat-text"><?= htmlspecialchars($prodi['peringkat']) ?></span>
                            </td>
                            <td class="text-center text-muted"><?= htmlspecialchars($prodi['nama_lembaga']) ?></td>
                            <td class="text-center">
                                <?= date('d M Y', strtotime($prodi['tanggal_berakhir'])) ?>
                                <?php if($prodi['is_expire']): ?>
                                    <br><span class="badge bg-danger" style="font-size: 10px;">Akan Berakhir</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <a href="akre_prodi_detail.php?id=<?= $prodi['id_prodi'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Detail
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="6" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-folder-x fs-1"></i></div>
                            <h6 class="fw-bold text-dark">Belum Ada Data</h6>
                            <p class="small text-muted mb-0">Data akreditasi program studi nasional belum diisi.</p>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- ================= TAB INTERNASIONAL ================= -->
          <div class="tab-pane fade" id="internasional" role="tabpanel">
            <div class="row g-3 mb-4 align-items-center">
              <div class="col-lg-3">
                <div class="chart-container">
                  <canvas id="donutChartInternasional"></canvas>
                </div>
              </div>

              <div class="col-lg-9">
                <div class="d-flex justify-content-end mb-2">
                  <button onclick="exportExcel('internasional')" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                  </button>
                </div>

                <div class="row g-2">
                  <div class="col">
                    <div class="stat-card" id="stat-card-int-unggul" onclick="toggleFilterFromCard('internasional', 'unggul')">
                      <div class="stat-label"><span class="dot" style="background:#6BE5E8;"></span> Unggul</div>
                      <div class="stat-number"><?= $total_unggul_int ?></div>
                      <div class="stat-desc"><?= $pct_unggul_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" id="stat-card-int-baik sekali" onclick="toggleFilterFromCard('internasional', 'baik sekali')">
                      <div class="stat-label"><span class="dot" style="background:#3DB4D3;"></span> Baik Sekali</div>
                      <div class="stat-number"><?= $total_baik_sekali_int ?></div>
                      <div class="stat-desc"><?= $pct_baik_sekali_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" id="stat-card-int-baik" onclick="toggleFilterFromCard('internasional', 'baik')">
                      <div class="stat-label"><span class="dot" style="background:#2B87BB;"></span> Baik</div>
                      <div class="stat-number"><?= $total_baik_int ?></div>
                      <div class="stat-desc"><?= $pct_baik_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" id="stat-card-int-terakreditasi" onclick="toggleFilterFromCard('internasional', 'terakreditasi')">
                      <div class="stat-label"><span class="dot" style="background:#5E3463;"></span> Terakreditasi</div>
                      <div class="stat-number"><?= $total_terakreditasi_int ?></div>
                      <div class="stat-desc"><?= $pct_terakreditasi_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card card-warning" id="stat-card-int-expire" onclick="setFilter('internasional', 'expire', '1')">
                      <div class="stat-label">Masa Habis Berlaku</div>
                      <div class="stat-number"><?= $total_expire_int ?></div>
                      <div class="stat-desc">Reminder KPS</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h6 class="fw-bold mb-2">Pilih Jurusan (Filter)</h6>
              <div class="d-flex flex-wrap gap-2" id="filter-pills-int">
                <button class="pill-filter active" onclick="setFilter('internasional', 'jurusan', 'Semua Jurusan')">Semua Jurusan</button>
                <?php foreach ($listJurusan as $jur): ?>
                  <button class="pill-filter" onclick="setFilter('internasional', 'jurusan', '<?= htmlspecialchars($jur, ENT_QUOTES) ?>')"><?= htmlspecialchars($jur) ?></button>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="modern-card">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h6 class="fw-bold mb-1" id="judul-tabel-int">Daftar Prodi (Internasional)</h6>
                  <p class="text-muted small mb-0">Hanya menampilkan data akreditasi terbaru yang masih aktif</p>
                </div>
                <input type="text" id="search-input-int" class="form-control form-control-sm" placeholder="Cari nama prodi..." style="width: 250px; border-radius: 20px;" onkeyup="searchTable('internasional')">
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-custom table-hover" id="tabelInternasional">
                    <thead>
                      <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="30%">Program Studi</th>
                        <th class="text-center">Predikat</th>
                        <th class="text-center">Lembaga</th>
                        <th class="text-center">Berlaku Sampai</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($data_internasional) > 0): ?>
                        <?php $no_int = 1; foreach($data_internasional as $prodi_int): ?>
                          <tr class="prodi-row-int" data-jurusan="<?= htmlspecialchars($prodi_int['nama_jurusan']) ?>" data-kategori="<?= $prodi_int['kategori'] ?>" data-expire="<?= $prodi_int['is_expire'] ? '1' : '0' ?>">
                            <td class="text-center"><?= $no_int++ ?></td>
                            <td>
                              <div class="fw-bold text-dark"><?= htmlspecialchars($prodi_int['nama_program_studi']) ?></div>
                              <div class="text-muted small"><?= htmlspecialchars($prodi_int['nama_jurusan']) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="predikat-text"><?= htmlspecialchars($prodi_int['peringkat']) ?></span>
                            </td>
                            <td class="text-center text-muted"><?= htmlspecialchars($prodi_int['nama_lembaga']) ?></td>
                            <td class="text-center">
                                <?= date('d M Y', strtotime($prodi_int['tanggal_berakhir'])) ?>
                                <?php if($prodi_int['is_expire']): ?>
                                    <br><span class="badge bg-danger" style="font-size: 10px;">Akan Berakhir</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <a href="akre_prodi_detail.php?id=<?= $prodi_int['id_prodi'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Detail
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="6" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-globe fs-1"></i></div>
                            <h6 class="fw-bold text-dark">Belum Ada Data Internasional</h6>
                            <p class="small text-muted mb-0">Data akreditasi program studi internasional belum diisi.</p>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- ================= TAB TERSERTIFIKASI ================= -->
          <div class="tab-pane fade" id="tersertifikasi" role="tabpanel">
            <div class="row g-3 mb-4 align-items-center">
              <div class="col-lg-3">
                <div class="chart-container">
                  <canvas id="donutChartTersertifikasi"></canvas>
                </div>
              </div>

              <div class="col-lg-9">
                <div class="d-flex justify-content-end mb-2">
                  <button onclick="exportExcel('tersertifikasi')" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                  </button>
                </div>

                <div class="row g-2">
                  <div class="col">
                    <div class="stat-card" id="stat-card-cert-unggul" onclick="toggleFilterFromCard('tersertifikasi', 'unggul')">
                      <div class="stat-label"><span class="dot" style="background:#6BE5E8;"></span> Unggul</div>
                      <div class="stat-number"><?= $total_unggul_cert ?></div>
                      <div class="stat-desc"><?= $pct_unggul_cert ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" id="stat-card-cert-baik sekali" onclick="toggleFilterFromCard('tersertifikasi', 'baik sekali')">
                      <div class="stat-label"><span class="dot" style="background:#3DB4D3;"></span> Baik Sekali</div>
                      <div class="stat-number"><?= $total_baik_sekali_cert ?></div>
                      <div class="stat-desc"><?= $pct_baik_sekali_cert ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" id="stat-card-cert-baik" onclick="toggleFilterFromCard('tersertifikasi', 'baik')">
                      <div class="stat-label"><span class="dot" style="background:#2B87BB;"></span> Baik</div>
                      <div class="stat-number"><?= $total_baik_cert ?></div>
                      <div class="stat-desc"><?= $pct_baik_cert ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" id="stat-card-cert-terakreditasi" onclick="toggleFilterFromCard('tersertifikasi', 'terakreditasi')">
                      <div class="stat-label"><span class="dot" style="background:#5E3463;"></span> Terakreditasi</div>
                      <div class="stat-number"><?= $total_terakreditasi_cert ?></div>
                      <div class="stat-desc"><?= $pct_terakreditasi_cert ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card card-warning" id="stat-card-cert-expire" onclick="setFilter('tersertifikasi', 'expire', '1')">
                      <div class="stat-label">Masa Habis Berlaku</div>
                      <div class="stat-number"><?= $total_expire_cert ?></div>
                      <div class="stat-desc">Reminder KPS</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h6 class="fw-bold mb-2">Pilih Jurusan (Filter)</h6>
              <div class="d-flex flex-wrap gap-2" id="filter-pills-cert">
                <button class="pill-filter active" onclick="setFilter('tersertifikasi', 'jurusan', 'Semua Jurusan')">Semua Jurusan</button>
                <?php foreach ($listJurusan as $jur): ?>
                  <button class="pill-filter" onclick="setFilter('tersertifikasi', 'jurusan', '<?= htmlspecialchars($jur, ENT_QUOTES) ?>')"><?= htmlspecialchars($jur) ?></button>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="modern-card">
              <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                  <h6 class="fw-bold mb-1" id="judul-tabel-cert">Daftar Prodi (Tersertifikasi)</h6>
                  <p class="text-muted small mb-0">Hanya menampilkan data akreditasi terbaru yang masih aktif</p>
                </div>
                <input type="text" id="search-input-cert" class="form-control form-control-sm" placeholder="Cari nama prodi..." style="width: 250px; border-radius: 20px;" onkeyup="searchTable('tersertifikasi')">
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-custom table-hover" id="tabelTersertifikasi">
                    <thead>
                      <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="30%">Program Studi</th>
                        <th class="text-center">Predikat</th>
                        <th class="text-center">Lembaga</th>
                        <th class="text-center">Berlaku Sampai</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($data_tersertifikasi) > 0): ?>
                        <?php $no_cert = 1; foreach($data_tersertifikasi as $prodi_cert): ?>
                          <tr class="prodi-row-cert" data-jurusan="<?= htmlspecialchars($prodi_cert['nama_jurusan']) ?>" data-kategori="<?= $prodi_cert['kategori'] ?>" data-expire="<?= $prodi_cert['is_expire'] ? '1' : '0' ?>">
                            <td class="text-center"><?= $no_cert++ ?></td>
                            <td>
                              <div class="fw-bold text-dark"><?= htmlspecialchars($prodi_cert['nama_program_studi']) ?></div>
                              <div class="text-muted small"><?= htmlspecialchars($prodi_cert['nama_jurusan']) ?></div>
                            </td>
                            <td class="text-center">
                                <span class="predikat-text"><?= htmlspecialchars($prodi_cert['peringkat']) ?></span>
                            </td>
                            <td class="text-center text-muted"><?= htmlspecialchars($prodi_cert['nama_lembaga']) ?></td>
                            <td class="text-center">
                                <?= date('d M Y', strtotime($prodi_cert['tanggal_berakhir'])) ?>
                                <?php if($prodi_cert['is_expire']): ?>
                                    <br><span class="badge bg-danger" style="font-size: 10px;">Akan Berakhir</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <a href="akre_prodi_detail.php?id=<?= $prodi_cert['id_prodi'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                Detail
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="6" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-patch-check fs-1"></i></div>
                            <h6 class="fw-bold text-dark">Belum Ada Data Tersertifikasi</h6>
                            <p class="small text-muted mb-0">Data akreditasi program studi tersertifikasi belum diisi.</p>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </section>
  </main>

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
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      AOS.init({ duration: 600, once: true, easing: 'ease-out-cubic' });

      const softColors = ['#6BE5E8', '#3DB4D3', '#2B87BB', '#5E3463']; 
      const labelsKategori = ['Unggul', 'Baik Sekali', 'Baik', 'Terakreditasi'];

      // Simpan objek chart di sini agar bisa diakses secara global
      window.chartInstances = {};

      function buatDonut(suf, data, total) {
        const canvas = document.getElementById('donutChart' + suf);
        let terpilih = null;
        let teksAngka = String(total);
        let teksLabel = 'data';

        const angkaTengah = {
          id: 'angkaTengah',
          afterDatasetsDraw(c) {
            const { ctx, chartArea } = c;
            const cx = (chartArea.left + chartArea.right) / 2;
            const cy = (chartArea.top + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#fff';
            ctx.shadowColor = 'rgba(0,0,0,0.55)';
            ctx.shadowBlur = 6;
            ctx.font = '700 32px Nunito, Inter, sans-serif';
            ctx.fillText(teksAngka, cx, cy - 8);
            ctx.font = '600 13px Nunito, Inter, sans-serif';
            ctx.fillText(teksLabel, cx, cy + 16);
            ctx.restore();
          }
        };

        const chart = new Chart(canvas.getContext('2d'), {
          type: 'doughnut',
          plugins: [angkaTengah],
          data: {
            labels: labelsKategori,
            datasets: [{
              data: data,
              backgroundColor: softColors,
              borderWidth: 0,
              offset: [0, 0, 0, 0],
              hoverOffset: 4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '0%', 
            layout: { padding: 6 },
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: 'rgba(0,0,0,0.92)',
                padding: 8,
                titleFont: { size: 12, weight: 'bold' },
                bodyFont: { size: 12 },
                callbacks: {
                  title: (items) => items[0].label,
                  label: (ctx) => {
                    const jumlah = ctx.dataset.data.reduce((a, b) => a + b, 0);
                    const persen = jumlah ? Math.round((ctx.parsed / jumlah) * 100) : 0;
                    return ' ' + ctx.parsed + ' (' + persen + '%)';
                  }
                }
              }
            },
            onHover: (evt, els) => { evt.native.target.style.cursor = els.length ? 'pointer' : 'default'; },
            onClick: (evt, els) => {
              const tab = suf.toLowerCase() === 'nasional' ? 'nasional' : (suf.toLowerCase() === 'internasional' ? 'internasional' : 'tersertifikasi');
              
              if (!els.length || terpilih === els[0].index) {
                // Jika diklik di area kosong atau elemen yang sama, reset filter
                terpilih = null;
                window.setFilter(tab, 'kategori', 'semua');
              } else {
                // Jika memilih elemen baru
                terpilih = els[0].index;
                let kategoriName = labelsKategori[terpilih].toLowerCase();
                window.setFilter(tab, 'kategori', kategoriName);
              }
              
              const ds = chart.data.datasets[0];
              ds.offset = ds.data.map((_, i) => i === terpilih ? 6 : 0);
              teksAngka = String(terpilih === null ? total : data[terpilih]);
              teksLabel = terpilih === null ? 'data' : labelsKategori[terpilih];
              chart.update();
            }
          }
        });

        // Simpan instance chart
        window.chartInstances[suf.toLowerCase()] = {
          chart: chart,
          data: data,
          total: total,
          labels: labelsKategori,
          updateVisual: function(kategoriName) {
            if (!kategoriName || kategoriName === 'semua' || kategoriName === 'expire') {
              terpilih = null;
            } else {
              terpilih = this.labels.map(l => l.toLowerCase()).indexOf(kategoriName);
              if (terpilih === -1) terpilih = null; 
            }
            const ds = this.chart.data.datasets[0];
            ds.offset = ds.data.map((_, i) => i === terpilih ? 6 : 0);
            teksAngka = String(terpilih === null ? this.total : this.data[terpilih]);
            teksLabel = terpilih === null ? 'data' : this.labels[terpilih];
            this.chart.update();
          }
        };
      }

      buatDonut('Nasional', [<?= $total_unggul_nas ?>, <?= $total_baik_sekali_nas ?>, <?= $total_baik_nas ?>, <?= $total_terakreditasi_nas ?>], <?= $total_semua_nas ?>);
      buatDonut('Internasional', [<?= $total_unggul_int ?>, <?= $total_baik_sekali_int ?>, <?= $total_baik_int ?>, <?= $total_terakreditasi_int ?>], <?= $total_semua_int ?>);
      buatDonut('Tersertifikasi', [<?= $total_unggul_cert ?>, <?= $total_baik_sekali_cert ?>, <?= $total_baik_cert ?>, <?= $total_terakreditasi_cert ?>], <?= $total_semua_cert ?>);
    });

    let filterState = {
      nasional: { jurusan: "Semua Jurusan", kategori: "semua", expire: "0", search: "" },
      internasional: { jurusan: "Semua Jurusan", kategori: "semua", expire: "0", search: "" },
      tersertifikasi: { jurusan: "Semua Jurusan", kategori: "semua", expire: "0", search: "" }
    };

    function suffix(tab) { 
        if(tab === 'nasional') return 'nas';
        if(tab === 'internasional') return 'int';
        return 'cert';
    }

    window.setFilter = function(tab, type, value) {
      filterState[tab][type] = value;

      if (type === 'jurusan') {
        filterState[tab].kategori = "semua";
        filterState[tab].expire = "0";
        
        // Reset visual chart ke default saat ganti jurusan
        if(window.chartInstances && window.chartInstances[tab]) {
            window.chartInstances[tab].updateVisual('semua');
        }

        document.querySelectorAll(`#filter-pills-${suffix(tab)} .pill-filter`).forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll(`#filter-pills-${suffix(tab)} .pill-filter`).forEach(btn => {
          if (btn.innerText.trim() === value) btn.classList.add('active');
        });
      } else if (type === 'kategori' || type === 'expire') {
        filterState[tab].jurusan = "Semua Jurusan";
        if (type === 'kategori') {
            filterState[tab].expire = "0";
            // Update visual chart jika yang diklik adalah kotak statistik
            if(window.chartInstances && window.chartInstances[tab]) {
                window.chartInstances[tab].updateVisual(value);
            }
        }
        if (type === 'expire') {
            filterState[tab].kategori = "semua";
            if(window.chartInstances && window.chartInstances[tab]) {
                window.chartInstances[tab].updateVisual('semua');
            }
        }
        document.querySelectorAll(`#filter-pills-${suffix(tab)} .pill-filter`).forEach(btn => btn.classList.remove('active'));
        const first = document.querySelector(`#filter-pills-${suffix(tab)} .pill-filter`);
        if (first) first.classList.add('active');
      }
      applyFilter(tab);
    };

    // Fungsi Khusus untuk Kotak Statistik agar bisa klik 2x (Toggle)
    window.toggleFilterFromCard = function(tab, kategori) {
        if (filterState[tab].kategori === kategori) {
            // Jika diklik lagi, kembalikan ke semua
            setFilter(tab, 'kategori', 'semua');
        } else {
            // Jika baru diklik, filter data
            setFilter(tab, 'kategori', kategori);
        }
    };

    function applyFilter(tab) {
      const st = filterState[tab];
      const rows = document.querySelectorAll(`.prodi-row-${suffix(tab)}`);
      rows.forEach(row => {
        const matchJurusan = (st.jurusan === "Semua Jurusan" || row.dataset.jurusan.toLowerCase() === st.jurusan.toLowerCase());
        const matchKategori = (st.kategori === "semua" || row.dataset.kategori === st.kategori);
        const matchExpire = (st.expire === "0" || row.dataset.expire === "1");
        const matchSearch = (st.search === "" || row.innerText.toLowerCase().includes(st.search));
        row.style.display = (matchJurusan && matchKategori && matchExpire && matchSearch) ? '' : 'none';
      });
    }

    window.searchTable = function(tab) {
      filterState[tab].search = document.getElementById(`search-input-${suffix(tab)}`).value.toLowerCase();
      applyFilter(tab);
    };

    window.exportExcel = function(tab) {
      const tableId = tab === 'nasional' ? 'tabelNasional' : (tab === 'internasional' ? 'tabelInternasional' : 'tabelTersertifikasi');
      const title = tab === 'nasional' ? 'AKREDITASI PROGRAM STUDI NASIONAL' : (tab === 'internasional' ? 'AKREDITASI PROGRAM STUDI INTERNASIONAL' : 'SERTIFIKASI PROGRAM STUDI');
      const fileName = tab === 'nasional' ? 'Akreditasi_Prodi_Nasional.xlsx' : (tab === 'internasional' ? 'Akreditasi_Prodi_Internasional.xlsx' : 'Sertifikasi_Prodi.xlsx');

      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Data Akreditasi', {
        pageSetup: { paperSize: 9, orientation: 'landscape', fitToPage: true, fitToWidth: 1, margins: { left: 0.5, right: 0.5, top: 0.75, bottom: 0.75 } }
      });

      worksheet.mergeCells('A1:F1'); 
      const titleCell = worksheet.getCell('A1');
      titleCell.value = title;
      titleCell.font = { name: 'Arial', size: 16, bold: true, color: { argb: 'FF17365D' } };
      titleCell.alignment = { vertical: 'middle', horizontal: 'center' };
      worksheet.getRow(1).height = 35;

      worksheet.mergeCells('A2:F2'); 
      const subTitleCell = worksheet.getCell('A2');
      subTitleCell.value = 'Politeknik Negeri Batam - Pusat Penjaminan Mutu dan Pengembangan Pembelajaran';
      subTitleCell.font = { name: 'Arial', size: 10, italic: true, color: { argb: 'FF555555' } };
      subTitleCell.alignment = { vertical: 'middle', horizontal: 'center' };

      worksheet.mergeCells('A3:F3'); 
      worksheet.getCell('A3').border = { bottom: { style: 'medium', color: { argb: 'FF17365D' } } };

      const headerRow = worksheet.getRow(5);
      headerRow.values = ['No', 'Jurusan', 'Program Studi', 'Predikat', 'Lembaga', 'Berlaku Sampai'];
      
      worksheet.columns = [
        { key: 'no', width: 6 }, { key: 'jurusan', width: 25 }, { key: 'prodi', width: 60 }, 
        { key: 'predikat', width: 20 }, { key: 'lembaga', width: 30 }, { key: 'berakhir', width: 20 }
      ];

      headerRow.eachCell((cell) => {
        cell.font = { name: 'Arial', size: 11, bold: true, color: { argb: 'FFFFFFFF' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF17365D' } };
        cell.alignment = { vertical: 'middle', horizontal: 'center' };
      });
      headerRow.height = 35;

      const table = document.getElementById(tableId);
      const rows = table.querySelectorAll('tbody tr');
      let currentRowIndex = 6; 
      let rowCounter = 1;

      rows.forEach(function(row) {
        if (row.style.display !== 'none' && !row.querySelector('td[colspan]')) {
          const cols = row.querySelectorAll('td');
          const jurusan = cols[1].querySelector('.text-muted').innerText.trim();
          const prodi = cols[1].querySelector('.fw-bold').innerText.trim();
          const predikat = cols[2].innerText.trim();
          const lembaga = cols[3].innerText.trim();
          const berakhir = cols[4].innerText.trim();
          
          const dataRow = worksheet.getRow(currentRowIndex);
          dataRow.values = [rowCounter++, jurusan, prodi, predikat, lembaga, berakhir];

          dataRow.eachCell({ includeEmpty: true }, (cell) => {
            cell.font = { name: 'Arial', size: 11 };
            cell.alignment = { vertical: 'middle', wrapText: true };
            cell.border = { top: { style: 'thin', color: { argb: 'FFD6DCE5' } }, bottom: { style: 'thin', color: { argb: 'FFD6DCE5' } }, left: { style: 'thin', color: { argb: 'FFD6DCE5' } }, right: { style: 'thin', color: { argb: 'FFD6DCE5' } } };
          });
          dataRow.height = 30; 
          currentRowIndex++;
        }
      });

      workbook.xlsx.writeBuffer().then(function(buffer) {
        saveAs(new Blob([buffer], { type: 'application/octet-stream' }), fileName);
      });
    };
  </script>
</body>
</html>