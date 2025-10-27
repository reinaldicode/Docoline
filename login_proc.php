<?php
session_start(); // Memulai Session

include 'koneksi.php'; // Menyertakan file koneksi

// 1. Periksa apakah form sudah di-submit
if (isset($_POST['username']) && isset($_POST['password'])) {

    // 2. Ambil dan bersihkan input
    // Mempertahankan logika trim "gzb" dari kode lama
    $username = addslashes(trim(strtolower($_POST['username']), "gzb"));
    $password = addslashes(trim($_POST['password']));

    // 3. Validasi dasar: pastikan input tidak kosong
    if (empty($username) || empty($password)) {
        header("Location: login.php?error=Username and Password are required!");
        exit();
    }

    // ================================================================
    // PROSES LOGIN HANYA MENGGUNAKAN DATABASE LOKAL
    // ================================================================
    
    // 4. Gunakan Prepared Statement untuk keamanan dari SQL Injection
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($link, $query);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);

        // 5. Verifikasi pengguna dan password
        if ($data) { // Jika user ditemukan
            
            // Verifikasi password (kode ini menangani password plain-text dan hash)
            if ($password == $data['password'] || password_verify($password, $data['password'])) {
                
                // Jika password cocok, set semua session
                $_SESSION['username'] = $data['username'];
                $_SESSION['name'] = $data['name'];
                $_SESSION['email'] = $data['email'];
                $_SESSION['section'] = $data['section'];
                $_SESSION['state'] = $data['state'];
                $_SESSION['level'] = $data['level'];
                $_SESSION['user_authentication'] = "valid";

                session_regenerate_id(true); // Keamanan tambahan

                header("location: index_login.php"); // Arahkan ke halaman utama setelah login
                exit();

            } else {
                // Jika password salah
                header("Location: login.php?error=Your Password Incorrect!");
                exit();
            }
        } else {
            // Jika username tidak ditemukan di database
            header("Location: login.php?error=Your account doesn't exist! Please contact administrator.");
            exit();
        }
    } else {
        // Jika query gagal dipersiapkan (error database)
        header("Location: login.php?error=Database query failed. Please try again later.");
        exit();
    }
} else {
    // Jika file diakses langsung tanpa submit form
    header("Location: login.php");
    exit();
}
?>