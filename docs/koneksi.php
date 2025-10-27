<?php
// koneksi.php - versi debug-friendly (testing)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mssqlHost = "localhost";
$mssqlUser = "admin";
$mssqlPass = "asdf123!";
$mssqlDB   = "doc";

// koneksi
$link = mysqli_connect($mssqlHost, $mssqlUser, $mssqlPass, $mssqlDB);

// cek koneksi
if (!$link) {
    // tampilkan pesan error yang benar untuk koneksi
    die("Koneksi ke MySQL gagal: " . mysqli_connect_error());
}

// set charset aman
mysqli_set_charset($link, "utf8mb4");

// Optional: hapus echo setelah selesai debug
// echo "Koneksi MySQL OK ke {$mssqlHost} / DB: {$mssqlDB}";
?>