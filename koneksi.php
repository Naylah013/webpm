<?php
$env = parse_ini_file(__DIR__ . '/.env');

if ($env === false) {
    die("File konfigurasi tidak ditemukan.");
}

$db_host = $env['DB_HOST'];
$db_user = $env['DB_USER'];
$db_pass = $env['DB_PASS'];
$db_name = $env['DB_NAME'];

$koneksi = mysqli_connect(
    $db_host,
    $db_user,
    $db_pass,
    $db_name
);

if (!$koneksi) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("Koneksi database gagal.");
}

mysqli_set_charset($koneksi, "utf8mb4");

function query($query) {
    global $koneksi;

    $result = mysqli_query($koneksi, $query);

    if (!$result) {
        error_log(mysqli_error($koneksi));
        return [];
    }

    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}
?>