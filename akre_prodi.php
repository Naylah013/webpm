<?php
include "koneksi.php";

$hari_ini = date('Y-m-d');

// ============================================================
// QUERY UTAMA: akreditasi TERBARU per prodi (MAX tanggal_berakhir)
// ============================================================
$filterJurusan = "";
if (isset($_GET['nama_jurusan']) && $_GET['nama_jurusan'] != "") {
    $selectedJurusan = mysqli_real_escape_string($koneksi, $_GET['nama_jurusan']);
    $filterJurusan = "AND ps.nama_jurusan = '$selectedJurusan'";
}

$query = "
    SELECT a1.*, ps.nama_program_studi, ps.nama_jurusan, ja.jenis_akreditasi, l.nama_lembaga
    FROM akreditasi a1
    INNER JOIN (
        SELECT id_prodi, MAX(tanggal_berakhir) AS max_tanggal
        FROM akreditasi
        WHERE akreditasi = 'program studi'
        GROUP BY id_prodi
    ) a2 ON a1.id_prodi = a2.id_prodi AND a1.tanggal_berakhir = a2.max_tanggal
    INNER JOIN program_studi ps ON a1.id_prodi = ps.id_prodi
    LEFT JOIN jenis_akreditasi ja ON a1.id_jenis = ja.id_jenis
    LEFT JOIN lembaga l ON a1.id_lembaga = l.id_lembaga
    WHERE a1.akreditasi = 'program studi' $filterJurusan
    ORDER BY ps.nama_jurusan ASC, a1.tanggal_berakhir DESC
";
$result = mysqli_query($koneksi, $query);

// Penampung data
$data_nasional = [];
$data_internasional = [];

// Statistik Nasional
$total_semua_nas = 0; $total_unggul_nas = 0; $total_baik_sekali_nas = 0;
$total_baik_nas = 0; $total_expire_nas = 0; $total_terakreditasi_nas = 0;

// Statistik Internasional
$total_semua_int = 0; $total_unggul_int = 0; $total_baik_sekali_int = 0;
$total_baik_int = 0; $total_expire_int = 0; $total_terakreditasi_int = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $tipe = isset($row['jenis_akreditasi']) ? $row['jenis_akreditasi'] : 'Nasional';

    // Hitung expire
    $tgl_berakhir_obj = new DateTime($row['tanggal_berakhir']);
    $tgl_sekarang_obj = new DateTime($hari_ini);
    $selisih = $tgl_sekarang_obj->diff($tgl_berakhir_obj)->days;
    $is_expire = ($tgl_berakhir_obj >= $tgl_sekarang_obj && $selisih <= 365);
    $row['is_expire'] = $is_expire;

    // Hitung kategori predikat
    $predikat_lower = strtolower($row['peringkat']);
    $kategori = 'terakreditasi';
    if (strpos($predikat_lower, 'unggul') !== false) $kategori = 'unggul';
    elseif (strpos($predikat_lower, 'baik sekali') !== false) $kategori = 'baik sekali';
    elseif (strpos($predikat_lower, 'baik') !== false) $kategori = 'baik';
    $row['kategori'] = $kategori;

    if (strtolower($tipe) == 'internasional') {
        $data_internasional[] = $row;
        $total_semua_int++;
        if ($kategori == 'unggul') $total_unggul_int++;
        elseif ($kategori == 'baik sekali') $total_baik_sekali_int++;
        elseif ($kategori == 'baik') $total_baik_int++;
        else $total_terakreditasi_int++;
        if ($is_expire) $total_expire_int++;
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

// Daftar jurusan dinamis (untuk pill filter)
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
    .nav-tabs-custom { border-bottom: 2px solid #e9ecef; margin-bottom: 30px; }
    .nav-tabs-custom .nav-link { border: none; color: #6c757d; font-weight: 600; padding: 12px 20px; position: relative; background: transparent; transition: 0.3s; }
    .nav-tabs-custom .nav-link:hover { color: #0d83fd; }
    .nav-tabs-custom .nav-link.active { color: #0d83fd; background: transparent; }
    .nav-tabs-custom .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background-color: #0d83fd; }
    .modern-card { background: #fff; border-radius: 12px; border: 1px solid #e9ecef; box-shadow: 0 4px 15px rgba(0,0,0,0.03); height: 100%; transition: 0.3s; }
    .modern-card .card-header { background: transparent; border-bottom: 1px solid #e9ecef; padding: 20px; }
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
    .pill-filter.active { background: #0d83fd; border-color: #0d83fd; color: #fff; }
    .table-custom { vertical-align: middle; margin-bottom: 0; }
    .table-custom thead th { background-color: #f8f9fa; color: #1e2b4c; font-weight: 600; border-bottom: 2px solid #dee2e6; padding: 15px; white-space: nowrap; }
    .table-custom tbody td { padding: 15px; border-bottom: 1px solid #e9ecef; color: #495057; vertical-align: middle; }
    .table-custom tbody tr { transition: 0.2s; }
    .table-custom tbody tr:hover { background-color: #f8fbff; }
    .badge-expire { background-color: #fff3cd; color: #856404; font-weight: 600; padding: 6px 12px; border-radius: 6px; font-size: 13px; }
    .badge-aktif { background-color: #d1e7dd; color: #0f5132; font-weight: 600; padding: 6px 12px; border-radius: 6px; font-size: 13px; }
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
          <li class="dropdown">
            <a href=""><span>Akreditasi</span><i class="bi bi-chevron-down toggle-dropdown"></i></a>
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
    <div class="page-title light-background" style="margin-top: 80px;">
      <div class="container">
        <div class="container section-title" data-aos="fade-up">
          <h2>Akreditasi Program Studi</h2>
          <p>Halaman ini menyediakan data Akreditasi Program Studi.</p>
        </div>
      </div>
    </div>

    <section id="service-details" class="service-details section">
      <div class="container" data-aos="fade-up">

        <ul class="nav nav-tabs nav-tabs-custom" id="myTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="nasional-tab" data-bs-toggle="tab" data-bs-target="#nasional" type="button" role="tab">Nasional</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="internasional-tab" data-bs-toggle="tab" data-bs-target="#internasional" type="button" role="tab">Internasional</button>
          </li>
        </ul>

        <div class="tab-content" id="myTabContent">

          <!-- ================= TAB NASIONAL ================= -->
          <div class="tab-pane fade show active" id="nasional" role="tabpanel">

            <div class="row g-3 mb-4 align-items-center">
              <div class="col-lg-2">
                <div class="position-relative mx-auto" style="width: 150px; height: 150px;">
                  <!-- ID ini yang tadinya salah, sekarang sudah benar -->
                  <canvas id="donutChartNasional"></canvas>
                  <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                    <h3 class="mb-0 fw-bold" style="color: #1e2b4c;"><?= $total_semua_nas ?></h3>
                    <small class="text-muted">prodi</small>
                  </div>
                </div>
              </div>

              <div class="col-lg-10">
                <div class="d-flex justify-content-end mb-2">
                  <button onclick="exportExcel('nasional')" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                  </button>
                </div>

                <div class="row g-2">
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('nasional', 'kategori', 'unggul')">
                      <div class="stat-label"><span class="dot" style="background:#4A90E2;"></span> Unggul</div>
                      <div class="stat-number"><?= $total_unggul_nas ?></div>
                      <div class="stat-desc"><?= $pct_unggul_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('nasional', 'kategori', 'baik sekali')">
                      <div class="stat-label"><span class="dot" style="background:#50E3C2;"></span> Baik Sekali</div>
                      <div class="stat-number"><?= $total_baik_sekali_nas ?></div>
                      <div class="stat-desc"><?= $pct_baik_sekali_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('nasional', 'kategori', 'baik')">
                      <div class="stat-label"><span class="dot" style="background:#FFE082;"></span> Baik</div>
                      <div class="stat-number"><?= $total_baik_nas ?></div>
                      <div class="stat-desc"><?= $pct_baik_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('nasional', 'kategori', 'terakreditasi')">
                      <div class="stat-label"><span class="dot" style="background:#F48FB1;"></span> Terakreditasi</div>
                      <div class="stat-number"><?= $total_terakreditasi_nas ?></div>
                      <div class="stat-desc"><?= $pct_terakreditasi_nas ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card card-warning" onclick="setFilter('nasional', 'expire', '1')">
                      <div class="stat-label">Expire < 1 Thn</div>
                      <div class="stat-number"><?= $total_expire_nas ?></div>
                      <div class="stat-desc">Reminder KPS</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h6 class="fw-bold mb-2">Pilih Jurusan (Filter)</h6>
              <p class="text-muted small mb-3">Klik jurusan untuk menyaring tabel</p>
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
                  <h6 class="fw-bold mb-1" id="judul-tabel-nas">Daftar Prodi (Nasional) - Total: <?= $total_semua_nas ?> Prodi</h6>
                  <p class="text-muted small mb-0">Menampilkan daftar program studi berdasarkan filter terpilih</p>
                </div>
                <input type="text" id="search-input-nas" class="form-control form-control-sm" placeholder="Cari nama prodi..." style="width: 250px; border-radius: 20px;" onkeyup="searchTable('nasional')">
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-custom table-hover" id="tabelNasional">
                    <thead>
                      <tr>
                        <th width="4%" class="text-center">No</th>
                        <th width="25%">Program Studi</th>
                        <th class="text-center">Predikat</th>
                        <th class="text-center">Lembaga</th>
                        <th class="text-center">Tanggal Mulai</th>
                        <th class="text-center">Tanggal Berakhir</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($data_nasional) > 0): ?>
                        <?php $no = 1; foreach($data_nasional as $prodi): ?>
                          <tr class="prodi-row-nas" data-jurusan="<?= htmlspecialchars($prodi['nama_jurusan']) ?>" data-kategori="<?= $prodi['kategori'] ?>" data-expire="<?= $prodi['is_expire'] ? '1' : '0' ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                              <b><?= htmlspecialchars($prodi['nama_program_studi']) ?></b><br>
                              <small class="text-muted"><?= htmlspecialchars($prodi['nama_jurusan']) ?></small>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($prodi['peringkat']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($prodi['nama_lembaga']) ?></td>
                            <td class="text-center text-muted"><?= date('d M Y', strtotime($prodi['tanggal_mulai'])) ?></td>
                            <td class="text-center text-muted"><?= date('d M Y', strtotime($prodi['tanggal_berakhir'])) ?></td>
                            <td class="text-center">
                              <?php if($prodi['is_expire']): ?>
                                <span class="badge-expire">Expire < 1 Thn</span>
                              <?php else: ?>
                                <span class="badge-aktif">Aktif</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <a href="akre_prodi_detail.php?id=<?= $prodi['id_prodi'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-eye"></i> Detail
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-folder-x fs-1"></i></div>
                            <h6 class="fw-bold text-dark">Belum Ada Data</h6>
                            <p class="small text-muted mb-0">Data akreditasi program studi nasional belum diisi oleh admin.</p>
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
              <div class="col-lg-2">
                <div class="position-relative mx-auto" style="width: 150px; height: 150px;">
                  <!-- ID ini yang tadinya salah, sekarang sudah benar -->
                  <canvas id="donutChartInternasional"></canvas>
                  <div class="position-absolute top-50 start-50 translate-middle text-center" style="pointer-events: none;">
                    <h3 class="mb-0 fw-bold" style="color: #1e2b4c;"><?= $total_semua_int ?></h3>
                    <small class="text-muted">prodi</small>
                  </div>
                </div>
              </div>

              <div class="col-lg-10">
                <div class="d-flex justify-content-end mb-2">
                  <button onclick="exportExcel('internasional')" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                  </button>
                </div>

                <div class="row g-2">
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('internasional', 'kategori', 'unggul')">
                      <div class="stat-label"><span class="dot" style="background:#4A90E2;"></span> Unggul</div>
                      <div class="stat-number"><?= $total_unggul_int ?></div>
                      <div class="stat-desc"><?= $pct_unggul_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('internasional', 'kategori', 'baik sekali')">
                      <div class="stat-label"><span class="dot" style="background:#50E3C2;"></span> Baik Sekali</div>
                      <div class="stat-number"><?= $total_baik_sekali_int ?></div>
                      <div class="stat-desc"><?= $pct_baik_sekali_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('internasional', 'kategori', 'baik')">
                      <div class="stat-label"><span class="dot" style="background:#FFE082;"></span> Baik</div>
                      <div class="stat-number"><?= $total_baik_int ?></div>
                      <div class="stat-desc"><?= $pct_baik_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card" onclick="setFilter('internasional', 'kategori', 'terakreditasi')">
                      <div class="stat-label"><span class="dot" style="background:#F48FB1;"></span> Terakreditasi</div>
                      <div class="stat-number"><?= $total_terakreditasi_int ?></div>
                      <div class="stat-desc"><?= $pct_terakreditasi_int ?>%</div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="stat-card card-warning" onclick="setFilter('internasional', 'expire', '1')">
                      <div class="stat-label">Expire < 1 Thn</div>
                      <div class="stat-number"><?= $total_expire_int ?></div>
                      <div class="stat-desc">Reminder KPS</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-4">
              <h6 class="fw-bold mb-2">Pilih Jurusan (Filter)</h6>
              <p class="text-muted small mb-3">Klik jurusan untuk menyaring tabel</p>
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
                  <h6 class="fw-bold mb-1" id="judul-tabel-int">Daftar Prodi (Internasional) - Total: <?= $total_semua_int ?> Prodi</h6>
                  <p class="text-muted small mb-0">Menampilkan daftar program studi internasional</p>
                </div>
                <input type="text" id="search-input-int" class="form-control form-control-sm" placeholder="Cari nama prodi..." style="width: 250px; border-radius: 20px;" onkeyup="searchTable('internasional')">
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-custom table-hover" id="tabelInternasional">
                    <thead>
                      <tr>
                        <th width="4%" class="text-center">No</th>
                        <th width="25%">Program Studi</th>
                        <th class="text-center">Predikat</th>
                        <th class="text-center">Lembaga</th>
                        <th class="text-center">Tanggal Mulai</th>
                        <th class="text-center">Tanggal Berakhir</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if(count($data_internasional) > 0): ?>
                        <?php $no_int = 1; foreach($data_internasional as $prodi_int): ?>
                          <tr class="prodi-row-int" data-jurusan="<?= htmlspecialchars($prodi_int['nama_jurusan']) ?>" data-kategori="<?= $prodi_int['kategori'] ?>" data-expire="<?= $prodi_int['is_expire'] ? '1' : '0' ?>">
                            <td class="text-center"><?= $no_int++ ?></td>
                            <td>
                              <b><?= htmlspecialchars($prodi_int['nama_program_studi']) ?></b><br>
                              <small class="text-muted"><?= htmlspecialchars($prodi_int['nama_jurusan']) ?></small>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($prodi_int['peringkat']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($prodi_int['nama_lembaga']) ?></td>
                            <td class="text-center text-muted"><?= date('d M Y', strtotime($prodi_int['tanggal_mulai'])) ?></td>
                            <td class="text-center text-muted"><?= date('d M Y', strtotime($prodi_int['tanggal_berakhir'])) ?></td>
                            <td class="text-center">
                              <?php if($prodi_int['is_expire']): ?>
                                <span class="badge-expire">Expire < 1 Thn</span>
                              <?php else: ?>
                                <span class="badge-aktif">Aktif</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-center">
                              <a href="akre_prodi_detail.php?id=<?= $prodi_int['id_prodi'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="bi bi-eye"></i> Detail
                              </a>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-globe fs-1"></i></div>
                            <h6 class="fw-bold text-dark">Belum Ada Data Internasional</h6>
                            <p class="small text-muted mb-0">Data akreditasi program studi internasional belum diisi oleh admin.</p>
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
            <li><a href="akre_perguruan.php">Akreditasi</a></li>
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

      // ======================================================
      // UPDATE: DONUT CHART KEMBALI MUNCUL
      // ======================================================
      const softColors = ['#4A90E2', '#50E3C2', '#FFE082', '#F48FB1']; 
      const labelsKategori = ['Unggul', 'Baik Sekali', 'Baik', 'Terakreditasi'];

      // Panggil ID yang benar: 'donutChartNasional'
      const ctxNas = document.getElementById('donutChartNasional').getContext('2d');
      new Chart(ctxNas, {
        type: 'doughnut', // Kembalikan ke doughnut
        data: {
          labels: labelsKategori,
          datasets: [{
            data: [<?= $total_unggul_nas ?>, <?= $total_baik_sekali_nas ?>, <?= $total_baik_nas ?>, <?= $total_terakreditasi_nas ?>],
            backgroundColor: softColors,
            borderWidth: 2, 
            borderColor: '#fff'
          }]
        },
        options: { 
          responsive: true, 
          maintainAspectRatio: false, 
          cutout: '75%', // Lubang di tengah donat
          plugins: { 
            legend: { display: false },
            tooltip: {
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

      // Panggil ID yang benar: 'donutChartInternasional'
      const ctxInt = document.getElementById('donutChartInternasional').getContext('2d');
      new Chart(ctxInt, {
        type: 'doughnut', // Kembalikan ke doughnut
        data: {
          labels: labelsKategori,
          datasets: [{
            data: [<?= $total_unggul_int ?>, <?= $total_baik_sekali_int ?>, <?= $total_baik_int ?>, <?= $total_terakreditasi_int ?>],
            backgroundColor: softColors,
            borderWidth: 2, 
            borderColor: '#fff'
          }]
        },
        options: { 
          responsive: true, 
          maintainAspectRatio: false,
          cutout: '75%', // Lubang di tengah donat
          plugins: { 
            legend: { display: false },
            tooltip: {
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

    let filterState = {
      nasional: { jurusan: "Semua Jurusan", kategori: "semua", expire: "0", search: "" },
      internasional: { jurusan: "Semua Jurusan", kategori: "semua", expire: "0", search: "" }
    };

    function suffix(tab) { return tab === 'nasional' ? 'nas' : 'int'; }

    window.setFilter = function(tab, type, value) {
      filterState[tab][type] = value;

      if (type === 'jurusan') {
        filterState[tab].kategori = "semua";
        filterState[tab].expire = "0";
        document.querySelectorAll(`#filter-pills-${suffix(tab)} .pill-filter`).forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll(`#filter-pills-${suffix(tab)} .pill-filter`).forEach(btn => {
          if (btn.innerText.trim() === value) btn.classList.add('active');
        });
      } else if (type === 'kategori' || type === 'expire') {
        filterState[tab].jurusan = "Semua Jurusan";
        if (type === 'kategori') filterState[tab].expire = "0";
        if (type === 'expire') filterState[tab].kategori = "semua";
        document.querySelectorAll(`#filter-pills-${suffix(tab)} .pill-filter`).forEach(btn => btn.classList.remove('active'));
        const first = document.querySelector(`#filter-pills-${suffix(tab)} .pill-filter`);
        if (first) first.classList.add('active');
      }
      applyFilter(tab);
    };

    function applyFilter(tab) {
      const st = filterState[tab];

      let judul = "Daftar Prodi ";
      if (st.jurusan !== "Semua Jurusan") judul += `(${st.jurusan})`;
      else if (st.kategori !== "semua") judul += `(Predikat: ${st.kategori.toUpperCase()})`;
      else if (st.expire === "1") judul += `(Akan Expire < 1 Tahun)`;
      else judul += `(${tab.charAt(0).toUpperCase() + tab.slice(1)})`;

      document.getElementById(`judul-tabel-${suffix(tab)}`).innerText = judul;

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

    // ======================================================
    // EXPORT EXCEL DENGAN FORMAT PROFESIONAL & SIAP CETAK
    // ======================================================
    window.exportExcel = function(tab) {
      const tableId = tab === 'nasional' ? 'tabelNasional' : 'tabelInternasional';
      const title = tab === 'nasional' ? 'AKREDITASI PROGRAM STUDI NASIONAL' : 'AKREDITASI PROGRAM STUDI INTERNASIONAL';
      const fileName = tab === 'nasional' ? 'Akreditasi_Prodi_Nasional.xlsx' : 'Akreditasi_Prodi_Internasional.xlsx';

      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Data Akreditasi', {
        pageSetup: {
          paperSize: 9, // A4
          orientation: 'landscape',
          fitToPage: true,
          fitToWidth: 1,
          fitToHeight: 0,
          margins: {
            left: 0.5, right: 0.5, top: 0.75, bottom: 0.75, header: 0.3, footer: 0.3
          },
          printTitlesRow: '5:5' 
        }
      });

      // 1. JUDUL Akreditaisi
      worksheet.mergeCells('A1:H1');
      const titleCell = worksheet.getCell('A1');
      titleCell.value = title;
      titleCell.font = { name: 'Arial', size: 16, bold: true, color: { argb: 'FF17365D' } };
      titleCell.alignment = { vertical: 'middle', horizontal: 'center' };
      worksheet.getRow(1).height = 35;

      worksheet.mergeCells('A2:H2');
      const subTitleCell = worksheet.getCell('A2');
      subTitleCell.value = 'Politeknik Negeri Batam - Pusat Penjaminan Mutu dan Pengembangan Pembelajaran';
      subTitleCell.font = { name: 'Arial', size: 10, italic: true, color: { argb: 'FF555555' } };
      subTitleCell.alignment = { vertical: 'middle', horizontal: 'center' };
      worksheet.getRow(2).height = 20;

      // Garis pemisah judul
      worksheet.mergeCells('A3:H3');
      const lineCell = worksheet.getCell('A3');
      lineCell.value = '';
      lineCell.border = { bottom: { style: 'medium', color: { argb: 'FF17365D' } } };
      worksheet.getRow(3).height = 15;

      // 2. HEADER TABEL (Baris 5, jarak 1 baris)
      const headerRow = worksheet.getRow(5);
      headerRow.values = ['No', 'Jurusan', 'Program Studi', 'Predikat', 'Lembaga', 'Tgl Mulai', 'Tgl Berakhir', 'Status'];
      
      // Set lebar kolom
      worksheet.columns = [
        { key: 'no', width: 6 },
        { key: 'jurusan', width: 25 },
        { key: 'prodi', width: 65 }, 
        { key: 'predikat', width: 18 },
        { key: 'lembaga', width: 35 }, 
        { key: 'mulai', width: 15 },
        { key: 'berakhir', width: 15 },
        { key: 'status', width: 22 }
      ];

      // Styling Header
      headerRow.eachCell((cell) => {
        cell.font = { name: 'Arial', size: 11, bold: true, color: { argb: 'FFFFFFFF' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF17365D' } }; // Deep Navy
        cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
        cell.border = {
          top: { style: 'thin', color: { argb: 'FFD6DCE5' } },
          left: { style: 'thin', color: { argb: 'FFD6DCE5' } },
          bottom: { style: 'thin', color: { argb: 'FFD6DCE5' } },
          right: { style: 'thin', color: { argb: 'FFD6DCE5' } }
        };
      });
      headerRow.height = 35;

      // 3. ISI TABEL & DATA
      const table = document.getElementById(tableId);
      const rows = table.querySelectorAll('tbody tr');
      let currentRowIndex = 6; 
      let rowCounter = 1;

      // WARNA JURUSAN (LEBIH SOFT)
      function getJurusanColor(jurusan) {
        if (!jurusan) return 'FFFFFFFF';
        const j = jurusan.toLowerCase();
        if (j.includes('manajemen')) return 'FFF9F5FF'; // Soft Lavender
        if (j.includes('informatika')) return 'FFF0F9F5'; // Soft Sage
        if (j.includes('elektro')) return 'FFF0F5FC'; // Soft Blue
        if (j.includes('mesin')) return 'FFFDF2F2'; // Soft Pink
        return 'FFFFFFFF';
      }

      rows.forEach(function(row) {
        if (row.style.display !== 'none' && !row.querySelector('td[colspan]')) {
          const cols = row.querySelectorAll('td');
          
          const jurusan = cols[1].querySelector('small') ? cols[1].querySelector('small').innerText.trim() : '';
          const prodi = cols[1].querySelector('b') ? cols[1].querySelector('b').innerText.trim() : '';
          const predikat = cols[2].innerText.trim();
          const lembaga = cols[3].innerText.trim();
          const mulai = cols[4].innerText.trim();
          const berakhir = cols[5].innerText.trim();
          
          const is_expire = row.getAttribute('data-expire') === '1';
          const status = is_expire ? 'Masa Expire < 1 Thn' : 'Aktif';

          const dataRow = worksheet.getRow(currentRowIndex);
          dataRow.values = [rowCounter++, jurusan, prodi, predikat, lembaga, mulai, berakhir, status];

          const jurusanBgColor = getJurusanColor(jurusan);

          dataRow.eachCell({ includeEmpty: true }, (cell, colNumber) => {
            cell.font = { name: 'Arial', size: 11 };
            cell.alignment = { 
              vertical: 'middle', 
              horizontal: (colNumber === 3 || colNumber === 2 || colNumber === 5) ? 'left' : 'center',
              wrapText: true 
            };
            cell.border = {
              top: { style: 'thin', color: { argb: 'FFD6DCE5' } },
              left: { style: 'thin', color: { argb: 'FFD6DCE5' } },
              bottom: { style: 'thin', color: { argb: 'FFD6DCE5' } },
              right: { style: 'thin', color: { argb: 'FFD6DCE5' } }
            };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: jurusanBgColor } };

            if (colNumber === 8) {
              if (is_expire) {
                cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFF2CC' } };
                cell.font = { name: 'Arial', size: 11, color: { argb: 'FF805600' }, bold: true };
              } else {
                cell.font = { name: 'Arial', size: 11, color: { argb: 'FF1A7F4B' }, bold: true };
              }
            }
          });
          
          // TINGGI BARIS OTOMATIS
          const linesProdi = Math.ceil(prodi.length / 55); 
          const linesLembaga = Math.ceil(lembaga.length / 30); 
          const maxLines = Math.max(linesProdi, linesLembaga);
          dataRow.height = Math.max(35, maxLines * 15); 
          
          currentRowIndex++;
        }
      });

      worksheet.pageSetup.printArea = `A1:H${currentRowIndex - 1}`;

      workbook.xlsx.writeBuffer().then(function(buffer) {
        saveAs(new Blob([buffer], { type: 'application/octet-stream' }), fileName);
      });
    };
  </script>
</body>
</html>