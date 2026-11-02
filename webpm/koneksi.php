<?php
$koneksi = mysqli_connect("localhost", "root", "", "web");

if (mysqli_connect_errno()) {
    echo "Koneksi database gagal : " . mysqli_connect_error();
}

function query($query) {
    global $koneksi;
    $result = mysqli_query($koneksi, $query);
    $rows = []; 
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
?>
