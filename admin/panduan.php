<?php
include "../koneksi.php";
/** @var mysqli $koneksi */
include 'header.php';

$panduan=query("SELECT * FROM panduan");

// Pagination setup
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;
// Pencarian
$search = isset($_GET['versi']) ? trim($_GET['versi']) : '';
if ($search !== '') {
    $panduan = array_filter($panduan, function ($item) use ($search) {
        return stripos($item['versi'], $search) !== false;
    });
}
// Query untuk mendapatkan data
$totalData = count($panduan);
$totalPages = ceil($totalData / $limit);
$panduan = array_slice($panduan, $start, $limit);

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
                <h5 class="mb-4 text-primary">Kelola Panduan Program</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                        <!-- search bar -->
                        <div class="search-bar-wrapper">
                            <div style="display: flex; justify-content: flex-start; align-items: center;">
                            <a class="button btn-tambah" href="#" style="margin-right: 5px;" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Data</a>
                            <form action="panduan.php" method="get">
                            <input type="text" name="versi" class="form-control search-bar" placeholder="versi panduan" value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Versi Panduan</th>
                                <th>Tanggal</th>
                                <th>Buku</th>
                                <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($panduan)): ?>
                                <?php $id=1; ?>
                                <?php foreach ($panduan as $row): ?>
                                <tr>
                                    <td><?php echo $id; ?></td>
                                    <td><?php echo $row["versi"]; ?></td>
                                    <td><?php echo $row["tanggal"]; ?></td>
                                    <td>
                                        <?php if (!empty($row['buku'])): ?>
                                            <a href="<?php echo $row['buku']; ?>" target="_blank" class="btn btn-icon btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No File</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $row['id_panduan']; ?>"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?php echo $row['id_panduan']; ?>"><i class="fas fa-trash-alt"></i></button>                                    
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
                                    <a class="page-link arrow" href="?page=<?php echo $page - 1; ?>&versi=<?php echo $search; ?>" tabindex="-1">←</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&versi=<?php echo $search; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link arrow" href="?page=<?php echo $page + 1; ?>&versi=<?php echo $search; ?>">→</a>
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
            if (isset($_POST["submit"])) {
                $versi = $_POST["versi"];
                $tanggal = $_POST["tanggal"];
                $buku = '';

                // Upload Buku (satu file, format PDF)
                if (!empty($_FILES['buku']['name'])) {
                    $uploadDir = 'uploads/buku/';
                    $fileName = time() . '-' . basename($_FILES['buku']['name']);
                    $filePath = $uploadDir . $fileName;

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    if ($fileType == 'pdf') {
                        if (move_uploaded_file($_FILES['buku']['tmp_name'], $filePath)) {
                            $buku = $filePath;
                        } else {
                            echo "<script>alert('Gagal mengupload buku!');</script>";
                        }
                    } else {
                        echo "<script>alert('Hanya file dengan format pdf yang diperbolehkan untuk buku!');</script>";
                    }
                }

                // Simpan data ke database

                $sql = "INSERT INTO panduan (versi, tanggal, buku) 
                        VALUES ('$versi', '$tanggal', '$buku')";

                $result = mysqli_query($koneksi, $sql);
                if ($result) {
                    echo "<script>alert('Data telah ditambahkan');
                        window.location='panduan.php';</script>";
                } else {
                    echo "<script>alert('Data gagal ditambahkan!');
                    window.location='panduan.php';</script>";
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
                                    <label for="versi" class="form-label">Versi Panduan</label>
                                    <input type="text" class="form-control" name="versi" required>
                                </div>
                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" required>
                                </div>
                                <div class="mb-3">
                                    <label for="buku" class="form-label">Buku</label>
                                    <input type="file" class="form-control" id="buku" name="buku" accept=".pdf" required>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>            
            
            <?php foreach ($panduan as $row): ?>

            <!-- Modal Edit Data -->
            <?php
            if (isset($_POST["edit"])) {
                $id_panduan = $_POST["id_panduan"];

                // Ambil data sebelumnya
                $query_old = mysqli_query($koneksi, "SELECT * FROM panduan WHERE id_panduan='$id_panduan'");
                $rowedit = mysqli_fetch_assoc($query_old);

                $buku = $rowedit['buku'];

                // Buku (PDF)
                if (!empty($_FILES['ubah_buku']['name'])) {
                    $fileName = $_FILES['ubah_buku']['name'];
                    $fileTmp = $_FILES['ubah_buku']['tmp_name'];
                    $targetDir = "uploads/buku/";
                    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if ($fileType == 'pdf') {
                        $newName = time() . '-' . basename($fileName);
                        $targetPath = $targetDir . $newName;
                        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                        if (move_uploaded_file($fileTmp, $targetPath)) {
                            $buku = $targetPath;
                        } else {
                            echo "<script>alert('Gagal mengupload file buku!');</script>";
                        }
                    } else {
                        echo "<script>alert('Hanya file PDF yang diperbolehkan untuk buku!');</script>";
                    }
                }

                // Escape input untuk keamanan
                $versi = mysqli_real_escape_string($koneksi, $_POST['ubah_versi']);
                $tanggal = mysqli_real_escape_string($koneksi, $_POST['ubah_tanggal']);

                // Update query
                $update = mysqli_query($koneksi, "UPDATE panduan SET 
                    versi='$versi',
                    tanggal='$tanggal',
                    buku='$buku'
                    WHERE id_panduan='$id_panduan'");

                if ($update) {
                    echo "<script>alert('Data berhasil diedit'); window.location='panduan.php';</script>";
                } else {
                    echo "<script>alert('Gagal mengedit data'); window.location='panduan.php';</script>";
                }
            }
            ?>

            <div class="modal fade" id="modalEdit<?php echo $row['id_panduan']; ?>" tabindex="-1" aria-labelledby="modalEditLabel<?php echo $row['id_panduan']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalEditLabel<?php echo $row['id_panduan']; ?>">Edit Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <input type="hidden" name="id_panduan" value="<?php echo $row['id_panduan']; ?>">

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label for="ubah_versi" class="form-label">Versi Panduan</label>
                                    <input type="text" class="form-control" name="ubah_versi" value="<?php echo $row['versi']; ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="ubah_tanggal" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="ubah_tanggal" value="<?php echo $row['tanggal']; ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="ubah_buku" class="form-label">Buku (PDF)</label>
                                    <input type="file" class="form-control" name="ubah_buku" accept="application/pdf">
                                    <?php if (!empty($row['buku'])) { ?>
                                        <small class="text-muted">File saat ini: <a href="<?php echo $row['buku']; ?>" target="_blank">Lihat Buku</a></small>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Delete -->
            <?php
            if (isset($_POST["delete"])) {
                $id_panduan = mysqli_real_escape_string($koneksi, $_POST["id_panduan"]);
                $delete = mysqli_query($koneksi, "DELETE FROM panduan WHERE id_panduan='$id_panduan'");
            
                if ($delete) {
                    echo "<script>alert('Data Berhasil Dihapus'); window.location='panduan.php';</script>";
                } else {
                    echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='panduan.php';</script>";
                }
            }            
            ?>

            <div class="modal fade" id="modalDelete<?php echo $row['id_panduan']; ?>" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                            <input type="hidden" name="id_panduan" value="<?php echo $row['id_panduan']; ?>">
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data <b><?php echo $row['versi']; ?></b>?</p>
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