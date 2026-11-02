<?php
include "../koneksi.php";
$berita=query("SELECT * FROM berita");

// Pagination setup
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;
// Pencarian
$search = isset($_GET['judul']) ? trim($_GET['judul']) : '';
if ($search !== '') {
    $berita = array_filter($berita, function ($item) use ($search) {
        return stripos($item['judul'], $search) !== false;
    });
}
// Query untuk mendapatkan data
$totalData = count($berita);
$totalPages = ceil($totalData / $limit);
$berita = array_slice($berita, $start, $limit);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>P4M Polibatam</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">

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

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">

        <!-- Sidebar -->
        <div class="sidebar pe-4 pb-3 bg-light">
            <nav class="navbar bg-light navbar-light">
            <a href="#" class="navbar-brand mx-5 mb-3">
                <span style="font-size: 24px; font-weight: bold; color: #4AC4F3;">
                    <img src="../assets/img/logo/polibatam.svg" alt="" class="logo-image" /> Polibatam
                </span>
            </a>
                <div class="navbar-nav w-100">
                    <a href="dashboard.php" class="nav-item nav-link"><i class="fa fa-home me-2"></i>Dashboard</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-cogs me-2"></i>Kelola Layanan</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="hasil_kepuasan_jurusan.php" class="dropdown-item">Hasil Kepuasan Jurusan</a>
                            <a href="hasil_kepuasan_unit.php" class="dropdown-item">Hasil Kepuasan Unit Kerja</a>
                            <a href="layanan.php" class="dropdown-item">Menu Layanan</a>
                            <a href="isi_layanan.php" class="dropdown-item">Isi Layanan</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-user me-2"></i>Kelola Data</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="unit.php" class="dropdown-item">Unit Kerja</a>
                            <a href="prodi.php" class="dropdown-item">Program Studi</a>
                            <a href="lembaga.php" class="dropdown-item">Lembaga</a>
                            <a href="jenis_akre.php" class="dropdown-item">Jenis Akreditasi</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-graduation-cap me-2"></i>Kelola Akreditasi</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="akreditasi.php" class="dropdown-item">Akreditasi</a>
                            <a href="akre_perguruan.php" class="dropdown-item">Perguruan Tinggi</a>
                            <a href="akre_unit.php" class="dropdown-item">Unit Kerja</a>
                            <a href="akre_prodi.php" class="dropdown-item">Program Studi</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-concierge-bell me-2"></i>Kelola Program</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="panduan.php" class="dropdown-item">Panduan Program</a>
                            <a href="buku_isbn.php" class="dropdown-item">Buku ISBN</a>
                            <a href="video_digital.php" class="dropdown-item">Video Digital</a>
                        </div>
                    </div>
                    <a href="berita.php" class="nav-item nav-link active">
                        <i class="fa fa-clipboard-list me-2"></i>Kelola Berita
                    </a>
                </div>
            </nav>
        </div>
        <!-- Sidebar -->

        <!-- Content -->
        <div class="content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-2 shadow-sm">
                <a href="#" class="sidebar-toggler flex-shrink-0"><i class="fa fa-bars"></i></a>
                <div class="navbar-nav align-items-center ms-auto">
                <a href="../index.php" class="btn btn-link text-dark" style="position: absolute; right: 20px;">
                    <i class="fa fa-sign-out-alt"></i>
                </a>
                </div>
            </nav>
            <!-- Navbar -->

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <h5 class="mb-4 text-primary">Kelola Berita</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                        <!-- search bar -->
                        <div class="search-bar-wrapper">
                            <div style="display: flex; justify-content: flex-start; align-items: center;">
                            <a class="button btn-tambah" href="#" style="margin-right: 5px;" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Data</a>
                            <form action="berita.php" method="get">
                            <input type="text" name="judul" class="form-control search-bar" placeholder="judul berita" value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Tanggal</th>
                                <th>Judul</th>
                                <th>Penjelasan</th>
                                <th>Gambar</th>
                                <th>Buku</th>
                                <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($berita)): ?>
                                <?php $id=1; ?>
                                <?php foreach ($berita as $row): ?>
                                <tr>
                                    <td><?php echo $id; ?></td>
                                    <td><?php echo $row["tanggal"]; ?></td>
                                    <td><?php echo $row["judul"]; ?></td>
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
                                        <?php if (!empty($row['buku'])): ?>
                                            <a href="<?php echo $row['buku']; ?>" target="_blank" class="btn btn-icon btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No File</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $row['id_berita']; ?>"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?php echo $row['id_berita']; ?>"><i class="fas fa-trash-alt"></i></button>                                    
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
            if (isset($_POST["submit"])) {
                $tanggal = $_POST["tanggal"];
                $judul = $_POST["judul"];
                $text = $_POST["text"];                
                $gambar = '';
                $buku = '';

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

                $sql = "INSERT INTO berita (tanggal, judul, text, gambar, buku) 
                        VALUES ('$tanggal', '$judul', '$text', '$gambar', '$buku')";

                $result = mysqli_query($koneksi, $sql);
                if ($result) {
                    echo "<script>alert('Data telah ditambahkan');
                        window.location='berita.php';</script>";
                } else {
                    echo "<script>alert('Data gagal ditambahkan!');
                    window.location='berita.php';</script>";
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
                                    <label for="tanggal" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" required>
                                </div>
                                <div class="mb-3">
                                    <label for="judul" class="form-label">Judul</label>
                                    <input type="text" class="form-control" name="judul" required>
                                </div>
                                <div class="mb-3">
                                    <label for="text" class="form-label">Pejelasan</label>
                                    <textarea type="text" class="form-control" name="text" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="gambar" class="form-label">Gambar</label>
                                    <input type="file" class="form-control" id="gambar" name="gambar" accept=".jpg, .jpeg, .png">
                                </div>
                                <div class="mb-3">
                                    <label for="buku" class="form-label">Buku</label>
                                    <input type="file" class="form-control" id="buku" name="buku" accept=".pdf">
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>            
            
            <?php foreach ($berita as $row): ?>

            <!-- Modal Edit Data -->
            <?php
            $edit = mysqli_query($koneksi, "SELECT * FROM berita WHERE id_berita=$row[id_berita]");
            $rowedit = mysqli_fetch_array($edit);

            if (isset($_POST["edit"])) {
                $gambar = $rowedit['gambar'];
                $buku = $rowedit['buku'];

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

                // Cek dan upload buku (PDF)
                if (!empty($_FILES['ubah_buku']['name'])) {
                    $fileName = $_FILES['ubah_buku']['name'];
                    $fileTmp = $_FILES['ubah_buku']['tmp_name'];
                    $targetDir = "uploads/buku/";
                    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if ($fileType == 'pdf') {
                        $fileName = time() . '-' . basename($fileName);
                        $buku = $targetDir . $fileName;
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0777, true);
                        }
                        if (!move_uploaded_file($fileTmp, $buku)) {
                            echo "<script>alert('Gagal mengupload buku!');</script>";
                        }
                    } else {
                        echo "<script>alert('Hanya file PDF yang diperbolehkan untuk buku!');</script>";
                    }
                }

                // Update data di database
                $update = mysqli_query($koneksi, "UPDATE berita SET 
                    tanggal='$_POST[ubah_tanggal]',
                    judul='$_POST[ubah_judul]',
                    text='$_POST[ubah_text]',
                    gambar='$gambar',
                    buku='$buku'
                    WHERE id_berita='$_POST[id_berita]'");

                if ($update) {
                    echo "<script>alert('Data Berhasil Di Edit');
                        window.location='berita.php'</script>";
                } else {
                    echo "<script>alert('Data Gagal Di Edit');
                        window.location='berita.php'</script>";
                }
            }
            ?>

            <div class="modal fade" id="modalEdit<?php echo $rowedit["id_berita"]; ?>" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditLabel">Edit Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post" enctype="multipart/form-data">
                        <input type="hidden" class="form-control" name="id_berita" value="<?php echo $rowedit['id_berita']; ?>">
                        <div class="modal-body">
                                <div class="mb-3">
                                    <label for="ubah_tanggal" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" name="ubah_tanggal" value="<?php echo $rowedit['tanggal']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="ubah_judul" class="form-label">Judul</label>
                                    <input type="text" class="form-control" name="ubah_judul" value="<?php echo $rowedit['judul']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="ubah_text" class="form-label">Penjelasan</label>
                                    <textarea class="form-control" name="ubah_text"><?php echo $rowedit['text']; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="ubah_gambar" class="form-label">Gambar</label>
                                    <input type="file" class="form-control" id="ubah_gambar" name="ubah_gambar" accept="image/*">
                                    <small class="text-muted">File saat ini: <a href="<?php echo $rowedit['gambar']; ?>" target="_blank">Lihat Gambar</a></small>
                                </div>                        
                                <div class="mb-3">
                                    <label for="ubah_buku" class="form-label">Buku</label>
                                    <input type="file" class="form-control" id="ubah_buku" name="ubah_buku" accept="pdf/*">
                                    <small class="text-muted">File saat ini: <a href="<?php echo $rowedit['buku']; ?>" target="_blank">Lihat Buku</a></small>
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
                $id_berita = mysqli_real_escape_string($koneksi, $_POST["id_berita"]);
                $delete = mysqli_query($koneksi, "DELETE FROM berita WHERE id_berita='$id_berita'");
            
                if ($delete) {
                    echo "<script>alert('Data Berhasil Dihapus'); window.location='berita.php';</script>";
                } else {
                    echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='berita.php';</script>";
                }
            }            
            ?>

            <div class="modal fade" id="modalDelete<?php echo $row['id_berita']; ?>" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                            <input type="hidden" name="id_berita" value="<?php echo $row['id_berita']; ?>">
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data <b><?php echo $row['judul']; ?></b>?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>

            <!-- Footer -->
            <div class="container-fluid pt-4 px-4">
                <div class="bg-light rounded-top p-4">
                    <div class="row">
                        <div class="col-12 text-center">
                         Made By Pusat Pengembangan dan Penjaminan Mutu (P4M).
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->

        </div>
        <!-- Content -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>

</html>