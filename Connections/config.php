<?php
// Pastikan parameter koneksi benar
$host = 'localhost';
$username = 'admin';  // atau username lain
$password = 'asdf123!';      // password database Anda
$database = 'doc';   // nama database

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>