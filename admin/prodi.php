<?php
include "../koneksi.php";
/** @var mysqli $koneksi */
include 'header.php';

// ==========================================
// 1. PROSES TAMBAH DATA
// ==========================================
if (isset($_POST["submit"])) {
    $nama_jurusan = mysqli_real_escape_string($koneksi, $_POST["nama_jurusan"]);
    $nama_program_studi = mysqli_real_escape_string($koneksi, $_POST["nama_program_studi"]);
    $email_kps = mysqli_real_escape_string($koneksi, $_POST["email_kps"]);
    
    $sql = "INSERT INTO program_studi (nama_jurusan, nama_program_studi, email_kps) 
            VALUES ('$nama_jurusan', '$nama_program_studi', '$email_kps')";
    $result = mysqli_query($koneksi, $sql);
    
    if ($result) {
        echo "<script>alert('Data telah ditambahkan'); window.location='prodi.php';</script>";
    } else {
        echo "<script>alert('Data gagal ditambahkan: " . mysqli_error($koneksi) . "'); window.location='prodi.php';</script>";
    }
}

// ==========================================
// 2. PROSES EDIT DATA
// ==========================================
if (isset($_POST["edit"])) {
    $id_prodi = mysqli_real_escape_string($koneksi, $_POST["id_prodi"]);
    $nama_jurusan = mysqli_real_escape_string($koneksi, $_POST["ubah_nama_jurusan"]);
    $nama_program_studi = mysqli_real_escape_string($koneksi, $_POST["ubah_nama_program_studi"]);
    $email_kps = mysqli_real_escape_string($koneksi, $_POST["ubah_email_kps"]);

    $update = mysqli_query($koneksi, "UPDATE program_studi SET 
        nama_jurusan = '$nama_jurusan',
        nama_program_studi = '$nama_program_studi',
        email_kps = '$email_kps'
        WHERE id_prodi = '$id_prodi'");

    if ($update) {
        echo "<script>alert('Data Berhasil Di Edit'); window.location='prodi.php';</script>";
    } else {
        echo "<script>alert('Data Gagal Di Edit: " . mysqli_error($koneksi) . "'); window.location='prodi.php';</script>";
    }
}

// ==========================================
// 3. PROSES HAPUS DATA
// ==========================================
if (isset($_POST["delete"])) {
    $id_prodi = mysqli_real_escape_string($koneksi, $_POST["id_prodi"]);
    $delete = mysqli_query($koneksi, "DELETE FROM program_studi WHERE id_prodi='$id_prodi'");

    if ($delete) {
        echo "<script>alert('Data Berhasil Dihapus'); window.location='prodi.php';</script>";
    } else {
        echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='prodi.php';</script>";
    }
}

// ==========================================
// 4. AMBIL DATA & PAGINATION
// ==========================================
$program_studi = query("SELECT * FROM program_studi");

// Pagination setup
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Pencarian
$search = isset($_GET['nama_program_studi']) ? trim($_GET['nama_program_studi']) : '';
if ($search !== '') {
    $program_studi = array_filter($program_studi, function ($item) use ($search) {
        return stripos($item['nama_program_studi'], $search) !== false;
    });
}

$totalData = count($program_studi);
$totalPages = ceil($totalData / $limit);
$program_studi = array_slice($program_studi, $start, $limit);
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

    .pagination-custom { display: flex; justify-content: center; align-items: center; list-style: none; padding: 0; margin: 15px 0; gap: 5px; }
    .pagination-custom .page-item { display: flex; align-items: center; justify-content: center; transition: all 0.2s ease-in-out; }
    .pagination-custom .page-link { display: flex; align-items: center; justify-content: center; padding: 6px 10px; font-size: 12px; font-weight: 500; color: #0d83fd; background-color: white; border: 1.5px solid #0d83fd; border-radius: 8px; box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.08); transition: all 0.2s ease-in-out; }
    .pagination-custom .page-item.active .page-link { background-color: #0d83fd; color: white; border-color: #0d83fd; box-shadow: 1px 1px 10px rgba(13, 131, 253, 0.2); }
    .pagination-custom .page-link:hover { background-color: rgba(13, 131, 253, 0.1); color: #0d83fd; transform: scale(1.05); }
    .pagination-custom .page-item.disabled .page-link { color: #aaa; border-color: #ddd; background-color: #f8f9fa; cursor: not-allowed; }
    .pagination-custom .arrow { font-size: 14px; padding: 6px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

    .search-bar-wrapper { display: flex; justify-content: flex-start; margin-bottom: 20px; }
    .search-bar-wrapper form { display: flex; align-items: center; }
    .search-bar { border: 1px solid #0d83fd; border-radius: 20px; padding: 5px 15px; width: 250px; margin-right: -40px; }
    .btn-cari { background-color: #0d83fd; color: white; border-radius: 20px; padding: 5px 15px; border: none; margin-left: -8px; }
    .btn-cari i { font-size: 18px; }
    .showing-info { font-size: 14px; color: #0d83fd; text-align: center; margin-bottom: 10px; }
    .btn-tambah { background-color: #0d83fd; color: white; border-radius: 20px; padding: 5px 15px; border: none; }
    </style>
</head>

<!-- Main Content -->
<div class="container-fluid pt-4 px-4">
    <h5 class="mb-4 text-primary">Kelola Data Program Studi</h5>
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">
            <!-- search bar -->
            <div class="search-bar-wrapper">
                <div style="display: flex; justify-content: flex-start; align-items: center;">
                <a class="button btn-tambah" href="#" style="margin-right: 5px;" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Data</a>
                <form action="prodi.php" method="get">
                <input type="text" name="nama_program_studi" class="form-control search-bar" placeholder="nama program studi" value="<?php echo htmlspecialchars($search); ?>">
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
                    <th>Nama Jurusan</th>
                    <th>Nama Program Studi</th>
                    <th>Email KPS</th>
                    <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($program_studi)): ?>
                    <?php $id = $start + 1; // Perbaikan nomor urut saat pindah halaman ?>
                    <?php foreach ($program_studi as $row): ?>
                    <tr>
                        <td><?php echo $id; ?></td>
                        <td><?php echo htmlspecialchars($row["nama_jurusan"]); ?></td>
                        <td><?php echo htmlspecialchars($row["nama_program_studi"]); ?></td>
                        <td>
                            <?php echo !empty($row["email_kps"]) ? htmlspecialchars($row["email_kps"]) : '<span class="text-muted fst-italic">Belum diatur</span>'; ?>
                        </td>
                        <td>
                            <button class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $row['id_prodi']; ?>"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?php echo $row['id_prodi']; ?>"><i class="fas fa-trash-alt"></i></button>                                    
                        </td>
                    </tr>
                    <?php $id++; ?>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5">Tidak ada data yang tersedia.</td>
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
                        <a class="page-link arrow" href="?page=<?php echo $page - 1; ?>&nama_program_studi=<?php echo $search; ?>" tabindex="-1">←</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&nama_program_studi=<?php echo $search; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link arrow" href="?page=<?php echo $page + 1; ?>&nama_program_studi=<?php echo $search; ?>">→</a>
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
            <form class="" method="post">
            <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_jurusan" class="form-label">Nama Jurusan</label>
                        <input type="text" class="form-control" name="nama_jurusan" required>
                    </div>
                    <div class="mb-3">
                        <label for="nama_program_studi" class="form-label">Nama Program Studi</label>
                        <input type="text" class="form-control" name="nama_program_studi" required>
                    </div>
                    <div class="mb-3">
                        <label for="email_kps" class="form-label">Email KPS / Kaprodi</label>
                        <input type="email" class="form-control" name="email_kps" placeholder="xxxx@polibatam.ac.id">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
            </div>
            </form>
        </div>
    </div>
</div>

<?php foreach ($program_studi as $row): ?>
<!-- Modal Edit Data -->
<div class="modal fade" id="modalEdit<?php echo $row['id_prodi']; ?>" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">Edit Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="" method="post">
            <input type="hidden" class="form-control" name="id_prodi" value="<?php echo $row['id_prodi']; ?>">
            <div class="modal-body">
                    <div class="mb-3">
                        <label for="ubah_nama_jurusan" class="form-label">Nama Jurusan</label>
                        <input type="text" class="form-control" name="ubah_nama_jurusan" value="<?php echo htmlspecialchars($row['nama_jurusan']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ubah_nama_program_studi" class="form-label">Nama Program Studi</label>
                        <input type="text" class="form-control" name="ubah_nama_program_studi" value="<?php echo htmlspecialchars($row['nama_program_studi']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ubah_email_kps" class="form-label">Email KPS / Kaprodi</label>
                        <input type="email" class="form-control" name="ubah_email_kps" value="<?php echo isset($row['email_kps']) ? htmlspecialchars($row['email_kps']) : ''; ?>" placeholder="xxxx@polibatam.ac.id">
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
<div class="modal fade" id="modalDelete<?php echo $row['id_prodi']; ?>" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post">
                <input type="hidden" name="id_prodi" value="<?php echo $row['id_prodi']; ?>">
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data <b><?php echo htmlspecialchars($row['nama_program_studi']); ?></b>?</p>
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