<?php
/** @var mysqli $koneksi */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
}
date_default_timezone_set('Asia/Jakarta');
mysqli_query($koneksi, "SET time_zone = '+07:00'"); // Sinkronisasi jam DB

// ==========================================
// PENGATURAN JAM PENGIRIMAN (UBAH JADI 14:00)
// ==========================================
$jam_pengiriman = '14:03'; 
$jam_sekarang = date('H:i');

// Jika jam sekarang SAMA DENGAN jam pengiriman, baru jalankan fungsi
if ($jam_sekarang === $jam_pengiriman) {
    $today_date = date('Y-m-d');

    function proses_notifikasi($koneksi, $tanggal_berakhir, $id_target, $jenis_target, $email_penerima, $nama_penerima, $nama_target) {
        global $today_date;

        if (empty($email_penerima)) return;

        $tgl_mulai = new DateTime($today_date);
        $tgl_akhir = new DateTime($tanggal_berakhir);
        $selisih = $tgl_mulai->diff($tgl_akhir)->days;
        $jenis_notif = '';
        
        // Logika Normal
        if ($selisih == 365) $jenis_notif = '1_tahun';
        elseif ($selisih == 180) $jenis_notif = '6_bulan';
        elseif ($selisih == 30) $jenis_notif = '1_bulan';
        elseif ($selisih == 1) $jenis_notif = '1_hari';

        // --- FITUR DEBUG (TAMPILKAN DI LAYAR) ---
        // Ini biar kamu tahu sistem ngecek selisih berapa
        echo "<div style='position:fixed; top:10px; left:10px; background:black; color:lime; padding:5px; z-index:9999; font-size:12px;'>";
        echo "Cek: $nama_target | Selisih: $selisih hari | Notif: " . ($jenis_notif ? $jenis_notif : 'Tidak ada') . "<br>";
        echo "</div>";
        // ----------------------------------------

        if ($jenis_notif != '') {
            
            $cek_log = mysqli_query($koneksi, "SELECT * FROM log_notifikasi 
                WHERE jenis_target='$jenis_target' 
                AND id_target='$id_target' 
                AND jenis_notif='$jenis_notif' 
                AND DATE(tanggal_kirim) = CURDATE()");
            
            if (mysqli_num_rows($cek_log) == 0) {
                
                $mail = new PHPMailer(true); 
                
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.office365.com'; 
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'p4m@polibatam.ac.id'; 
                    $mail->Password   = 'Polisi_militer789'; // GANTI INI
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

                    mysqli_query($koneksi, "INSERT INTO log_notifikasi (jenis_target, id_target, jenis_notif, tanggal_kirim) 
                        VALUES ('$jenis_target', '$id_target', '$jenis_notif', NOW())");
                    
                    echo "<div style='position:fixed; top:40px; left:10px; background:green; color:white; padding:5px; z-index:9999; font-size:12px;'>EMAIL BERHASIL DIKIRIM KE: $email_penerima</div>";

                } catch (Exception $e) {
                    echo "<div style='position:fixed; top:40px; left:10px; background:red; color:white; padding:5px; z-index:9999; font-size:12px;'>GAGAL: " . $mail->ErrorInfo . "</div>";
                    error_log("Gagal kirim email ke $email_penerima: " . $mail->ErrorInfo);
                }
            } else {
                echo "<div style='position:fixed; top:40px; left:10px; background:orange; color:black; padding:5px; z-index:9999; font-size:12px;'>Batal: Email hari ini sudah pernah dikirim (Cek log_notifikasi)</div>";
            }
        }
    }

    // Cek Program Studi
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

} else {
    // Pesan kalau belum jam 14:00
    echo "<div style='position:fixed; top:10px; left:10px; background:gray; color:white; padding:5px; z-index:9999; font-size:12px;'>Menunggu jam 14:00. Sekarang jam: $jam_sekarang</div>";
}
?>