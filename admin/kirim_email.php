<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
include '../koneksi.php';
/** @var mysqli $koneksi */

// ==========================
// VALIDASI ID
// ==========================
if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// ==========================
// AMBIL DATA DARI DATABASE
// ==========================
$data = mysqli_query($koneksi, "SELECT * FROM kirim WHERE id_kirim='$id'");
$row = mysqli_fetch_assoc($data);

if (!$row) {
    die("Data tidak ditemukan");
}

$email = $row['email']; // 🔥 dari database
$jabatan  = $row['jabatan'];  // 🔥 dari database

// ==========================
// PHPMailer
// ==========================
$mail = new PHPMailer(true);

try {
    // ==========================
    // CONFIG SMTP OUTLOOK
    // ==========================
    $mail->isSMTP();
    $mail->Host       = 'smtp.office365.com';
    $mail->SMTPAuth   = true;
    $mail->Username = 'p4m@polibatam.ac.id';
    $mail->Password = 'Polisi_militer789';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // ==========================
    // DEBUG (aktifkan kalau error)
    // ==========================
    // $mail->SMTPDebug = 2;
    // $mail->Debugoutput = 'html';

    // ==========================
    // PENGIRIM (FIX)
    // ==========================
    $mail->setFrom('p4m@polibatam.ac.id', 'Sistem Akreditasi');

    // ==========================
    // PENERIMA (DINAMIS DARI DATABASE)
    // ==========================
    $mail->addAddress($email, $jabatan);

    // ==========================
    // ISI EMAIL
    // ==========================
    $mail->isHTML(true);
    $mail->Subject = 'Notifikasi Masa Berlaku Akreditasi';

    $mail->Body = "
        <p>Yth. <b>$jabatan</b>,</p>
        <p>Kami informasikan bahwa masa berlaku akreditasi Program Studi anda akan segera berakhir.</p>
        <p>Mohon untuk segera melakukan persiapan perpanjangan akreditasi.</p>
        <br>
        <p>Terima kasih.</p>
        <p><b>Pusat Penjaminan Mutu dan Pengembangan Pembelajaran</b></p>
    ";

    // ==========================
    // KIRIM EMAIL
    // ==========================
    $mail->send();

    header("Location: dashboard.php?status=sukses&id=$id");

} catch (Exception $e) {

    // DEBUG kalau mau lihat error asli
    // echo "Error: " . $mail->ErrorInfo;

    header("Location: dashboard.php?status=gagal&id=$id");
}
?>