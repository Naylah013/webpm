<?php
include '../koneksi.php';
include 'header.php';

$today = date('Y-m-d');
$months_later = date('Y-m-d', strtotime('+12 months'));

$notif_data = [];

// ===========================
// 1. Akreditasi Perguruan Tinggi (tanpa tampilkan ID atau nama)
// ===========================
$query_perguruan = mysqli_query($koneksi, "
    SELECT ap.*
    FROM akreditasi_perguruan ap
    INNER JOIN (
        SELECT id_perguruan, MAX(tanggal_berakhir) AS max_tanggal
        FROM akreditasi_perguruan
        GROUP BY id_perguruan
    ) latest
    ON ap.id_perguruan = latest.id_perguruan
    AND ap.tanggal_berakhir = latest.max_tanggal
");

while ($row = mysqli_fetch_assoc($query_perguruan)) {
    $tanggal_berakhir = $row['tanggal_berakhir'];

    if ($tanggal_berakhir <= $months_later && $tanggal_berakhir >= $today) {
        $notif_data[] = [
            'type' => 'perguruan',
            'info' => "Akreditasi perguruan tinggi akan berakhir pada <strong>{$tanggal_berakhir}</strong>."
        ];
    }
}
    
// ===========================
// 2. Akreditasi Unit & Program Studi
// ===========================
$query_prodi_unit = mysqli_query($koneksi, "
    SELECT a.*, u.nama_unit_kerja, p.nama_program_studi
    FROM akreditasi a
    LEFT JOIN unit u ON a.id_unit = u.id_unit
    LEFT JOIN program_studi p ON a.id_prodi = p.id_prodi
    INNER JOIN (
        SELECT 
            id_unit,
            id_prodi,
            MAX(tanggal_berakhir) AS max_tanggal
        FROM akreditasi
        GROUP BY id_unit, id_prodi
    ) latest
    ON a.id_unit <=> latest.id_unit
    AND a.id_prodi <=> latest.id_prodi
    AND a.tanggal_berakhir = latest.max_tanggal
");

while ($row = mysqli_fetch_assoc($query_prodi_unit)) {
    $tanggal_berakhir = $row['tanggal_berakhir'];

    if ($tanggal_berakhir <= $months_later && $tanggal_berakhir >= $today) {
        $nama = '';

        if (!empty($row['id_unit']) && empty($row['id_prodi'])) {
            $nama = $row['nama_unit_kerja'];
        } elseif (!empty($row['id_prodi'])) {
            $nama = $row['nama_program_studi'];
        }

        if ($nama !== '') {
            $notif_data[] = [
                'type' => 'prodi_unit',
                'info' => "Akreditasi untuk <strong>$nama</strong> akan berakhir pada <strong>{$tanggal_berakhir}</strong>."
            ];
        }
    }
}

// Tabel Kirim Notifikasi Kajur dan KPS 
$kirim=query("SELECT * FROM kirim");

// Pagination setup
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;
// Pencarian
$search = isset($_GET['nama']) ? trim($_GET['nama']) : '';
if ($search !== '') {
    $kirim = array_filter($kirim, function ($item) use ($search) {
        return stripos($item['nama'], $search) !== false;
    });
}
// Query untuk mendapatkan data
$totalData = count($kirim);
$totalPages = ceil($totalData / $limit);
$kirim = array_slice($kirim, $start, $limit);

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

    .icon-send {
    color: #0d6efd; /* biru bootstrap */
    }

    </style>

</head>

            <!-- isi -->
            <div class="container-fluid pt-4 px-4">
                <h5 class="mb-4 text-primary">Dashboard</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                            <!-- Notifikasi Masa Berlaku Akreditasi isi bagian sini-->
                            <?php if (!empty($notif_data)) : ?>
                            <div class="alert fade show p-4 rounded-4" role="alert" style="background-color: #0d83fd; color: white; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong>Perhatian!</strong> Berikut adalah daftar akreditasi yang akan berakhir dalam waktu kurang dari 1 tahun :
                                    </div>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($notif_data as $notif) : ?>
                                        <li><?= $notif['info']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                    </div>
                    </div>
                </div>
            </div>
            <!-- isi -->

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <h5 class="mb-4 text-primary">Kelola Notifikasi Kajur dan KPS</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                        <!-- search bar -->
                        <div class="search-bar-wrapper">
                            <div style="display: flex; justify-content: flex-start; align-items: center;">
                            <a class="button btn-tambah" href="#" style="margin-right: 5px;" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah Data</a>
                            <form action="dashboard.php" method="get">
                            <input type="text" name="nama" class="form-control search-bar" placeholder="nama" value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Email</th>
                                <th>Kirim Notifikasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($kirim)): ?>
                                <?php $id=1; ?>
                                <?php foreach ($kirim as $row): ?>
                                <tr>
                                    <td><?php echo $id; ?></td>
                                    <td><?php echo $row["nama"]; ?></td>
                                    <td><?php echo $row["jabatan"]; ?></td>
                                    <td><?php echo $row["email"]; ?></td>
                                    <td>
                                        <a href="kirim_email.php?id=<?php echo $row['id_kirim']; ?>" 
                                        class="btn btn-icon btn-light border btn-kirim" 
                                        title="Kirim Notifikasi">
                                            <i class="fas fa-paper-plane text-primary"></i>
                                        </a>
                                    </td>                                    
                                    <td>
                                        <?php if (isset($_GET['status']) && $_GET['id'] == $row['id_kirim']): ?>
                                            
                                            <?php if ($_GET['status'] == 'sukses'): ?>
                                                <span class="badge bg-success">Sudah Kirim</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Gagal</span>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <span class="badge bg-secondary">Belum</span>
                                        <?php endif; ?>
                                    </td>                                    
                                    <td>
                                        <button class="btn btn-icon btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $row['id_kirim']; ?>"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-icon btn-danger" data-bs-toggle="modal" data-bs-target="#modalDelete<?php echo $row['id_kirim']; ?>"><i class="fas fa-trash-alt"></i></button>                                    
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
                                    <a class="page-link arrow" href="?page=<?php echo $page - 1; ?>&nama=<?php echo $search; ?>" tabindex="-1">←</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&nama=<?php echo $search; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link arrow" href="?page=<?php echo $page + 1; ?>&nama=<?php echo $search; ?>">→</a>
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
                $nama=$_POST["nama"];
                $jabatan=$_POST["jabatan"];
                $email=$_POST["email"];
                
                $sql="INSERT INTO kirim (nama, jabatan, email) VALUES('$_POST[nama]', '$_POST[jabatan]', '$_POST[email]')";
                $result=mysqli_query($koneksi,$sql);
                if($result){
                echo "<script>alert('Data telah ditambahkan');
                    window.location='dashboard.php'</script>";
            }else{
                echo "<script>alert('Data gagal ditambahkan!');
                    window.location='dashboard.php'</script>";
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
                        <form class="" method="post">
                        <div class="modal-body">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama</label>
                                    <input type="text" class="form-control" name="nama" required>
                                </div>
                                <div class="mb-3">
                                    <label for="jabatan" class="form-label">Jabatan</label>
                                    <input type="text" class="form-control" name="jabatan" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="text" class="form-control" name="email" required>
                                </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php foreach ($kirim as $row): ?>

            <!-- Modal Edit Data -->
            <?php
                $edit = mysqli_query($koneksi,"SELECT * FROM kirim WHERE kirim.id_kirim=$row[id_kirim]");
                $rowedit = mysqli_fetch_array($edit);

                if(isset($_POST["edit"])){
                    $update = mysqli_query($koneksi,"UPDATE kirim SET 
                    nama='$_POST[ubah_nama]',
                    jabatan='$_POST[ubah_jabatan]',
                    email='$_POST[ubah_email]'
                    WHERE id_kirim='$_POST[id_kirim]'");
    
                    if($update){
                        echo "<script>alert('Data Berhasil Di Edit');
                            window.location='dashboard.php'</script>";
                    }else{
                        echo "<script>alert('Data Gagal Di Edit');
                            window.location='dashboard.php'</script>";
                    }
                }
            ?>

            <div class="modal fade" id="modalEdit<?php echo $rowedit["id_kirim"]; ?>" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditLabel">Edit Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="" method="post">
                        <input type="hidden" class="form-control" name="id_kirim" value="<?php echo $rowedit['id_kirim']; ?>">
                        <div class="modal-body">
                                <div class="mb-3">
                                    <label for="ubah_nama" class="form-label">Nama</label>
                                    <input type="text" class="form-control" name="ubah_nama" value="<?php echo $rowedit['nama']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="ubah_jabatan" class="form-label">Jabatan</label>
                                    <input type="text" class="form-control" name="ubah_jabatan" value="<?php echo $rowedit['jabatan']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="ubah_email" class="form-label">Email</label>
                                    <input type="text" class="form-control" name="ubah_email" value="<?php echo $rowedit['email']; ?>" required>
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
                $id_kirim = mysqli_real_escape_string($koneksi, $_POST["id_kirim"]);
                $delete = mysqli_query($koneksi, "DELETE FROM kirim WHERE id_kirim='$id_kirim'");
            
                if ($delete) {
                    echo "<script>alert('Data Berhasil Dihapus'); window.location='dashboard.php';</script>";
                } else {
                    echo "<script>alert('Data Gagal Dihapus: " . mysqli_error($koneksi) . "'); window.location='dashboard.php';</script>";
                }
            }            
            ?>

            <div class="modal fade" id="modalDelete<?php echo $row['id_kirim']; ?>" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus Data</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                            <input type="hidden" name="id_kirim" value="<?php echo $row['id_kirim']; ?>">
                            <div class="modal-body">
                                <p>Apakah Anda yakin ingin menghapus data <b><?php echo $row['nama']; ?></b>?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php endforeach; ?>

<script>
document.querySelectorAll('.btn-kirim').forEach(function(btn){
    btn.addEventListener('click', function(){
        alert('Mengirim email...');
    });
});
</script>

<?php include 'footer.php'; ?>