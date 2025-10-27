<?php
// ===== PROSES DELETE DULU SEBELUM ADA OUTPUT APAPUN =====
if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $jsonFile = __DIR__ . '/data/document_types.json';
    $types = json_decode(file_get_contents($jsonFile), true);
    
    $idx = intval($_GET['idx']);
    $submenuIdx = intval($_GET['submenu_idx']);
    
    // Validasi
    if (isset($types[$idx]['submenu'][$submenuIdx])) {
        $submenuName = $types[$idx]['submenu'][$submenuIdx]['name'];
        
        // Hapus submenu
        array_splice($types[$idx]['submenu'], $submenuIdx, 1);
        
        // Simpan ke JSON
        @file_put_contents($jsonFile, json_encode(array_values($types), JSON_PRETTY_PRINT));
        
        header("Location: conf_document.php?success=submenu_deleted&name=" . urlencode($submenuName));
        exit;
    } else {
        header("Location: conf_document.php?error=invalid_submenu");
        exit;
    }
}

// ===== SETELAH CEK CONFIRM, BARU LOAD HEADER =====
include('header.php');
include('config_head.php');

$jsonFile = __DIR__ . '/data/document_types.json';
$types = json_decode(file_get_contents($jsonFile), true);

// Validasi index
if (!isset($_GET['idx']) || !isset($types[$_GET['idx']])) {
    echo "<br><br><div class='container'><div class='alert alert-danger'>";
    echo "<strong>Error!</strong> Invalid document type index</div>";
    echo "<a href='conf_document.php' class='btn btn-default'>Kembali ke Configuration</a>";
    echo "</div>";
    exit;
}

$idx = intval($_GET['idx']);
$current = $types[$idx];

// Cek parameter submenu_idx dengan berbagai nama alternatif
$submenuIdx = null;
if (isset($_GET['submenu_idx'])) {
    $submenuIdx = intval($_GET['submenu_idx']);
} elseif (isset($_GET['sub_idx'])) {
    $submenuIdx = intval($_GET['sub_idx']);
} elseif (isset($_GET['subidx'])) {
    $submenuIdx = intval($_GET['subidx']);
} elseif (isset($_GET['s'])) {
    $submenuIdx = intval($_GET['s']);
}

// Validasi submenu index
if ($submenuIdx === null) {
    echo "<br><br><div class='container'><div class='alert alert-danger'>";
    echo "<strong>Error!</strong> Parameter submenu index tidak ditemukan di URL<br>";
    echo "<p style='margin-top:10px;'>URL yang benar harus seperti: <code>del_submenu.php?idx=0&submenu_idx=0</code></p>";
    echo "</div>";
    echo "<a href='conf_document.php' class='btn btn-default'>Kembali ke Configuration</a>";
    echo "</div>";
    exit;
}

if (!isset($current['submenu']) || !is_array($current['submenu'])) {
    echo "<br><br><div class='container'><div class='alert alert-danger'>";
    echo "<strong>Error!</strong> Document type <strong>" . htmlspecialchars($current['name']) . "</strong> tidak memiliki submenu.";
    echo "</div>";
    echo "<a href='conf_document.php' class='btn btn-default'>Kembali</a>";
    echo "</div>";
    exit;
}

if (!isset($current['submenu'][$submenuIdx])) {
    echo "<br><br><div class='container'><div class='alert alert-danger'>";
    echo "<strong>Error!</strong> Invalid submenu index: <code>$submenuIdx</code>";
    echo "<p style='margin-top:10px;'>Submenu yang tersedia:</p><ul class='list-group'>";
    foreach ($current['submenu'] as $idx_sub => $sub) {
        echo "<li class='list-group-item'>";
        echo "<strong>Index $idx_sub:</strong> " . htmlspecialchars($sub['name']);
        echo "</li>";
    }
    echo "</ul></div>";
    echo "<a href='conf_document.php' class='btn btn-default'>Kembali ke Configuration</a>";
    echo "</div>";
    exit;
}

$currentSubmenu = $current['submenu'][$submenuIdx];
?>

<br /><br />
<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-6 well well-lg">
 <h2 class="text-danger">
     <span class="glyphicon glyphicon-trash"></span> Delete Submenu
 </h2>
 <hr>

 <div class="alert alert-danger">
     <span class="glyphicon glyphicon-warning-sign"></span>
     <strong>Peringatan!</strong> Anda akan menghapus submenu berikut:
 </div>

 <div class="panel panel-default">
     <div class="panel-heading">
         <h4 class="panel-title">Informasi Submenu</h4>
     </div>
     <div class="panel-body">
         <table class="table table-bordered">
             <tr>
                 <th width="40%">Parent Document Type</th>
                 <td><strong><?php echo htmlspecialchars($current['name']); ?></strong></td>
             </tr>
             <tr>
                 <th>Submenu Name</th>
                 <td><strong class="text-danger"><?php echo htmlspecialchars($currentSubmenu['name']); ?></strong></td>
             </tr>
             <tr>
                 <th>Submenu ID</th>
                 <td><code><?php echo htmlspecialchars($currentSubmenu['id']); ?></code></td>
             </tr>
             <tr>
                 <th>Filter Configuration</th>
                 <td>
                     <?php 
                     if (isset($currentSubmenu['filter_config']) && is_array($currentSubmenu['filter_config'])) {
                         $activeFilters = array_filter($currentSubmenu['filter_config']);
                         if (count($activeFilters) > 0) {
                             echo "<ul style='margin:0; padding-left:20px;'>";
                             foreach ($activeFilters as $key => $value) {
                                 if ($value === true) {
                                     $label = ucfirst(str_replace('_', ' ', $key));
                                     echo "<li>" . htmlspecialchars($label) . "</li>";
                                 }
                             }
                             echo "</ul>";
                         } else {
                             echo "<em class='text-muted'>Tidak ada filter aktif</em>";
                         }
                     } else {
                         echo "<em class='text-muted'>Tidak ada konfigurasi filter</em>";
                     }
                     ?>
                 </td>
             </tr>
         </table>
     </div>
 </div>

 <div class="alert alert-warning">
     <span class="glyphicon glyphicon-exclamation-sign"></span>
     <strong>Perhatian:</strong>
     <ul style="margin: 5px 0 0 0;">
         <li>Penghapusan submenu <strong>tidak dapat dibatalkan</strong></li>
         <li>Semua konfigurasi filter submenu akan hilang</li>
         <li>Jika ada dokumen yang menggunakan submenu ini, mereka mungkin tidak dapat diakses dengan benar</li>
     </ul>
 </div>

 <div class="form-group">
     <p class="text-center" style="font-size: 16px; margin-bottom: 20px;">
         <strong>Apakah Anda yakin ingin menghapus submenu ini?</strong>
     </p>
     
     <a href="del_submenu.php?idx=<?php echo $idx; ?>&submenu_idx=<?php echo $submenuIdx; ?>&confirm=yes" 
        class="btn btn-danger btn-lg btn-block"
        onclick="return confirm('KONFIRMASI: Anda yakin ingin menghapus submenu \'<?php echo addslashes($currentSubmenu['name']); ?>\'?\n\nPenghapusan tidak dapat dibatalkan!');">
         <span class="glyphicon glyphicon-trash"></span> Ya, Hapus Submenu
     </a>
     
     <a href="conf_document.php" class="btn btn-default btn-lg btn-block">
         <span class="glyphicon glyphicon-arrow-left"></span> Batal, Kembali
     </a>
 </div>

</div>
</div>

<style>
.panel-heading {
    background-color: #f5f5f5;
}
.table th {
    background-color: #f9f9f9;
}
</style>

<script>
$(document).ready(function(){
    // Auto focus pada tombol Batal untuk keamanan
    $('a.btn-default').focus();
});
</script>