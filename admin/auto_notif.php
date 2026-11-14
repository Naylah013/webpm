<?php
/** @var mysqli $koneksi */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
}

// Set zona waktu dan sinkronisasi jam database
date_default_timezone_set('Asia/Jakarta');
mysqli_query($koneksi, "SET time_zone = '+07:00'"); 

// ==========================================
// PENGATURAN JAM PENGIRIMAN (JAM 08:00 PAGI)
// ==========================================
$jam_pengiriman = '08:00'; 
$jam_sekarang = date('H:i');

// Jika jam sekarang SAMA DENGAN jam pengiriman, baru jalankan fungsi
if ($jam_sekarang === $jam_pengiriman) {
    $today_date = date('Y-m-d');

    function proses_notifikasi($koneksi, $tanggal_berakhir, $id_target, $jenis_target, $email_penerima, $nama_target) {
        global $today_date;

        if (empty($email_penerima)) return;

        $tgl_mulai = new DateTime($today_date);
        $tgl_akhir = new DateTime($tanggal_berakhir);
        $selisih = $tgl_mulai->diff($tgl_akhir)->days;
        $jenis_notif = '';
        
        // Logika Normal Pengecekan Selisih Tanggal
        if ($selisih == 365) $jenis_notif = '1_tahun';
        elseif ($selisih == 180) $jenis_notif = '6_bulan';
        elseif ($selisih == 30) $jenis_notif = '1_bulan';
        elseif ($selisih == 1) $jenis_notif = '1_hari';

        if ($jenis_notif != '') {
            
            // Cek log untuk mencegah pengiriman ganda di hari yang sama
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
                    $mail->Password   = 'Polisi_militer789'; // Pastikan password ini aman
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->setFrom('p4m@polibatam.ac.id', 'P4M Polibatam');
                    $mail->addAddress($email_penerima);
                    $mail->isHTML(true);
                    
                    // Format Subject Email
                    $mail->Subject = "Peringatan Akreditasi: $nama_target";
                    
                    // Format Body Email Sesuai Permintaan Dosen
                    $mail->Body = "
                        <p>Yth. Bapak/Ibu Ketua Program Studi <b>$nama_target</b>,</p>
                        <br>
                        <p>Kami informasikan bahwa masa berlaku akreditasi Program Studi <b>$nama_target</b> akan berakhir pada <b>$tanggal_berakhir</b>.</p>
                        <br>
                        <p>Demikian pemberitahuan ini kami sampaikan. Terima kasih.</p>
                        <br>
                        <p>Hormat kami,<br>
                        Pusat Penjaminan Mutu dan Pengembangan Pembelajaran (P4M)<br>
                        Politeknik Negeri Batam</p>
                    ";
                    
                    $mail->send();

                    // Simpan log jika berhasil
                    mysqli_query($koneksi, "INSERT INTO log_notifikasi (jenis_target, id_target, jenis_notif, tanggal_kirim) 
                        VALUES ('$jenis_target', '$id_target', '$jenis_notif', NOW())");

                } catch (Exception $e) {
                    // Hanya catat error di log server, tidak ditampilkan ke layar
                    error_log("Gagal kirim email ke $email_penerima: " . $mail->ErrorInfo);
                }
            }
        }
    }

    // ==========================================
    // QUERY DATABASE (MENGAMBIL SEMUA PRODI)
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
            proses_notifikasi($koneksi, $row['tanggal_berakhir'], $row['id_prodi'], 'program_studi', $row['email_kps'], $row['nama_program_studi']);
        }
    }
}
// Selesai. Tidak ada blok "else" untuk menampilkan pesan menunggu.
?>