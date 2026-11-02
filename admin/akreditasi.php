<?php
include "../koneksi.php";
include 'header.php';

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
");

// Pagination setup
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;
// Pencarian
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $akreditasi = array_filter($akreditasi, function ($item) use ($search) {
        return stripos($item['nama_unit_kerja'], $search) !== false
            || stripos($item['nama_jurusan'], $search) !== false
            || stripos($item['nama_program_studi'], $search) !== false;
    });
}
// Query untuk mendapatkan data
$totalData = count($akreditasi);
$totalPages = ceil($totalData / $limit);
$akreditasi = array_slice($akreditasi, $start, $limit);

?>

<head>

    <style>
    .logo-image {
        height: 40px; 
        width: auto; 
    }

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

    .pagination-custom {
    display: flex;
    justify-content: center;
    align-items: center;
    list-style: none;
    padding: 0;
    margin: 15px 0; /* Margin dikurangi */
    gap: 5px; /* Mengurangi jarak antar tombol */
    }

    .pagination-custom .page-item {
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease-in-out;
    }

    .pagination-custom .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px; /* Padding lebih kecil */
        font-size: 12px; /* Font-size diperkecil */
        font-weight: 500;
        color: #0d83fd;
        background-color: white;
        border: 1.5px solid #0d83fd; /* Border lebih tipis */
        border-radius: 8px; /* Lebih kecil */
        box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.08);
        transition: all 0.2s ease-in-out;
    }

    .pagination-custom .page-item.active .page-link {
        background-color: #0d83fd;
        color: white;
        border-color: #0d83fd;
        box-shadow: 1px 1px 10px rgba(13, 131, 253, 0.2);
    }

    .pagination-custom .page-link:hover {
        background-color: rgba(13, 131, 253, 0.1);
        color: #0d83fd;
        transform: scale(1.05);
    }

    .pagination-custom .page-item.disabled .page-link {
        color: #aaa;
        border-color: #ddd;
        background-color: #f8f9fa;
        cursor: not-allowed;
    }

    .pagination-custom .arrow {
        font-size: 14px; /* Ukuran ikon lebih kecil */
        padding: 6px; /* Padding lebih kecil */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
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
      font-size: 18px;
    }

    .showing-info {
      font-size: 14px;
      color: #0d83fd;
      text-align: center;
      margin-bottom: 10px;
    }

    .btn-tambah {
    background-color: #0d83fd;
    color: white;
    border-radius: 20px;
    padding: 5px 15px; 
    border: none;
    }

    .hidden {
    display: none;
    }

    </style>

<script>

    function showQuestions(value) {
        // Sembunyikan questions
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

</script>

</head>

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <h5 class="mb-4 text-primary">Kelola Akreditasi Dan Sertifikasi</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                        <!-- search bar -->
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
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                            <thead>
                                <tr>
                                <th>No</th>
                                <th>Unit Kerja</th>
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
                            <tbody>
                                <?php if (!empty($akreditasi)): ?>
                                <?php $id=1; ?>
                                <?php foreach ($akreditasi as $row): ?>
                                <tr>
                                    <td><?php echo $id; ?></td>
                                    <td><?= !empty($row['nama_unit_kerja']) ? $row['nama_unit_kerja'] : "Tidak Tersedia" ?></td>
                                    <td><?= !empty($row['nama_jurusan']) ? $row['nama_jurusan'] : "Tidak Tersedia" ?></td>
                                    <td><?= !empty($row['nama_program_studi']) ? $row['nama_program_studi'] : "Tidak Tersedia" ?></td>
                                    <td><?php echo $row["peringkat"]; ?></td>
                                    <td><?php echo $row["nama_lembaga"]; ?></td>
                                    <td><?php echo $row["jenis_akreditasi"]; ?></td>
                                    <td><?php echo $row["tanggal_mulai"]; ?></td>
                                    <td><?php echo $row["tanggal_berakhir"]; ?></td>
                                    <td>
                                    <?php if (!empty($row['sertifikat'])): ?>
                                        <a href="<?php echo $row['sertifikat']; ?>" target="_blank" class="btn btn-icon btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No File</span>
                                    <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?php echo $row['id_akreditasi']; ?>"><i class="fas fa-trash-alt"></i></button>                                    
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
                        <!-- Pagination -->
                        <div class="showing-info text-center mb-3">
                            Menampilkan <?php echo $start + 1; ?> sampai <?php echo min($start + $limit, $totalData); ?> dari <?php echo $totalData; ?> data
                        </div>

                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-custom">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link arrow" href="?page=<?php echo $page - 1; ?>&judul=<?php echo $search; ?>" tabindex="-1">←</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&judul=<?php echo $search; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link arrow" href="?page=<?php echo $page + 1; ?>&judul=<?php echo $search; ?>">→</a>
                                </li>
                            </ul>
                        </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Main Content -->

            <!-- Modal Tambah Data -->
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

                // Upload Sertifikat (satu file)
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
                echo "<script>alert('Data telah ditambahkan');
                    window.location='akreditasi.php'</script>";
                }else{
                echo "<script>alert('Data gagal ditambahkan!');
                    window.location='akreditasi.php'</script>";
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
                                    <select class="form-select" name="nama_jurusan">
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
                                    <select class="form-select" name="id_prodi">
                                        <option value="0" selected disabled>-- Pilih Program Studi --</option>
                                        <?php
                                            $ambil = mysqli_query($koneksi,"SELECT * FROM program_studi");
                                            while ($data = mysqli_fetch_assoc($ambil)){
                                                echo '<option value="'.$data['id_prodi'].'">'.$data['nama_program_studi'].'</option>';
                                            }
                                        ?>
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

            <?php foreach ($akreditasi as $row): ?>

            <!-- Modal Delete -->
            <?php
            if (isset($_POST["delete"])) {
                $id_akreditasi = mysqli_real_escape_string($koneksi, $_POST["id_akreditasi"]);
                // Query delete
                $delete = mysqli_query($koneksi, "DELETE FROM akreditasi WHERE id_akreditasi='$id_akreditasi'");
                // Cek apakah berhasil
                if ($delete) {
                    echo "<script>alert('Data Berhasil Dihapus'); window.location='akreditasi.php';</script>";
                } else {
                    echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='akreditasi.php';</script>";
                }
            }
            ?>

            <div class="modal fade" id="modalDelete<?php echo $row['id_akreditasi']; ?>" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" action="akreditasi.php">
                            <input type="hidden" name="id_akreditasi" value="<?php echo $row['id_akreditasi']; ?>">
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data <b><?php echo $row['id_akreditasi']; ?></b>?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>

<?php include 'footer.php'; ?>