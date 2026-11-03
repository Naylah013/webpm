<?php
include "../koneksi.php";
/** @var mysqli $koneksi */
include 'header.php';

// ============================================================
// Ambil SEMUA data akreditasi
// ============================================================
$akreditasi = query("SELECT 
a.id_akreditasi,
a.akreditasi,
a.id_unit,
a.id_prodi,
u.nama_unit_kerja,
a.id_lembaga,
a.id_jenis,
ps.nama_jurusan,
ps.nama_program_studi,
a.peringkat,
l.nama_lembaga,
ja.jenis_akreditasi,
a.tanggal_mulai,
a.tanggal_berakhir,
a.sertifikat
FROM akreditasi a
LEFT JOIN unit u ON u.id_unit = a.id_unit
LEFT JOIN program_studi ps ON ps.id_prodi = a.id_prodi
LEFT JOIN lembaga l ON l.id_lembaga = a.id_lembaga
LEFT JOIN jenis_akreditasi ja ON ja.id_jenis = a.id_jenis
ORDER BY a.id_akreditasi DESC
");

// ============================================================
// Pencarian
// ============================================================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $akreditasi = array_filter($akreditasi, function ($item) use ($search) {
        return stripos($item['nama_unit_kerja'] ?? '', $search) !== false
            || stripos($item['nama_jurusan'] ?? '', $search) !== false
            || stripos($item['nama_program_studi'] ?? '', $search) !== false;
    });
}

// ============================================================
// Pisahkan Unit Kerja vs Program Studi
// ============================================================
$data_unit = [];
$data_prodi = [];

foreach ($akreditasi as $row) {
    if (isset($row['akreditasi']) && $row['akreditasi'] === 'Unit Kerja') {
        $data_unit[] = $row;
    } else {
        $data_prodi[] = $row;
    }
}

// ============================================================
// GROUPING Program Studi berdasarkan (jurusan + prodi)
// ============================================================
$grouped_prodi = [];
foreach ($data_prodi as $row) {
    $key = ($row['nama_jurusan'] ?? '') . '||' . ($row['nama_program_studi'] ?? '');
    if (!isset($grouped_prodi[$key])) {
        $grouped_prodi[$key] = [
            'nama_jurusan' => $row['nama_jurusan'],
            'nama_program_studi' => $row['nama_program_studi'],
            'id_prodi' => $row['id_prodi'],
            'rows' => []
        ];
    }
    $grouped_prodi[$key]['rows'][] = $row;
}

// ============================================================
// Daftar jurusan unik untuk tombol filter
// ============================================================
$jurusanList = ['Informatika', 'Elektro', 'Mesin', 'Manajemen'];
?>

<head>
  <style>
    .logo-image { height: 40px; width: auto; }

    .table-custom { background-color: white; color: #333; width: 100%; }
    .table-custom thead th { background-color: #0d83fd; color: white; text-align: center; }
    .table-custom td, .table-custom th { text-align: center; vertical-align: middle; }
    .table-custom .btn-icon { padding: 0; margin: 0; border: none; background: transparent; font-size: 16px; }
    .table-custom .btn-icon i { font-size: 18px; margin: 0; }
    .table-custom .btn-warning i { color: #ffc107; }
    .table-custom .btn-danger i { color: #dc3545; }
    .table-custom .btn-icon:hover { color: #0d83fd; }

    .section-title-admin {
      font-weight: 700;
      color: #0d83fd;
      border-left: 4px solid #0d83fd;
      padding-left: 12px;
      margin: 30px 0 15px 0;
    }

    .search-bar-wrapper { display: flex; justify-content: flex-start; margin-bottom: 20px; }
    .search-bar-wrapper form { display: flex; align-items: center; }
    .search-bar {
      border: 1px solid #0d83fd; border-radius: 20px;
      padding: 5px 15px; width: 250px; margin-right: -40px;
    }
    .btn-cari {
      background-color: #0d83fd; color: white; border-radius: 20px;
      padding: 5px 15px; border: none; margin-left: -8px;
    }
    .btn-cari i { font-size: 18px; }
    .btn-tambah {
      background-color: #0d83fd; color: white; border-radius: 20px;
      padding: 5px 15px; border: none;
    }

    /* Tombol filter jurusan */
    .btn-jurusan {
      background: white;
      color: #0d83fd;
      border: 1.5px solid #0d83fd;
      border-radius: 20px;
      padding: 6px 20px;
      font-weight: 600;
      font-size: 14px;
      transition: 0.2s;
      margin-right: 6px;
      margin-bottom: 6px;
    }
    .btn-jurusan:hover {
      background: #e6f2ff;
    }
    .btn-jurusan.active {
      background: #0d83fd;
      color: white;
    }

    .hidden { display: none; }

    /* Rowspan grouping style */
    .group-cell { background-color: #f8fbff; font-weight: 600; }
  </style>

  <script>
    function showQuestions(value) {
      document.getElementById('id_unit').classList.add('hidden');
      document.getElementById('nama_jurusan').classList.add('hidden');
      document.getElementById('id_prodi').classList.add('hidden');
      if (value == 'Unit Kerja') {
        document.getElementById('id_unit').classList.remove('hidden');
      }
      if (value == 'Program Studi') {
        document.getElementById('nama_jurusan').classList.remove('hidden');
        document.getElementById('id_prodi').classList.remove('hidden');
      }
    }

    // FUNGSI PERBAIKAN: Menghilangkan Duplikat Prodi & Filter Dropdown
    function filterDropdownProdi() {
      var jurusanSelect = document.getElementById('select_jurusan');
      var prodiSelect = document.getElementById('select_prodi');
      var selectedJurusan = jurusanSelect.value;

      // Kosongkan dropdown prodi terlebih dahulu
      prodiSelect.innerHTML = '<option value="0" selected disabled>-- Pilih Program Studi --</option>';

      // Ambil data dari PHP (sudah dalam format JSON)
      var prodiData = <?php 
          $all_prodi = mysqli_query($koneksi, "SELECT id_prodi, nama_program_studi, nama_jurusan FROM program_studi");
          $prodi_list = [];
          $seen = []; // Array untuk melacak nama prodi yang sudah dimasukkan
          
          while($p = mysqli_fetch_assoc($all_prodi)) {
              // Ubah ke huruf kecil dan hilangkan spasi di ujung untuk perbandingan yang aman
              $key = strtolower(trim($p['nama_program_studi']));
              
              // Jika nama prodi belum ada di array $seen, maka masukkan
              if (!in_array($key, $seen)) {
                  $seen[] = $key;
                  $prodi_list[] = $p;
              }
          }
          echo json_encode($prodi_list); 
      ?>;

      // Filter dan masukkan ke dropdown
      prodiData.forEach(function(item) {
        // Hanya tampilkan prodi yang jurusannya cocok dengan pilihan
        if (selectedJurusan === '0' || item.nama_jurusan === selectedJurusan) {
          var opt = document.createElement('option');
          opt.value = item.id_prodi;
          opt.innerHTML = item.nama_program_studi;
          opt.setAttribute('data-jurusan', item.nama_jurusan);
          prodiSelect.appendChild(opt);
        }
      });
    }
  </script>
</head>

<!-- Main Content -->
<div class="container-fluid pt-4 px-4">
  <h5 class="mb-4 text-primary">Kelola Akreditasi Dan Sertifikasi</h5>
  <div class="row g-4">
    <div class="col-12">
      <div class="bg-light rounded h-100 p-4">

        <!-- Search Bar & Tambah -->
        <div class="search-bar-wrapper">
          <div style="display: flex; justify-content: flex-start; align-items: center; gap: 5px;">
            <a class="button btn-tambah" href="#" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Data</a>
            <form action="akreditasi.php" method="get">
              <input type="text" name="search" class="form-control search-bar"
                placeholder="unit, jurusan, atau prodi"
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
              <button type="submit" class="btn btn-cari"><i class="fas fa-search"></i></button>
            </form>
          </div>
        </div>

        <!-- BAGIAN 1 : TABEL UNIT KERJA -->
        <h6 class="section-title-admin">Akreditasi Unit Kerja</h6>
        <div class="table-responsive">
          <table class="table table-hover table-custom">
            <thead>
              <tr>
                <th>No</th>
                <th>Unit Kerja</th>
                <th>Peringkat</th>
                <th>Lembaga</th>
                <th>Jenis Akreditasi</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Berakhir</th>
                <th>Sertifikat</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($data_unit)): ?>
                <?php $id = 1; foreach ($data_unit as $row): ?>
                  <tr>
                    <td><?= $id++ ?></td>
                    <td><?= !empty($row['nama_unit_kerja']) ? htmlspecialchars($row['nama_unit_kerja']) : "Tidak Tersedia" ?></td>
                    <td><?= htmlspecialchars($row['peringkat']) ?></td>
                    <td><?= htmlspecialchars($row['nama_lembaga'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['jenis_akreditasi'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['tanggal_mulai']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal_berakhir']) ?></td>
                    <td>
                      <?php if (!empty($row['sertifikat'])): ?>
                        <a href="<?= htmlspecialchars($row['sertifikat']) ?>" target="_blank" class="btn btn-icon btn-info">
                          <i class="fas fa-eye"></i> View
                        </a>
                      <?php else: ?>
                        <span class="text-muted">No File</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <button class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_akreditasi'] ?>">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?= $row['id_akreditasi'] ?>">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center py-3 text-muted">Tidak ada data Unit Kerja.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- BAGIAN 2 : TABEL PROGRAM STUDI -->
        <h6 class="section-title-admin">Akreditasi Program Studi</h6>

        <!-- Tombol Filter Jurusan -->
        <div class="mb-3">
          <button class="btn-jurusan active" onclick="filterProdi('semua', this)">Semua</button>
          <?php foreach ($jurusanList as $jur): ?>
            <button class="btn-jurusan" onclick="filterProdi('<?= htmlspecialchars($jur) ?>', this)"><?= htmlspecialchars($jur) ?></button>
          <?php endforeach; ?>
        </div>

        <div class="table-responsive">
          <table class="table table-hover table-custom" id="tabelProdi">
            <thead>
              <tr>
                <th>No</th>
                <th>Jurusan</th>
                <th>Program Studi</th>
                <th>Peringkat</th>
                <th>Lembaga</th>
                <th>Jenis Akreditasi</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Berakhir</th>
                <th>Sertifikat</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="tbodyProdi">
              <!-- Tabel akan di-render otomatis oleh JavaScript di bawah -->
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- ===================================================== -->
<!-- MODAL TAMBAH DATA                                     -->
<!-- ===================================================== -->
<?php
if(isset($_POST["submit"])){
    $akreditasi=$_POST["akreditasi"];
    $id_unit = (isset($_POST["id_unit"]) && $_POST["id_unit"] !== "0") ? (int)$_POST["id_unit"] : 0;
    $nama_jurusan = (isset($_POST["nama_jurusan"]) && $_POST["nama_jurusan"] !== "0") ? $_POST["nama_jurusan"] : "Tidak Tersedia";
    $id_prodi = (isset($_POST["id_prodi"]) && $_POST["id_prodi"] !== "0") ? (int)$_POST["id_prodi"] : 0;
    $peringkat=$_POST["peringkat"];
    $id_lembaga = (isset($_POST["id_lembaga"]) && $_POST["id_lembaga"] !== "0") ? (int)$_POST["id_lembaga"] : 0;
    $id_jenis = (isset($_POST["id_jenis"]) && $_POST["id_jenis"] !== "0") ? (int)$_POST["id_jenis"] : 0;
    $tanggal_mulai=$_POST["tanggal_mulai"];
    $tanggal_berakhir=$_POST["tanggal_berakhir"];
    $sertifikat = '';

    if (!empty($_FILES['sertifikat']['name'])) {
        $uploadDir = 'uploads/sertifikat/';
        $fileName = time() . '-' . basename($_FILES['sertifikat']['name']);
        $filePath = $uploadDir . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($fileType == 'pdf') {
            if (move_uploaded_file($_FILES['sertifikat']['tmp_name'], $filePath)) {
                $sertifikat = $filePath;
            } else {
                echo "<script>alert('Gagal mengupload sertifikat!');</script>";
            }
        } else {
            echo "<script>alert('Hanya file dengan format pdf yang diperbolehkan untuk sertifikat!');</script>";
        }
    }

    $sql = "INSERT INTO akreditasi (akreditasi, id_unit, nama_jurusan, id_prodi, peringkat, id_lembaga, id_jenis, tanggal_mulai, tanggal_berakhir, sertifikat) 
            VALUES ('$akreditasi', '$id_unit', '$nama_jurusan', '$id_prodi', '$peringkat', '$id_lembaga', '$id_jenis', '$tanggal_mulai', '$tanggal_berakhir', '$sertifikat')";
    $result=mysqli_query($koneksi,$sql);
    if($result){
        echo "<script>alert('Data telah ditambahkan'); window.location='akreditasi.php'</script>";
    }else{
        echo "<script>alert('Data gagal ditambahkan!'); window.location='akreditasi.php'</script>";
    }
}
?>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahLabel">Tambah Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Akreditasi</label>
            <div class="flex flex-col">
              <div class="flex items-center px-4">
                <input id="akreditasi_unit" type="radio" value="Unit Kerja" name="akreditasi" onclick="showQuestions(this.value)" class="form-check-input" required/>
                <label class="form-label px-2">Unit Kerja</label>
                <input id="akreditasi_program_studi" type="radio" value="Program Studi" name="akreditasi" onclick="showQuestions(this.value)" class="form-check-input" required/>
                <label class="form-label px-2">Program Studi</label>
              </div>
            </div>
          </div>
          <div class="mb-3 hidden" id="id_unit">
            <label for="id_unit" class="form-label">Nama Unit Kerja</label>
            <select class="form-select" name="id_unit">
              <option value="0" selected disabled>-- Pilih Unit Kerja --</option>
              <?php
                $ambil = mysqli_query($koneksi,"SELECT * FROM unit");
                while ($data = mysqli_fetch_assoc($ambil)){
                  echo '<option value="'.$data['id_unit'].'">'.$data['nama_unit_kerja'].'</option>';
                }
              ?>
            </select>
          </div>
          <div class="mb-3 hidden" id="nama_jurusan">
            <label for="nama_jurusan" class="form-label">Nama Jurusan</label>
            <select class="form-select" name="nama_jurusan" id="select_jurusan" onchange="filterDropdownProdi()">
              <option value="0" selected disabled>-- Pilih Jurusan --</option>
              <?php
                $ambil = mysqli_query($koneksi,"SELECT DISTINCT(nama_jurusan) FROM program_studi");
                while ($data = mysqli_fetch_assoc($ambil)){
                  echo '<option value="'.$data['nama_jurusan'].'">'.$data['nama_jurusan'].'</option>';
                }
              ?>
            </select>
          </div>
          <div class="mb-3 hidden" id="id_prodi">
            <label for="id_prodi" class="form-label">Nama Program Studi</label>
            <select class="form-select" name="id_prodi" id="select_prodi">
              <option value="0" selected disabled>-- Pilih Program Studi --</option>
              <!-- Opsi akan diisi oleh JavaScript -->
            </select>
          </div>
          <div class="mb-3">
            <label for="peringkat" class="form-label">Peringkat</label>
            <input type="text" class="form-control" name="peringkat" required>
          </div>
          <div class="mb-3" id="id_lembaga">
            <label for="id_lembaga" class="form-label">Lembaga</label>
            <select class="form-select" name="id_lembaga">
              <option value="0" selected disabled>-- Pilih Lembaga --</option>
              <?php
                $ambil = mysqli_query($koneksi,"SELECT * FROM lembaga");
                while ($data = mysqli_fetch_assoc($ambil)){
                  echo '<option value="'.$data['id_lembaga'].'">'.$data['nama_lembaga'].'</option>';
                }
              ?>
            </select>
          </div>
          <div class="mb-3" id="id_jenis">
            <label for="id_jenis" class="form-label">Jenis Akreditasi</label>
            <select class="form-select" name="id_jenis">
              <option value="0" selected disabled>-- Pilih Jenis Akreditasi --</option>
              <?php
                $ambil = mysqli_query($koneksi,"SELECT * FROM jenis_akreditasi");
                while ($data = mysqli_fetch_assoc($ambil)){
                  echo '<option value="'.$data['id_jenis'].'">'.$data['jenis_akreditasi'].'</option>';
                }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
            <input type="date" class="form-control" name="tanggal_mulai" required>
          </div>
          <div class="mb-3">
            <label for="tanggal_berakhir" class="form-label">Tanggal Berakhir</label>
            <input type="date" class="form-control" name="tanggal_berakhir" required>
          </div>
          <div class="mb-3">
            <label for="sertifikat" class="form-label">Sertifikat</label>
            <input type="file" class="form-control" id="sertifikat" name="sertifikat" accept=".pdf" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ===================================================== -->
<!-- MODAL EDIT + DELETE (loop SEMUA data)                -->
<!-- ===================================================== -->
<?php
// Proses update
if (isset($_POST["update"])) {
    $id_akreditasi = mysqli_real_escape_string($koneksi, $_POST["id_akreditasi"]);
    $peringkat = mysqli_real_escape_string($koneksi, $_POST["peringkat"]);
    $id_lembaga = (int)$_POST["id_lembaga"];
    $id_jenis = (int)$_POST["id_jenis"];
    $tanggal_mulai = mysqli_real_escape_string($koneksi, $_POST["tanggal_mulai"]);
    $tanggal_berakhir = mysqli_real_escape_string($koneksi, $_POST["tanggal_berakhir"]);

    $sertifikatBaru = '';
    $hasNewFile = false;
    if (!empty($_FILES['sertifikat']['name'])) {
        $uploadDir = 'uploads/sertifikat/';
        $fileName = time() . '-' . basename($_FILES['sertifikat']['name']);
        $filePath = $uploadDir . $fileName;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($fileType == 'pdf' && move_uploaded_file($_FILES['sertifikat']['tmp_name'], $filePath)) {
            $sertifikatBaru = $filePath;
            $hasNewFile = true;
        }
    }

    if ($hasNewFile) {
        $sql = "UPDATE akreditasi SET peringkat='$peringkat', id_lembaga='$id_lembaga', id_jenis='$id_jenis',
                tanggal_mulai='$tanggal_mulai', tanggal_berakhir='$tanggal_berakhir', sertifikat='$sertifikatBaru'
                WHERE id_akreditasi='$id_akreditasi'";
    } else {
        $sql = "UPDATE akreditasi SET peringkat='$peringkat', id_lembaga='$id_lembaga', id_jenis='$id_jenis',
                tanggal_mulai='$tanggal_mulai', tanggal_berakhir='$tanggal_berakhir'
                WHERE id_akreditasi='$id_akreditasi'";
    }

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>alert('Data berhasil diupdate'); window.location='akreditasi.php';</script>";
    } else {
        echo "<script>alert('Data gagal diupdate: " . mysqli_error($koneksi) . "'); window.location='akreditasi.php';</script>";
    }
}

// Proses delete
if (isset($_POST["delete"])) {
    $id_akreditasi = mysqli_real_escape_string($koneksi, $_POST["id_akreditasi"]);
    $delete = mysqli_query($koneksi, "DELETE FROM akreditasi WHERE id_akreditasi='$id_akreditasi'");
    if ($delete) {
        echo "<script>alert('Data Berhasil Dihapus'); window.location='akreditasi.php';</script>";
    } else {
        echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='akreditasi.php';</script>";
    }
}

// Loop SEMUA data (unit + prodi) untuk bikin modal edit & delete
$semua_data = array_merge($data_unit, $data_prodi);
foreach ($semua_data as $row):
?>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit<?= $row['id_akreditasi'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Data Akreditasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" enctype="multipart/form-data" action="akreditasi.php">
        <input type="hidden" name="id_akreditasi" value="<?= $row['id_akreditasi'] ?>">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Peringkat</label>
            <input type="text" class="form-control" name="peringkat" value="<?= htmlspecialchars($row['peringkat']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Lembaga</label>
            <select class="form-select" name="id_lembaga">
              <?php
                $lmb = mysqli_query($koneksi,"SELECT * FROM lembaga");
                while ($d = mysqli_fetch_assoc($lmb)){
                  $sel = ($d['id_lembaga'] == $row['id_lembaga']) ? 'selected' : '';
                  echo '<option value="'.$d['id_lembaga'].'" '.$sel.'>'.$d['nama_lembaga'].'</option>';
                }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Jenis Akreditasi</label>
            <select class="form-select" name="id_jenis">
              <?php
                $jn = mysqli_query($koneksi,"SELECT * FROM jenis_akreditasi");
                while ($d = mysqli_fetch_assoc($jn)){
                  $sel = ($d['id_jenis'] == $row['id_jenis']) ? 'selected' : '';
                  echo '<option value="'.$d['id_jenis'].'" '.$sel.'>'.$d['jenis_akreditasi'].'</option>';
                }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" class="form-control" name="tanggal_mulai" value="<?= htmlspecialchars($row['tanggal_mulai']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Tanggal Berakhir</label>
            <input type="date" class="form-control" name="tanggal_berakhir" value="<?= htmlspecialchars($row['tanggal_berakhir']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Sertifikat (Biarkan kosong jika tidak diganti)</label>
            <input type="file" class="form-control" name="sertifikat" accept=".pdf">
            <?php if (!empty($row['sertifikat'])): ?>
              <small class="text-muted d-block mt-1">File saat ini: 
                <a href="<?= htmlspecialchars($row['sertifikat']) ?>" target="_blank">Lihat</a>
              </small>
            <?php endif; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="modalDelete<?= $row['id_akreditasi'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Hapus Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="akreditasi.php">
        <input type="hidden" name="id_akreditasi" value="<?= $row['id_akreditasi'] ?>">
        <div class="modal-body">
          <p>Apakah Anda yakin ingin menghapus data <b><?= $row['id_akreditasi'] ?></b>?</p>
        </div>
        <div class="modal-footer">
          <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php endforeach; ?>

<!-- ===================================================== -->
<!-- JAVASCRIPT UNTUK RENDER TABEL & FILTER PRODI         -->
<!-- ===================================================== -->
<script>
// 1. Ambil data dari PHP dan ubah ke JSON
const dataProdi = <?= json_encode(array_values($grouped_prodi)) ?>;

// 2. Fungsi untuk merender tabel berdasarkan filter
function renderTabelProdi(filterJurusan = 'semua') {
  const tbody = document.getElementById('tbodyProdi');
  tbody.innerHTML = ''; // Kosongkan tabel

  let html = '';
  let no = 1;
  let adaData = false;

  dataProdi.forEach(group => {
    // Cek apakah grup ini sesuai dengan filter
    const jurusanGrup = (group.nama_jurusan || '').toLowerCase();
    const filterLower = filterJurusan.toLowerCase();
    
    // Jika filter bukan 'semua' dan nama jurusan tidak mengandung kata filter, lewati
    if (filterJurusan !== 'semua' && jurusanGrup.indexOf(filterLower) === -1) {
      return;
    }

    adaData = true;
    const rowCount = group.rows.length;

    group.rows.forEach((row, idx) => {
      html += `<tr>`;
      
      // Hanya tampilkan No, Jurusan, Prodi di baris pertama grup (rowspan)
      if (idx === 0) {
        html += `<td rowspan="${rowCount}" class="group-cell align-middle">${no++}</td>`;
        html += `<td rowspan="${rowCount}" class="group-cell align-middle">${group.nama_jurusan || '-'}</td>`;
        html += `<td rowspan="${rowCount}" class="group-cell align-middle">${group.nama_program_studi || '-'}</td>`;
      }

      // Kolom lainnya (selalu tampil per baris)
      html += `<td>${row.peringkat || '-'}</td>`;
      html += `<td>${row.nama_lembaga || '-'}</td>`;
      html += `<td>${row.jenis_akreditasi || '-'}</td>`;
      html += `<td>${row.tanggal_mulai || '-'}</td>`;
      html += `<td>${row.tanggal_berakhir || '-'}</td>`;
      
      // Sertifikat
      if (row.sertifikat) {
        html += `<td><a href="${row.sertifikat}" target="_blank" class="btn btn-icon btn-info"><i class="fas fa-eye"></i> View</a></td>`;
      } else {
        html += `<td><span class="text-muted">No File</span></td>`;
      }

      // Aksi (Edit & Delete)
      html += `<td>
        <button class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit${row.id_akreditasi}">
          <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete${row.id_akreditasi}">
          <i class="fas fa-trash-alt"></i>
        </button>
      </td>`;
      
      html += `</tr>`;
    });
  });

  if (!adaData) {
    html = `<tr><td colspan="10" class="text-center py-3 text-muted">Tidak ada data Program Studi untuk jurusan ini.</td></tr>`;
  }

  tbody.innerHTML = html;
}

// 3. Fungsi yang dipanggil saat tombol filter diklik
function filterProdi(jurusan, btn) {
  // Reset tombol active
  document.querySelectorAll('.btn-jurusan').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  
  // Render ulang tabel
  renderTabelProdi(jurusan);
}

// 4. Render tabel pertama kali saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
  renderTabelProdi('semua');
});
</script>

<?php include 'footer.php'; ?>