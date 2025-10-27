<?php
// ===== PROSES DELETE DULU SEBELUM ADA OUTPUT APAPUN =====
$jsonFile = __DIR__ . '/data/document_types.json';

// Cek apakah file JSON ada
if (!file_exists($jsonFile)) { 
    header("Location: conf_document.php?error=nofile"); 
    exit; 
}

// Load data
$types = json_decode(file_get_contents($jsonFile), true);

// Validasi parameter idx
if (!isset($_GET['idx']) || !isset($types[$_GET['idx']])) {
    header("Location: conf_document.php?error=notfound");
    exit;
}

// Proses delete
$idx = intval($_GET['idx']);
unset($types[$idx]);

// Simpan kembali ke JSON
@file_put_contents($jsonFile, json_encode(array_values($types), JSON_PRETTY_PRINT));

// Redirect dengan pesan sukses
header("Location: conf_document.php?success=deleted");
exit;
?>