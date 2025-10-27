<?php
// ===== PROSES POST DULU SEBELUM ADA OUTPUT APAPUN =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $jsonFile = __DIR__ . '/data/document_types.json';
    $types = json_decode(file_get_contents($jsonFile), true);
    
    $idx = intval($_POST['idx']);
    $submenuIdx = intval($_POST['submenu_idx']);
    $submenuName = trim($_POST['submenu_name']);
    
    // Ambil filter config dari checkbox yang di-submit
    $filterConfig = [];
    
    $availableFilterKeys = ['section_dept', 'section_prod', 'status', 'category', 'device', 'process'];
    
    foreach ($availableFilterKeys as $key) {
        $filterConfig[$key] = isset($_POST['filter_' . $key]) ? true : false;
    }
    
    if ($submenuName !== '') {
        // Check duplicate (kecuali dirinya sendiri)
        if (isset($types[$idx]['submenu']) && is_array($types[$idx]['submenu'])) {
            $tmp = $types[$idx]['submenu'];
            unset($tmp[$submenuIdx]);
            $existingNames = array_column($tmp, 'name');
            $lower = array_map('strtolower', $existingNames);
            
            if (in_array(strtolower($submenuName), $lower)) {
                header("Location: edit_submenu.php?idx=$idx&submenu_idx=$submenuIdx&error=duplicate");
                exit;
            }
        }
        
        // Update submenu
        $types[$idx]['submenu'][$submenuIdx]['name'] = $submenuName;
        $types[$idx]['submenu'][$submenuIdx]['id'] = strtolower(str_replace(' ', '_', $submenuName));
        $types[$idx]['submenu'][$submenuIdx]['filter_config'] = $filterConfig;
        
        @file_put_contents($jsonFile, json_encode(array_values($types), JSON_PRETTY_PRINT));
        
        header("Location: conf_document.php?success=submenu_updated");
        exit;
    } else {
        header("Location: edit_submenu.php?idx=$idx&submenu_idx=$submenuIdx&error=empty");
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
    // Fallback
    $availableFilters = [
        'section_dept' => [
            'label' => 'Section (Department)',
            'description' => 'Filter by department/administrative section'
        ],
        'section_prod' => [
            'label' => 'Section (Production)',
            'description' => 'Filter by production section/line'
        ],
        'status' => [
            'label' => 'Status',
            'description' => 'Document approval status'
        ],
        'category' => [
            'label' => 'Category',
            'description' => 'Internal or External document'
        ],
        'device' => [
            'label' => 'Device',
            'description' => 'Production device/equipment'
        ],
        'process' => [
            'label' => 'Process',
            'description' => 'Manufacturing process stage'
        ]
    ];
}

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

// Cek apakah ini legacy document
$legacyFile = isset($current['legacy_file']) ? $current['legacy_file'] : '';
$isLegacy = !empty($legacyFile);

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
    echo "<p style='margin-top:10px;'>URL yang benar harus seperti: <code>edit_submenu.php?idx=0&submenu_idx=0</code></p>";
    echo "<p>Parameter yang diterima: <code>" . http_build_query($_GET) . "</code></p>";
    echo "</div>";
    echo "<a href='conf_document.php' class='btn btn-default'>Kembali ke Configuration</a>";
    echo "</div>";
    exit;
}

if (!isset($current['submenu']) || !is_array($current['submenu'])) {
    echo "<br><br><div class='container'><div class='alert alert-danger'>";
    echo "<strong>Error!</strong> Document type <strong>" . htmlspecialchars($current['name']) . "</strong> tidak memiliki submenu.";
    echo "</div>";
    echo "<a href='edit_document.php?idx=$idx' class='btn btn-warning'>Enable Submenu</a> ";
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
        echo "<strong>Index $idx_sub:</strong> " . htmlspecialchars($sub['name']) . " ";
        echo "<a href='edit_submenu.php?idx=$idx&submenu_idx=$idx_sub' class='btn btn-xs btn-primary pull-right'>";
        echo "<span class='glyphicon glyphicon-edit'></span> Edit</a>";
        echo "</li>";
    }
    echo "</ul></div>";
    echo "<a href='conf_document.php' class='btn btn-default'>Kembali ke Configuration</a>";
    echo "</div>";
    exit;
}

$currentSubmenu = $current['submenu'][$submenuIdx];

// Pesan error dari redirect
$error = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'duplicate') {
        $error = "Submenu dengan nama tersebut sudah ada.";
    } elseif ($_GET['error'] == 'empty') {
        $error = "Silakan masukkan nama submenu.";
    }
}

// Get parent filter config untuk info
$parentFilterConfig = isset($current['filter_config']) ? $current['filter_config'] : [];

// Get submenu filter config (dengan default values)
$filterConfig = isset($currentSubmenu['filter_config']) ? $currentSubmenu['filter_config'] : [];

// Ensure all available filters are in config (default false if not exist)
foreach ($availableFilters as $key => $filter) {
    if (!isset($filterConfig[$key])) {
        $filterConfig[$key] = false;
    }
}
?>

<br /><br />
<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-6 well well-lg">
 <h2><span class="glyphicon glyphicon-edit"></span> Edit Submenu</h2>
 <p class="text-muted">
     Edit submenu untuk: <strong><?php echo htmlspecialchars($current['name']); ?></strong>
     <?php if ($isLegacy): ?>
         <span class="label label-warning">LEGACY</span>
     <?php endif; ?>
     <br>
     Submenu saat ini: <strong><?php echo htmlspecialchars($currentSubmenu['name']); ?></strong>
 </p>
 <hr>

 <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
    </div>
 <?php endif; ?>

 <div class="alert alert-info">
     <span class="glyphicon glyphicon-info-sign"></span>
     <strong>Info:</strong> Anda dapat mengubah nama submenu dan konfigurasi filter yang berbeda dari parent document type.
 </div>

 <?php if ($isLegacy): ?>
 <div class="alert alert-warning">
     <span class="glyphicon glyphicon-warning-sign"></span>
     <strong>Legacy Document Type!</strong><br>
     Filter configuration di bawah <strong>tidak akan berlaku</strong> karena dokumen ini menggunakan file legacy 
     (<code><?php echo htmlspecialchars($legacyFile); ?></code>).<br>
     Filter diatur langsung di dalam file legacy tersebut.
 </div>
 <?php endif; ?>

 <form action="" method="POST">
     <input type="hidden" name="idx" value="<?php echo $idx; ?>">
     <input type="hidden" name="submenu_idx" value="<?php echo $submenuIdx; ?>">
     
     <div class="form-group">
         <label><strong>Submenu Name</strong> <span class="text-danger">*</span></label>
         <input type="text" class="form-control input-lg" name="submenu_name" 
                value="<?php echo htmlspecialchars($currentSubmenu['name']); ?>"
                placeholder="e.g., Production, Other, General, etc." required>
         <p class="help-block">
             <span class="glyphicon glyphicon-info-sign"></span>
             Nama submenu yang akan ditampilkan di navigation (contoh: Production, Other)
         </p>
     </div>

     <hr>

     <h4>
         <span class="glyphicon glyphicon-filter"></span> Filter Configuration
         <?php if ($isLegacy): ?>
             <span class="label label-warning">Tidak Berlaku untuk Legacy</span>
         <?php endif; ?>
     </h4>
     <p class="text-muted">Pilih filter yang akan tersedia untuk submenu ini:</p>

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
                 $isCheckedParent = isset($parentFilterConfig[$key]) && $parentFilterConfig[$key] === true;
                 
                 $filterLabel = isset($filter['label']) ? $filter['label'] : ucfirst(str_replace('_', ' ', $key));
                 $filterDesc = isset($filter['description']) ? $filter['description'] : '';
             ?>
                 <div class="checkbox" style="margin-bottom: 15px;">
                     <label>
                         <input type="checkbox" name="filter_<?php echo $key; ?>" 
                                class="filter-checkbox" data-key="<?php echo $key; ?>"
                                <?php echo $isChecked ? 'checked' : ''; ?>
                                <?php echo $isLegacy ? 'disabled' : ''; ?>>
                         <strong><?php echo htmlspecialchars($filterLabel); ?></strong>
                         <?php if (!$isLegacy): ?>
                             <?php if ($isCheckedParent && !$isChecked): ?>
                             <?php elseif (!$isCheckedParent && $isChecked): ?>
                             <?php endif; ?>
                         <?php endif; ?>
                     </label>
                     <br>
                     <small class="text-muted" style="margin-left: 20px;">
                         <?php echo htmlspecialchars($filterDesc); ?>
                     </small>
                 </div>
             <?php 
                 $count++;
             endforeach; 
             ?>
             </div>
         </div>
     </div>

     <?php if (!$isLegacy): ?>
     <div class="alert alert-warning">
         <span class="glyphicon glyphicon-exclamation-sign"></span>
         <strong>Rekomendasi:</strong>
         <ul style="margin: 5px 0 0 0;">
             <li>Untuk submenu <strong>Production</strong> → Gunakan filter: Section (Production), Device, Process</li>
             <li>Untuk submenu <strong>Other/General</strong> → Gunakan filter: Section (Department), Status, Category</li>
         </ul>
     </div>
     <?php endif; ?>

     <div class="form-group">
         <button type="submit" name="submit" class="btn btn-success btn-lg btn-block">
             <span class="glyphicon glyphicon-save"></span> Update Submenu
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