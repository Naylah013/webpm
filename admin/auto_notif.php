<?php
/** @var mysqli $koneksi */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Pastikan path ke autoload benar (dari folder admin naik satu tingkat)
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
}

date_default_timezone_set('Asia/Jakarta');
$today_date = date('Y-m-d');

// Fungsi untuk ngecek dan kirim notifikasi
function proses_notifikasi($koneksi, $tanggal_berakhir, $id_target, $jenis_target, $email_penerima, $nama_penerima, $nama_target) {
    global $today_date;

    // Jika email kosong, langsung lewati (skip)
    if (empty($email_penerima)) return;

    $tgl_mulai = new DateTime($today_date);
    $tgl_akhir = new DateTime($tanggal_berakhir);
    
    // Hitung selisih hari
    $selisih = $tgl_mulai->diff($tgl_akhir)->days;

    $jenis_notif = '';
    // Logika pengecekan waktu (1 tahun, 6 bulan, 1 bulan, 1 hari)
    // Untuk testing, kamu bisa ubah angkanya jadi (misal: $selisih <= 365 && $selisih > 0)
    if ($selisih == 365) $jenis_notif = '1_tahun';
    elseif ($selisih == 180) $jenis_notif = '6_bulan';
    elseif ($selisih == 30) $jenis_notif = '1_bulan';
    elseif ($selisih == 1) $jenis_notif = '1_hari';

    // Jika hari ini masuk kriteria notif
    if ($jenis_notif != '') {
        
        // Cek log, apakah sudah pernah dikirim?
        $cek_log = mysqli_query($koneksi, "SELECT * FROM log_notifikasi 
            WHERE jenis_target='$jenis_target' 
            AND id_target='$id_target' 
            AND jenis_notif='$jenis_notif' 
            AND YEAR(tanggal_kirim) = YEAR(NOW())");
        
        if (mysqli_num_rows($cek_log) == 0) {
            
            // ==========================================
            // PROSES KIRIM EMAIL
            // ==========================================
            $mail = new PHPMailer(true); // Objek dibuat di luar try agar tidak error kuning
            
            try {
                // SETTING SMTP (Sesuaikan dengan .env atau settingan pembimbing)
                $mail->isSMTP();
                $mail->Host       = 'smtp.office365.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'p4m@polibatam.ac.id'; 
                $mail->Password   = 'Polisi_militer789'; // 🔥 GANTI INI
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('p4m@polibatam.ac.id', 'P4M Polibatam');
                $mail->addAddress($email_penerima, $nama_penerima);
                $mail->isHTML(true);
                
                $mail->Subject = "Peringatan Akreditasi: $nama_target ($jenis_notif)";
                $mail->Body = "
                    <p>Yth. <b>$nama_penerima</b>,</p>
                    <p>Kami informasikan bahwa masa berlaku akreditasi untuk <b>$nama_target</b> akan berakhir pada <b>$tanggal_berakhir</b>.</p>
                    <p>Status peringatan: <b>$jenis_notif</b> sebelum masa berlaku habis.</p>
                    <p>Mohon untuk segera melakukan persiapan perpanjangan akreditasi.</p>
                    <br>
                    <p>Terima kasih.</p>
                    <p><b>Pusat Penjaminan Mutu dan Pengembangan Pembelajaran (P4M)</b></p>
                ";

                $mail->send();

                // Catat ke Log jika sukses terkirim
                mysqli_query($koneksi, "INSERT INTO log_notifikasi (jenis_target, id_target, jenis_notif, tanggal_kirim) 
                    VALUES ('$jenis_target', '$id_target', '$jenis_notif', NOW())");

            } catch (Exception $e) {
                // Jika gagal, jangan hentikan web. Cukup catat ke error log PHP.
                error_log("Gagal kirim email ke $email_penerima: " . $mail->ErrorInfo);
            }
        }
    }
}

// ==========================================
// 1. CEK PROGRAM STUDI
// ==========================================
$query_prodi = mysqli_query($koneksi, "
    SELECT a.tanggal_berakhir, ps.id_prodi, ps.nama_program_studi, ps.email_kps
    FROM akreditasi a
    JOIN program_studi ps ON a.id_prodi = ps.id_prodi
    INNER JOIN (
        SELECT id_prodi, MAX(tanggal_berakhir) as max_date
        FROM akreditasi WHERE akreditasi = 'program studi' GROUP BY id_prodi
    ) latest ON a.id_prodi = latest.id_prodi AND a.tanggal_berakhir = latest.max_date
    WHERE a.akreditasi = 'program studi'
");

if ($query_prodi) {
    while ($row = mysqli_fetch_assoc($query_prodi)) {
        proses_notifikasi($koneksi, $row['tanggal_berakhir'], $row['id_prodi'], 'program_studi', $row['email_kps'], 'Ketua Program Studi', $row['nama_program_studi']);
    }
}

// ==========================================
// 2. CEK UNIT KERJA
// ==========================================
$query_unit = mysqli_query($koneksi, "
    SELECT a.tanggal_berakhir, u.id_unit, u.nama_unit_kerja, u.email_unit
    FROM akreditasi a
    JOIN unit u ON a.id_unit = u.id_unit
    INNER JOIN (
        SELECT id_unit, MAX(tanggal_berakhir) as max_date
        FROM akreditasi WHERE akreditasi = 'unit kerja' GROUP BY id_unit
    ) latest ON a.id_unit = latest.id_unit AND a.tanggal_berakhir = latest.max_date
    WHERE a.akreditasi = 'unit kerja'
");

if ($query_unit) {
    while ($row = mysqli_fetch_assoc($query_unit)) {
        proses_notifikasi($koneksi, $row['tanggal_berakhir'], $row['id_unit'], 'unit', $row['email_unit'], 'Kepala Unit Kerja', $row['nama_unit_kerja']);
    }
}
?>