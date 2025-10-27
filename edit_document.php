<?php
// ini edit_document.php - FIXED VERSION
// ===== PROSES POST DULU SEBELUM ADA OUTPUT APAPUN =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $jsonFile = __DIR__ . '/data/document_types.json';
    $types = json_decode(file_get_contents($jsonFile), true);
    
    $idx = intval($_POST['idx']);
    $name = trim($_POST['name']);
    $hasSubmenu = isset($_POST['has_submenu']) ? true : false;
    
    // Ambil filter config dari checkbox yang di-submit
    $filterConfig = [];
    
    // ✅ FIXED: Ambil semua filter keys dari filter_options.json
    $filterOptionsFile = __DIR__ . '/data/filter_options.json';
    $availableFilters = json_decode(file_get_contents($filterOptionsFile), true);
    $availableFilterKeys = array_keys($availableFilters);
    
    foreach ($availableFilterKeys as $key) {
        $filterConfig[$key] = isset($_POST['filter_' . $key]) ? true : false;
    }
    
    if ($name !== '') {
        // Check duplicate (kecuali dirinya sendiri)
        $tmp = $types;
        unset($tmp[$idx]);
        $existingNames = array_column($tmp, 'name');
        $lower = array_map('strtolower', $existingNames);
        
        if (in_array(strtolower($name), $lower)) {
            header("Location: edit_document.php?idx=$idx&error=duplicate");
            exit;
        } else {
            // Update data
            $types[$idx]['name'] = $name;
            $types[$idx]['has_submenu'] = $hasSubmenu;
            $types[$idx]['filter_config'] = $filterConfig;
            
            // Generate ID baru dari nama
            $types[$idx]['id'] = strtolower(str_replace(' ', '_', $name));
            
            // Set use_custom_file = false untuk document type baru
            if (!isset($types[$idx]['legacy_file']) || empty($types[$idx]['legacy_file'])) {
                $types[$idx]['use_custom_file'] = false;
                $types[$idx]['custom_file'] = '';
            }
            
            // ===== PERBAIKAN: Inisialisasi submenu array =====
            if ($hasSubmenu) {
                // Jika has_submenu diaktifkan tapi submenu belum ada, inisialisasi array kosong
                if (!isset($types[$idx]['submenu']) || !is_array($types[$idx]['submenu'])) {
                    $types[$idx]['submenu'] = [];
                }
                
                // ✅ SYNC: Update filter_config ke semua submenu yang ada
                if (!empty($types[$idx]['submenu'])) {
                    foreach ($types[$idx]['submenu'] as $subIdx => $submenu) {
                        // Submenu inherit parent filter config sebagai default
                        // Tapi tetap pertahankan custom config jika sudah diatur
                        if (!isset($submenu['filter_config']) || empty($submenu['filter_config'])) {
                            $types[$idx]['submenu'][$subIdx]['filter_config'] = $filterConfig;
                        }
                    }
                }
            } else {
                // Jika has_submenu dimatikan, hapus semua submenu
                $types[$idx]['submenu'] = [];
            }
            
            @file_put_contents($jsonFile, json_encode(array_values($types), JSON_PRETTY_PRINT));
            
            header("Location: conf_document.php?success=updated");
            exit;
        }
    } else {
        header("Location: edit_document.php?idx=$idx&error=empty");
        exit;
    }
}

// ===== SETELAH PROSES POST, BARU LOAD HEADER =====
include('header.php');
include('config_head.php');

$jsonFile = __DIR__ . '/data/document_types.json';
$types = json_decode(file_get_contents($jsonFile), true);

// Load filter options from JSON
$filterOptionsFile = __DIR__ . '/data/filter_options.json';
$availableFilters = [];

if (file_exists($filterOptionsFile)) {
    $availableFilters = json_decode(file_get_contents($filterOptionsFile), true);
} else {
    echo "<div class='alert alert-danger'>Error: filter_options.json not found!</div>";
    exit;
}

// Validasi index
if (!isset($_GET['idx']) || !isset($types[$_GET['idx']])) {
    echo "<div class='alert alert-danger'>Invalid index</div>"; 
    exit;
}

$idx = intval($_GET['idx']);
$current = $types[$idx];

// Pesan error dari redirect
$error = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'duplicate') {
        $error = "Document type dengan nama tersebut sudah ada.";
    } elseif ($_GET['error'] == 'empty') {
        $error = "Silakan masukkan nama document type.";
    }
}

$hasSubmenu = isset($current['has_submenu']) && $current['has_submenu'] === true;
$submenuCount = $hasSubmenu && !empty($current['submenu']) ? count($current['submenu']) : 0;
$legacyFile = isset($current['legacy_file']) ? $current['legacy_file'] : '';
$isLegacy = !empty($legacyFile);

// Get filter config (dengan default values)
$filterConfig = isset($current['filter_config']) ? $current['filter_config'] : [];

// Ensure all available filters are in config (default false if not exist)
foreach ($availableFilters as $key => $filter) {
    if (!isset($filterConfig[$key])) {
        $filterConfig[$key] = false;
    }
}

// ✅ NEW: Check if this document type has dynamic type options
$hasDynamicTypeOptions = false;
$dynamicTypeOptions = [];
if (isset($availableFilters['type']['options_by_doctype'][$current['name']])) {
    $hasDynamicTypeOptions = true;
    $dynamicTypeOptions = $availableFilters['type']['options_by_doctype'][$current['name']];
}
?>

<br /><br />
<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-6 well well-lg">
 <h2><span class="glyphicon glyphicon-edit"></span> Edit Document Type</h2>
 <p class="text-muted">Edit konfigurasi untuk: <strong><?php echo htmlspecialchars($current['name']); ?></strong></p>
 
 <?php if ($hasSubmenu): ?>
 <div class="alert alert-info">
     <span class="glyphicon glyphicon-info-sign"></span>
     <strong>Info:</strong> Document type ini memiliki <strong><?php echo $submenuCount; ?> submenu</strong>. 
     Filter configuration submenu dikelola di masing-masing submenu.
 </div>
 <?php endif; ?>
 
 <hr>

 <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
    </div>
 <?php endif; ?>

 <?php if ($submenuCount > 0 && !$hasSubmenu): ?>
    <div class="alert alert-warning">
        <span class="glyphicon glyphicon-warning-sign"></span>
        <strong>Perhatian!</strong> Dokumen ini memiliki <?php echo $submenuCount; ?> submenu. 
        Jika Anda menonaktifkan "Has Submenu", semua submenu akan dihapus!
    </div>
 <?php endif; ?>

 <form action="" method="POST">
     <input type="hidden" name="idx" value="<?php echo $idx; ?>">
     
     <div class="form-group">
         <label><strong>Document Type Name</strong> <span class="text-danger">*</span></label>
         <input type="text" class="form-control input-lg" name="name" 
                value="<?php echo htmlspecialchars($current['name']); ?>" required>
         <p class="help-block">
             <span class="glyphicon glyphicon-info-sign"></span>
             Nama tipe dokumen yang akan ditampilkan di menu navigation
         </p>
     </div>
     
     <div class="form-group">
         <div class="checkbox">
             <label>
                 <input type="checkbox" name="has_submenu" id="has_submenu" <?php echo $hasSubmenu ? 'checked' : ''; ?>> 
                 <strong>Dokumen ini memiliki Sub-Menu</strong>
             </label>
         </div>
         <p class="help-block">
             <span class="glyphicon glyphicon-info-sign"></span>
             Centang jika dokumen ini akan memiliki kategori sub-menu (misalnya: Production, Other, dll)
             <?php if ($submenuCount > 0): ?>
             <br><span class="text-info"><strong>Saat ini memiliki <?php echo $submenuCount; ?> submenu</strong></span>
             <?php endif; ?>
         </p>
     </div>

     <hr>

     <h4>
         <span class="glyphicon glyphicon-filter"></span> Filter Configuration
         <?php if ($hasSubmenu): ?>
         <span class="label label-info">Parent Default</span>
         <?php endif; ?>
     </h4>
     
     <?php if ($hasSubmenu): ?>
     <div class="alert alert-info">
         <span class="glyphicon glyphicon-info-sign"></span>
         Filter ini akan menjadi <strong>default untuk submenu baru</strong>. 
         Submenu yang sudah ada dapat memiliki konfigurasi filter sendiri.
     </div>
     <?php else: ?>
     <p class="text-muted">Pilih filter yang akan tersedia di halaman document list:</p>
     <?php endif; ?>

     <?php if ($isLegacy): ?>
     <div class="alert alert-warning">
         <span class="glyphicon glyphicon-warning-sign"></span>
         <strong>Info:</strong> Filter config di bawah tidak berlaku untuk legacy document type. Filter diatur di file legacy.
     </div>
     <?php endif; ?>

     <div class="well">
         <div class="row">
             <?php 
             $count = 0;
             $totalFilters = count($availableFilters);
             $halfPoint = ceil($totalFilters / 2);
             
             foreach ($availableFilters as $key => $filter): 
                 if ($count == 0) echo '<div class="col-xs-6">';
                 if ($count == $halfPoint) echo '</div><div class="col-xs-6">';
                 
                 $isChecked = isset($filterConfig[$key]) && $filterConfig[$key] === true;
                 $filterLabel = isset($filter['label']) ? $filter['label'] : ucfirst(str_replace('_', ' ', $key));
                 $filterDesc = isset($filter['description']) ? $filter['description'] : '';
                 
                 // ✅ Check if this is dynamic type filter
                 $isDynamicType = ($key === 'type' && $hasDynamicTypeOptions);
             ?>
                 <div class="checkbox" style="margin-bottom: 15px;">
                     <label>
                         <input type="checkbox" name="filter_<?php echo $key; ?>" 
                                class="filter-checkbox" data-key="<?php echo $key; ?>"
                                <?php echo $isChecked ? 'checked' : ''; ?>
                                <?php echo $isLegacy ? 'disabled' : ''; ?>>
                         <strong><?php echo htmlspecialchars($filterLabel); ?></strong>
                         <?php if ($isDynamicType): ?>
                         <?php endif; ?>
                     </label>
                     <br>
                     <small class="text-muted" style="margin-left: 20px;">
                         <?php echo htmlspecialchars($filterDesc); ?>
                         <?php if ($isDynamicType): ?>
                         <br><strong>Options:</strong> <?php echo implode(', ', $dynamicTypeOptions); ?>
                         <?php endif; ?>
                     </small>
                 </div>
             <?php 
                 $count++;
             endforeach; 
             ?>
             </div>
         </div>
     </div>

     <div class="alert alert-warning">
         <span class="glyphicon glyphicon-info-sign"></span>
         <strong>Catatan Penting:</strong>
         <ul style="margin: 5px 0 0 0;">
             <li><strong>Section (Department)</strong> - Untuk dokumen departemen (QC, Engineering, dll)</li>
             <li><strong>Section (Production)</strong> - Untuk dokumen production (Opto, Laser, dll)</li>
             <li><strong>Type</strong> - Filter dinamis, options berbeda untuk setiap document type</li>
         </ul>
     </div>

     <div class="form-group">
         <button type="submit" name="submit" class="btn btn-success btn-lg btn-block">
             <span class="glyphicon glyphicon-save"></span> Update Document Type
         </button>
         <a href="conf_document.php" class="btn btn-default btn-lg btn-block">
             <span class="glyphicon glyphicon-arrow-left"></span> Cancel
         </a>
     </div>
 </form>

</div>
</div>

<style>
.help-block {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}
</style>

<script>
$(document).ready(function(){
    var isLegacy = <?php echo $isLegacy ? 'true' : 'false'; ?>;
    
    if (!isLegacy) {
        // Dependency handling: Process requires Device
        $('input[data-key="process"]').change(function(){
            if($(this).is(':checked')) {
                $('input[data-key="device"]').prop('checked', true);
            }
        });

        $('input[data-key="device"]').change(function(){
            if(!$(this).is(':checked') && $('input[data-key="process"]').is(':checked')) {
                alert('Filter Process membutuhkan Filter Device. Filter Process akan dinonaktifkan.');
                $('input[data-key="process"]').prop('checked', false);
            }
        });

        // Warning jika memilih kedua section sekaligus
        $('input[data-key="section_dept"], input[data-key="section_prod"]').change(function(){
            var deptChecked = $('input[data-key="section_dept"]').is(':checked');
            var prodChecked = $('input[data-key="section_prod"]').is(':checked');
            
            if(deptChecked && prodChecked) {
                alert('Info: Anda memilih kedua jenis Section. Ini akan menampilkan 2 filter section di halaman dokumen.');
            }
        });
    }
});
</script>