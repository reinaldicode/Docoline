<?php
// session.php
include 'koneksi.php';

// ✅ PERBAIKAN: Cek apakah session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Storing Session
$user_check = $_SESSION['username'] ?? '';

// Jika tidak ada user_check, redirect ke login
if (empty($user_check)) {
    mysqli_close($link);
    header('Location: login.php');
    exit;
}

// SQL Query To Fetch Complete Information Of User
$user_check_escaped = mysqli_real_escape_string($link, $user_check);
$ses_sql = mysqli_query($link, "SELECT * FROM users WHERE username='$user_check_escaped'");

if (!$ses_sql || mysqli_num_rows($ses_sql) == 0) {
    // User tidak ditemukan di database
    mysqli_close($link);
    session_destroy();
    header('Location: login.php');
    exit;
}

$row = mysqli_fetch_array($ses_sql);
$name = $row['name'];
$state = $row['state'];
$nrp = $row['username'];
$sec = $row['section'];
$email = $row['email'];
$pass = $row['password'];
$nrp2 = $row['username'];
$pass2 = $row['password'];

// Cek apakah user valid
if (!isset($name) || empty($name)) {
    mysqli_close($link);
    session_destroy();
    header('Location: login.php');
    exit;
}
?>