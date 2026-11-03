<?php
include "../koneksi.php";
/** @var mysqli $koneksi */
include 'header.php';

$akreditasi_perguruan = query("
    SELECT 
        ap.id_perguruan,
        ap.peringkat,
        ap.jenis,
        ap.id_lembaga,
        l.nama_lembaga,
        ap.tanggal_mulai,
        ap.tanggal_berakhir,
        ap.sertifikat
    FROM akreditasi_perguruan AS ap
    LEFT JOIN lembaga AS l 
        ON l.id_lembaga = ap.id_lembaga
");

// Pagination setup
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;
// Pencarian
$search = isset($_GET['nama_lembaga']) ? trim($_GET['nama_lembaga']) : '';
if ($search !== '') {
    $akreditasi_perguruan = array_filter($akreditasi_perguruan, function ($item) use ($search) {
        return stripos($item['nama_lembaga'], $search) !== false;
    });
}
// Query untuk mendapatkan data
$totalData = count($akreditasi_perguruan);
$totalPages = ceil($totalData / $limit);
$akreditasi_perguruan = array_slice($akreditasi_perguruan, $start, $limit);

?>

<?php
if (isset($_POST["submit"])) {
    $peringkat = $_POST["peringkat"];
    $jenis = $_POST["jenis"];
    $id_lembaga = $_POST["id_lembaga"];
    $tanggal_mulai = $_POST["tanggal_mulai"];
    $tanggal_berakhir = $_POST["tanggal_berakhir"];
    $sertifikat = '';

    try {
        if (!isset($_FILES['sertifikat']) || $_FILES['sertifikat']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload file gagal: " . file_upload_error_message($_FILES['sertifikat']['error']));
        }

        $uploadDir = 'uploads/sertifikat/';
        $fileName = time() . '-' . basename($_FILES['sertifikat']['name']);
        $filePath = $uploadDir . $fileName;

        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Gagal membuat folder upload: $uploadDir");
            }
        }

        if (!is_writable($uploadDir)) {
            throw new Exception("Folder tidak bisa ditulis: $uploadDir");
        }

        $tmpPath = $_FILES['sertifikat']['tmp_name'];
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($fileType !== 'pdf') {
            throw new Exception("Format file harus PDF, yang diberikan: $fileType");
        }

        if (!file_exists($tmpPath)) {
            throw new Exception("File sementara tidak ditemukan.");
        }

        if (!is_uploaded_file($tmpPath)) {
            throw new Exception("File bukan hasil upload HTTP yang sah.");
        }

        if (!move_uploaded_file($tmpPath, $filePath)) {
            throw new Exception("Gagal memindahkan file ke folder tujuan.");
        }

        $sertifikat = $filePath;

        $sql = "INSERT INTO akreditasi_perguruan (peringkat, jenis, id_lembaga, tanggal_mulai, tanggal_berakhir, sertifikat) 
                VALUES ('$peringkat', '$jenis', '$id_lembaga', '$tanggal_mulai', '$tanggal_berakhir', '$sertifikat')";
        $result = mysqli_query($koneksi, $sql);

        if (!$result) {
            throw new Exception("Query MySQL gagal: " . mysqli_error($koneksi));
        }

        // Redirect jika berhasil
        header("Location: /admin/akre_perguruan.php");
        exit;

    } catch (Exception $e) {
        // Redirect kembali ke halaman sebelumnya jika gagal
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header("Location: $referer?error=" . urlencode($e->getMessage()));
        exit;
    }
}

?>

<?php
function file_upload_error_message($error_code) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => 'File melebihi batas maksimum upload_max_filesize di php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'File melebihi batas maksimum MAX_FILE_SIZE di form HTML.',
        UPLOAD_ERR_PARTIAL    => 'File hanya ter-upload sebagian.',
        UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang di-upload.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak tersedia.',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
        UPLOAD_ERR_EXTENSION  => 'Upload dihentikan oleh ekstensi PHP.',
    ];
    return $errors[$error_code] ?? 'Terjadi error upload yang tidak diketahui.';
}
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

    </style>

</head>

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <h5 class="mb-4 text-primary">Kelola Akreditasi Perguruan Tinggi</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                        <!-- search bar -->
                        <div class="search-bar-wrapper">
                            <div style="display: flex; justify-content: flex-start; align-items: center;">
                            <a class="button btn-tambah" href="#" style="margin-right: 5px;" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Data</a>
                            <form action="akre_perguruan.php" method="get">
                            <input type="text" name="nama_lembaga" class="form-control search-bar" placeholder="nama lembaga" value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Peringkat</th>
                                <th>Jenis</th>
                                <th>Lembaga</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Berakhir</th>
                                <th>Sertifikat</th>
                                <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($akreditasi_perguruan)): ?>
                                <?php $id=1; ?>
                                <?php foreach ($akreditasi_perguruan as $row): ?>
                                <tr>
                                    <td><?php echo $id; ?></td>
                                    <td><?php echo $row["peringkat"]; ?></td>
                                    <td><?php echo $row["jenis"]; ?></td>
                                    <td><?php echo $row["nama_lembaga"]; ?></td>
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
                                        <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?php echo $row['id_perguruan']; ?>"><i class="fas fa-trash-alt"></i></button>                                    
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
                                    <label for="peringkat" class="form-label">Peringkat</label>
                                    <input type="text" class="form-control" name="peringkat" required>
                                </div>
                                <div class="mb-3">
                                    <label for="jenis" class="form-label">Jenis</label>
                                    <input type="text" class="form-control" name="jenis" required>
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
                                <div class="mb-3">
                                    <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="tanggal_mulai" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal Berakhir</label>
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

            <?php foreach ($akreditasi_perguruan as $row): ?>

            <!-- Modal Delete -->
            <?php
            if (isset($_POST["delete"])) {
                $id_perguruan = mysqli_real_escape_string($koneksi, $_POST["id_perguruan"]);
                $delete = mysqli_query($koneksi, "DELETE FROM akreditasi_perguruan WHERE id_perguruan='$id_perguruan'");
            
                if ($delete) {
                    echo "<script>alert('Data Berhasil Dihapus'); window.location='akre_perguruan.php';</script>";
                } else {
                    echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='akre_perguruan.php';</script>";
                }
            }            
            ?>

            <div class="modal fade" id="modalDelete<?php echo $row['id_perguruan']; ?>" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                            <input type="hidden" name="id_perguruan" value="<?php echo $row['id_perguruan']; ?>">
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data <b><?php echo $row['peringkat']; ?></b>?</p>
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