<?php
// upload_evidence.php
session_start();
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));

// require koneksi
if (!file_exists('koneksi.php')) {
    die("koneksi.php tidak ditemukan.");
}
include 'koneksi.php';

// Function untuk redirect dengan pesan error
function redirectWithError($message, $drf = '') {
    $_SESSION['upload_error'] = $message;
    if (!empty($drf)) {
        header("Location: lihat_evidence.php?drf=" . urlencode($drf));
    } else {
        header("Location: wi_prod.php");
    }
    exit;
}

// Function untuk redirect dengan sukses
function redirectWithSuccess($message, $drf) {
    $_SESSION['upload_success'] = $message;
    header("Location: lihat_evidence.php?drf=" . urlencode($drf));
    exit;
}

// cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: wi_prod.php');
    exit;
}

// CSRF
$csrf_post = $_POST['csrf_token'] ?? '';
if ($csrf_post === '' || $csrf_post !== ($_SESSION['csrf_token'] ?? '')) {
    redirectWithError("Token keamanan tidak valid. Silakan refresh halaman dan coba lagi.");
}

// ambil input dan sanitasi awal
$drf_raw = $_POST['drf'] ?? '';
$drf = mysqli_real_escape_string($link, trim($drf_raw));
$notes = mysqli_real_escape_string($link, trim($_POST['notes'] ?? ''));

// Tentukan uploader secara robust (session prioritas)
$uploader = '';
if (!empty($_SESSION['nrp'])) {
    $uploader = $_SESSION['nrp'];
} elseif (!empty($_SESSION['username'])) {
    $uploader = $_SESSION['username'];
} elseif (!empty($_SESSION['user'])) {
    $uploader = $_SESSION['user'];
} elseif (!empty($_SESSION['name'])) {
    $uploader = $_SESSION['name'];
} elseif (!empty($_SESSION['email'])) {
    $uploader = $_SESSION['email'];
} elseif (!empty($nrp)) {
    $uploader = $nrp;
} elseif (!empty($email)) {
    $uploader = $email;
} else {
    $uploader = 'system';
}
$uploader_db = mysqli_real_escape_string($link, $uploader);

// validasi drf
if ($drf === '') {
    redirectWithError("DRF tidak diberikan.");
}

// file check
if (!isset($_FILES['sos_file'])) {
    redirectWithError("File tidak ditemukan. Silakan pilih file terlebih dahulu.", $drf);
}

$file = $_FILES['sos_file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errCode = intval($file['error']);
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => "File terlalu besar (melebihi batas server).",
        UPLOAD_ERR_FORM_SIZE => "File terlalu besar (melebihi batas form).",
        UPLOAD_ERR_PARTIAL => "File hanya terupload sebagian. Silakan coba lagi.",
        UPLOAD_ERR_NO_FILE => "Tidak ada file yang dipilih.",
        UPLOAD_ERR_NO_TMP_DIR => "Folder temporary tidak ditemukan.",
        UPLOAD_ERR_CANT_WRITE => "Gagal menulis file ke disk.",
        UPLOAD_ERR_EXTENSION => "Upload dihentikan oleh ekstensi PHP."
    ];
    $message = $errorMessages[$errCode] ?? "Upload error (kode: $errCode)";
    redirectWithError($message, $drf);
}

// konfigurasi validasi
$allowed_ext = ['pdf','jpg','jpeg','png'];
$max_size = 10 * 1024 * 1024; // 10MB

$originalName = basename($file['name']);
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if ($ext === '') {
    redirectWithError("File tidak memiliki ekstensi. Pastikan file Anda memiliki ekstensi yang valid (.pdf, .jpg, .jpeg, .png).", $drf);
}

if (!in_array($ext, $allowed_ext)) {
    redirectWithError("Format file .$ext tidak diizinkan. Silakan upload file dengan format: PDF, JPG, JPEG, atau PNG.", $drf);
}

if ($file['size'] > $max_size) {
    $sizeMB = round($file['size'] / (1024 * 1024), 2);
    redirectWithError("File terlalu besar ($sizeMB MB). Maksimal ukuran file adalah 10 MB.", $drf);
}

// cek MIME type menggunakan finfo (tambahan keamanan)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowed_mimes = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];

$validMime = false;
if (isset($allowed_mimes[$ext])) {
    if (strpos($mime, explode('/', $allowed_mimes[$ext])[0]) !== false || $mime === $allowed_mimes[$ext]) {
        $validMime = true;
    }
}
if (!$validMime) {
    redirectWithError("Tipe file tidak valid. File yang Anda upload bukan file $ext yang sebenarnya.", $drf);
}

// pastikan folder evidence ada
$uploadDir = __DIR__ . '/evidence/';
$webDir = 'evidence/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        redirectWithError("Gagal membuat folder upload. Silakan hubungi administrator.", $drf);
    }
}

// ✅ UBAH PREFIX: dari 'sos_' menjadi 'evidence_'
$time = time();
$safeBase = preg_replace("/[^A-Za-z0-9_\-]/", '_', pathinfo($originalName, PATHINFO_FILENAME));
$finalName = 'evidence_' . preg_replace('/_+/', '_', $safeBase) . '_' . $time . '.' . $ext;
$target = $uploadDir . $finalName;

// pindahkan file
if (!move_uploaded_file($file['tmp_name'], $target)) {
    redirectWithError("Gagal memindahkan file. Silakan coba lagi atau hubungi administrator.", $drf);
}

// OPTIONAL: jika sebelumnya sudah ada file pada kolom evidence (sos_file) -> hapus file lama
$rowQ = mysqli_query($link, "SELECT sos_file FROM docu WHERE no_drf = '" . mysqli_real_escape_string($link, $drf) . "' LIMIT 1");
if ($rowQ && mysqli_num_rows($rowQ) > 0) {
    $r = mysqli_fetch_assoc($rowQ);
    $old = $r['sos_file'] ?? '';
    if (!empty($old)) {
        $oldPath = $uploadDir . $old;
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }
}

// update docu
$finalName_db = mysqli_real_escape_string($link, $finalName);
$notes_db = mysqli_real_escape_string($link, $notes);

$update = "UPDATE docu SET 
    sos_file = '$finalName_db',
    sos_uploaded_by = '$uploader_db',
    sos_upload_date = NOW(),
    sos_notes = '$notes_db'
    WHERE no_drf = '" . mysqli_real_escape_string($link, $drf) . "' LIMIT 1";

$r = mysqli_query($link, $update);
if (!$r) {
    @unlink($target);
    redirectWithError("Gagal menyimpan ke database: " . mysqli_error($link), $drf);
}

// sukses
redirectWithSuccess("File Evidence berhasil diupload!", $drf);
?>