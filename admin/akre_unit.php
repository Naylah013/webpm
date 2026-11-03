<?php
include "../koneksi.php";
/** @var mysqli $koneksi */ 
include 'header.php';

// Ambil input pencarian
$search = isset($_GET['nama_unit_kerja']) ? trim($_GET['nama_unit_kerja']) : '';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Kondisi pencarian (jika ada)
$searchCondition = "";
if (!empty($search)) {
    $searchCondition = "WHERE u.nama_unit_kerja LIKE '%$search%'";
}

// Query total data (untuk pagination)
$countQuery = "
    SELECT COUNT(*) as total 
    FROM akreditasi a
    JOIN unit u ON a.id_unit = u.id_unit
    $searchCondition
";
$totalResult = query($countQuery);
$totalData = $totalResult[0]['total'];
$totalPages = ceil($totalData / $limit);

// Query data untuk ditampilkan (dengan LIMIT dan kondisi pencarian)
$query = "
    SELECT a.*, u.nama_unit_kerja 
    FROM akreditasi a
    JOIN unit u ON a.id_unit = u.id_unit
    $searchCondition
    LIMIT $start, $limit
";
$akreditasi = query($query);
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

    <script>

    function viewSertifikat(selectId) {
    const select = document.getElementById(selectId);
    const selectedUrl = select.value;
    if (selectedUrl) {
        window.open(selectedUrl, '_blank');
    }
    }
    </script>

</head>

            <!-- Main Content -->
            <div class="container-fluid pt-4 px-4">
                <h5 class="mb-4 text-primary">Akreditasi Unit Kerja</h5>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-light rounded h-100 p-4">
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                            <thead>
                                <tr>
                                <th>No</th>
                                <th>Unit Kerja</th>
                                <th>Peringkat</th>
                                <th>Lembaga</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Berakhir</th>
                                <th>Sertifikat</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $query = "
                                SELECT a1.*, u.nama_unit_kerja, l.nama_lembaga
                                FROM akreditasi a1
                                INNER JOIN (
                                    SELECT id_unit, MAX(tanggal_berakhir) AS max_tanggal
                                    FROM akreditasi
                                    WHERE akreditasi = 'unit kerja'
                                    GROUP BY id_unit
                                ) a2 ON a1.id_unit = a2.id_unit AND a1.tanggal_berakhir = a2.max_tanggal
                                INNER JOIN unit u ON a1.id_unit = u.id_unit
                                LEFT JOIN lembaga l ON a1.id_lembaga = l.id_lembaga
                                WHERE a1.akreditasi = 'unit kerja'
                                ORDER BY a1.tanggal_berakhir DESC
                                ";

                                $result = mysqli_query($koneksi, $query);
                                $id = 1;

                                while ($row = mysqli_fetch_assoc($result)) {
                                    $id_unit = $row['id_unit'];
                                    $nama_unit_kerja = $row['nama_unit_kerja'];
                                    $peringkat = $row['peringkat'];
                                    $nama_lembaga = $row['nama_lembaga'];
                                    $tanggal_mulai = $row['tanggal_mulai'];
                                    $tanggal_berakhir = $row['tanggal_berakhir'];

                                    // Ambil semua sertifikat milik unit kerja ini (bisa disortir pakai tanggal_mulai DESC)
                                    $sertifikatQuery = "
                                        SELECT sertifikat, tanggal_mulai, tanggal_berakhir 
                                        FROM akreditasi 
                                        WHERE id_unit = '$id_unit' AND akreditasi = 'unit kerja'
                                        ORDER BY tanggal_berakhir DESC
                                    ";
                                    $sertifikatResult = mysqli_query($koneksi, $sertifikatQuery);

                                    $sertifikatList = [];
                                    while ($s = mysqli_fetch_assoc($sertifikatResult)) {
                                        $sertifikatList[] = $s;
                                    }

                                    echo "<tr>";
                                    echo "<td>$id</td>";
                                    echo "<td>$nama_unit_kerja</td>";
                                    echo "<td>$peringkat</td>";
                                    echo "<td>$nama_lembaga</td>";
                                    echo "<td>$tanggal_mulai</td>";
                                    echo "<td>$tanggal_berakhir</td>";
                                    echo "<td>";
                                    if (!empty($sertifikatList)) {
                                        echo "<div class='list-group'>";
                                        foreach ($sertifikatList as $s) {
                                            $link = $s['sertifikat'];
                                            $label = preg_replace('/^\d+-/', '', basename($s['sertifikat']));
                                            $tanggal = $s['tanggal_berakhir'];

                                            echo "<a href='$link' target='_blank' class='list-group-item list-group-item-action'>";
                                            echo "<i class='fas fa-file-pdf text-danger me-2'></i> $label";
                                            echo "</a>";
                                        }
                                        echo "</div>";
                                    } else {
                                        echo "<span class='text-muted'>No File</span>";
                                    }
                                    echo "</td>";                                        
                                    echo "</tr>";
                                    $id++;
                                }
                            ?>
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
                                    <a class="page-link arrow" href="?page=<?php echo $page - 1; ?>&nama_sub_menu=<?php echo $search; ?>" tabindex="-1">←</a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&nama_sub_menu=<?php echo $search; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                    <a class="page-link arrow" href="?page=<?php echo $page + 1; ?>&nama_sub_menu=<?php echo $search; ?>">→</a>
                                </li>
                            </ul>
                        </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Main Content -->

<?php include 'footer.php'; ?>