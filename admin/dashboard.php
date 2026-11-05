<?php
include '../koneksi.php';
/** @var mysqli $koneksi */

// 1. PANGGIL AUTO NOTIFIKASI (Wajib di atas header)
include 'auto_notif.php'; 

// 2. BARU PANGGIL HEADER
include 'header.php'; 

$today = date('Y-m-d');
$months_later = date('Y-m-d', strtotime('+12 months'));

$notif_data = [];

// ===========================
// 1. Akreditasi Perguruan Tinggi
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
?>

<head>
    <style>
    .logo-image { height: 40px; width: auto; }
    /* CSS tabel tidak dipakai lagi di sini, jadi dihapus agar ringan */
    </style>
</head>

<!-- isi -->
<div class="container-fluid pt-4 px-4">
    <h5 class="mb-4 text-primary">Dashboard</h5>
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">
                <!-- Notifikasi Masa Berlaku Akreditasi -->
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
                <?php else: ?>
                    <div class="alert alert-success" role="alert">
                        Tidak ada peringatan akreditasi untuk saat ini.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- End isi -->

<?php include 'footer.php'; ?>