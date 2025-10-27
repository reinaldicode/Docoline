<?php
// Pastikan parameter koneksi benar
$host = 'localhost';
$username = 'root';  // atau username lain
$password = '';      // password database Anda
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