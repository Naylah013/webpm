<?php
include "../koneksi.php";
include 'header.php';

$hasil_kepuasan_jurusan=query("SELECT * FROM hasil_kepuasan_jurusan");

// Pagination setup
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;
// Pencarian
$search = isset($_GET['tahun']) ? trim($_GET['tahun']) : '';
if ($search !== '') {
    $hasil_kepuasan_jurusan = array_filter($hasil_kepuasan_jurusan, function ($item) use ($search) {
        return stripos($item['tahun'], $search) !== false;
    });
}
// Query untuk mendapatkan data
$totalData = count($hasil_kepuasan_jurusan);
$totalPages = ceil($totalData / $limit);
$hasil_kepuasan_jurusan = array_slice($hasil_kepuasan_jurusan, $start, $limit);

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
                <h5 class="mb-4 text-primary">Kelola Hasil Kepuasan Jurusan</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                        <!-- search bar -->
                        <div class="search-bar-wrapper">
                            <div style="display: flex; justify-content: flex-start; align-items: center;">
                            <a class="button btn-tambah" href="#" style="margin-right: 5px;" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Data</a>
                            <form action="hasil_kepuasan_jurusan.php" method="get">
                            <input type="text" name="tahun" class="form-control search-bar" placeholder="tahun" value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Tahun</th>
                                <th>Penjelasan</th>
                                <th>Gambar</th>
                                <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($hasil_kepuasan_jurusan)): ?>
                                <?php $id=1; ?>
                                <?php foreach ($hasil_kepuasan_jurusan as $row): ?>
                                <tr>
                                    <td><?php echo $id; ?></td>
                                    <td><?php echo $row["tahun"]; ?></td>
                                    <td><?php echo $row["text"]; ?></td>
                                    <td>
                                    <?php if (!empty($row['gambar'])): ?>
                                        <a href="<?php echo $row['gambar']; ?>" target="_blank" class="btn btn-icon btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No File</span>
                                    <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $row['id_hasil_jurusan']; ?>"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?php echo $row['id_hasil_jurusan']; ?>"><i class="fas fa-trash-alt"></i></button>                                    
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
                                    <a class="page-link arrow" href="?page=<?php echo $page - 1; ?>&tahun=<?php echo $search; ?>" tabindex="-1">←</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&tahun=<?php echo $search; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link arrow" href="?page=<?php echo $page + 1; ?>&tahun=<?php echo $search; ?>">→</a>
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
                $tahun = $_POST["tahun"];
                $text = $_POST["text"];
                $gambar = '';

                // Upload Gambar (satu file)
                if (!empty($_FILES['gambar']['name'])) {
                    $uploadDir = 'uploads/gambar/';
                    $fileName = time() . '-' . basename($_FILES['gambar']['name']);
                    $filePath = $uploadDir . $fileName;

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    if (in_array($fileType, ['jpeg', 'jpg', 'png'])) {
                        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $filePath)) {
                            $gambar = $filePath;
                        } else {
                            echo "<script>alert('Gagal mengupload gambar!');</script>";
                        }
                    } else {
                        echo "<script>alert('Hanya file dengan format jpeg, jpg, atau png yang diperbolehkan untuk gambar!');</script>";
                    }
                }

                // Simpan data ke database

                $sql = "INSERT INTO hasil_kepuasan_jurusan (tahun, text, gambar) 
                        VALUES ('$tahun', '$text', '$gambar')";

                $result = mysqli_query($koneksi, $sql);
                if ($result) {
                    echo "<script>alert('Data telah ditambahkan');
                        window.location='hasil_kepuasan_jurusan.php';</script>";
                } else {
                    echo "<script>alert('Data gagal ditambahkan!');
                    window.location='hasil_kepuasan_jurusan.php';</script>";
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
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <input type="text" class="form-control" name="tahun" required>
                                </div>
                                <div class="mb-3">
                                    <label for="text" class="form-label">Penjelasan</label>
                                    <input type="text" class="form-control" name="text" required>
                                </div>
                                <div class="mb-3">
                                    <label for="gambar" class="form-label">Gambar</label>
                                    <input type="file" class="form-control" id="gambar" name="gambar" accept=".jpg, .jpeg, .png" required>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>            
            
            <?php foreach ($hasil_kepuasan_jurusan as $row): ?>

            <!-- Modal Edit Data -->
            <?php
            if (isset($_POST["edit"])) {
                $id_hasil_jurusan = $_POST["id_hasil_jurusan"];

                // Ambil data sebelumnya
                $query_old = mysqli_query($koneksi, "SELECT * FROM hasil_kepuasan_jurusan WHERE id_hasil_jurusan='$id_hasil_jurusan'");
                $rowedit = mysqli_fetch_assoc($query_old);

                $gambar = $rowedit['gambar'];

                // Cek dan upload gambar utama
                if (!empty($_FILES['ubah_gambar']['name'])) {
                    $fileName = $_FILES['ubah_gambar']['name'];
                    $fileTmp = $_FILES['ubah_gambar']['tmp_name'];
                    $targetDir = "uploads/gambar/";
                    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (in_array($fileType, ['jpg', 'jpeg', 'png'])) {
                        $fileName = time() . '-' . basename($fileName);
                        $gambar = $targetDir . $fileName;
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0777, true);
                        }
                        if (!move_uploaded_file($fileTmp, $gambar)) {
                            echo "<script>alert('Gagal mengupload gambar utama!');</script>";
                        }
                    } else {
                        echo "<script>alert('Hanya file JPG, JPEG, atau PNG yang diperbolehkan untuk gambar utama!');</script>";
                    }
                }

                // Escape input untuk keamanan
                $tahun = mysqli_real_escape_string($koneksi, $_POST['ubah_tahun']);
                $text = mysqli_real_escape_string($koneksi, $_POST['ubah_text']);

                // Update query
                $update = mysqli_query($koneksi, "UPDATE hasil_kepuasan_jurusan SET 
                    tahun='$tahun',
                    text='$text',
                    gambar='$gambar'
                    WHERE id_hasil_jurusan='$id_hasil_jurusan'");

                if ($update) {
                    echo "<script>alert('Data berhasil diedit'); window.location='hasil_kepuasan_jurusan.php';</script>";
                } else {
                    echo "<script>alert('Gagal mengedit data'); window.location='hasil_kepuasan_jurusan.php';</script>";
                }
            }
            ?>

            <div class="modal fade" id="modalEdit<?php echo $row['id_hasil_jurusan']; ?>" tabindex="-1" aria-labelledby="modalEditLabel<?php echo $row['id_hasil_jurusan']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" enctype="multipart/form-data">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalEditLabel<?php echo $row['id_hasil_jurusan']; ?>">Edit Data</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <input type="hidden" name="id_hasil_jurusan" value="<?php echo $row['id_hasil_jurusan']; ?>">

                            <div class="modal-body">

                                <div class="mb-3">
                                    <label for="ubah_tahun" class="form-label">Tahun</label>
                                    <input type="text" class="form-control" name="ubah_tahun" value="<?php echo $row['tahun']; ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="ubah_text" class="form-label">Penjelasan</label>
                                    <input type="text" class="form-control" name="ubah_text" value="<?php echo $row['text']; ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="ubah_gambar" class="form-label">Gambar</label>
                                    <input type="file" class="form-control" id="ubah_gambar" name="ubah_gambar" accept="image/*">
                                    <small class="text-muted">File saat ini: <a href="<?php echo $rowedit['gambar']; ?>" target="_blank">Lihat Gambar</a></small>
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
                $id_hasil_jurusan = mysqli_real_escape_string($koneksi, $_POST["id_hasil_jurusan"]);
                $delete = mysqli_query($koneksi, "DELETE FROM hasil_kepuasan_jurusan WHERE id_hasil_jurusan='$id_hasil_jurusan'");
            
                if ($delete) {
                    echo "<script>alert('Data Berhasil Dihapus'); window.location='hasil_kepuasan_jurusan.php';</script>";
                } else {
                    echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='hasil_kepuasan_jurusan.php';</script>";
                }
            }            
            ?>

            <div class="modal fade" id="modalDelete<?php echo $row['id_hasil_jurusan']; ?>" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                            <input type="hidden" name="id_hasil_jurusan" value="<?php echo $row['id_hasil_jurusan']; ?>">
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data <b><?php echo $row['tahun']; ?></b>?</p>
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