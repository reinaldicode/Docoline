<?php
// ===== PROSES POST DULU SEBELUM ADA OUTPUT APAPUN =====
// ini add_submenu.php - FIXED VERSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $jsonFile = __DIR__ . '/data/document_types.json';
    $types = json_decode(file_get_contents($jsonFile), true);
    
    $idx = intval($_POST['idx']);
    $submenuName = trim($_POST['submenu_name']);
    
    // Ambil filter config dari checkbox yang di-submit
    $filterConfig = [];
    
    // ✅ FIXED: Ambil semua filter keys dari filter_options.json
    $filterOptionsFile = __DIR__ . '/data/filter_options.json';
    $availableFilters = json_decode(file_get_contents($filterOptionsFile), true);
    $availableFilterKeys = array_keys($availableFilters);
    
    foreach ($availableFilterKeys as $key) {
        $filterConfig[$key] = isset($_POST['filter_' . $key]) ? true : false;
    }
    
    if ($submenuName !== '') {
        // Check duplicate submenu name
        if (isset($types[$idx]['submenu']) && is_array($types[$idx]['submenu'])) {
            $existingNames = array_column($types[$idx]['submenu'], 'name');
            $lower = array_map('strtolower', $existingNames);
            
            if (in_array(strtolower($submenuName), $lower)) {
                header("Location: add_submenu.php?idx=$idx&error=duplicate");
                exit;
            }
        } else {
            // Inisialisasi submenu array jika belum ada
            $types[$idx]['submenu'] = [];
        }
        
        // Tambahkan submenu baru
        $newSubmenu = [
            'id' => strtolower(str_replace(' ', '_', $submenuName)),
            'name' => $submenuName,
            'filter_config' => $filterConfig
        ];
        
        $types[$idx]['submenu'][] = $newSubmenu;
        
        @file_put_contents($jsonFile, json_encode(array_values($types), JSON_PRETTY_PRINT));
        
        header("Location: conf_document.php?success=submenu_added");
        exit;
    } else {
        header("Location: add_submenu.php?idx=$idx&error=empty");
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

// Periksa apakah document type ini support submenu
if (!isset($current['has_submenu']) || $current['has_submenu'] !== true) {
    echo "<div class='alert alert-danger'>Document type ini tidak support submenu. Silakan enable 'Has Submenu' di Edit Document Type.</div>";
    echo "<a href='edit_document.php?idx=$idx' class='btn btn-primary'>Edit Document Type</a> ";
    echo "<a href='conf_document.php' class='btn btn-default'>Kembali</a>";
    exit;
}

// Pesan error dari redirect
$error = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'duplicate') {
        $error = "Submenu dengan nama tersebut sudah ada.";
    } elseif ($_GET['error'] == 'empty') {
        $error = "Silakan masukkan nama submenu.";
    }
}

// Get parent filter config untuk default values
$parentFilterConfig = isset($current['filter_config']) ? $current['filter_config'] : [];

// ✅ NEW: Check if parent document type has dynamic type options
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
 <h2><span class="glyphicon glyphicon-plus"></span> Add Submenu</h2>
 <p class="text-muted">Tambah submenu untuk: <strong><?php echo htmlspecialchars($current['name']); ?></strong></p>
 <hr>

 <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
    </div>
 <?php endif; ?>

 <div class="alert alert-info">
     <span class="glyphicon glyphicon-info-sign"></span>
     <strong>Info:</strong> Submenu akan menggunakan filter dari parent document type sebagai default, tetapi Anda bisa mengubahnya sesuai kebutuhan.
 </div>

 <form action="" method="POST">
     <input type="hidden" name="idx" value="<?php echo $idx; ?>">
     
     <div class="form-group">
         <label><strong>Submenu Name</strong> <span class="text-danger">*</span></label>
         <input type="text" class="form-control input-lg" name="submenu_name" 
                placeholder="e.g., Production, Other, General, etc." required>
         <p class="help-block">
             <span class="glyphicon glyphicon-info-sign"></span>
             Nama submenu yang akan ditampilkan di navigation (contoh: Production, Other)
         </p>
     </div>

     <hr>

     <h4>
         <span class="glyphicon glyphicon-filter"></span> Filter Configuration
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
                 
                 // Default checked jika parent juga menggunakan filter ini
                 $isCheckedParent = isset($parentFilterConfig[$key]) && $parentFilterConfig[$key] === true;
                 
                 $filterLabel = isset($filter['label']) ? $filter['label'] : ucfirst(str_replace('_', ' ', $key));
                 $filterDesc = isset($filter['description']) ? $filter['description'] : '';
                 
                 // ✅ Check if this is dynamic type filter
                 $isDynamicType = ($key === 'type' && $hasDynamicTypeOptions);
             ?>
                 <div class="checkbox" style="margin-bottom: 15px;">
                     <label>
                         <input type="checkbox" name="filter_<?php echo $key; ?>" 
                                class="filter-checkbox" data-key="<?php echo $key; ?>"
                                <?php echo $isCheckedParent ? 'checked' : ''; ?>>
                         <strong><?php echo htmlspecialchars($filterLabel); ?></strong>
                         <?php if ($isDynamicType): ?>
                         <?php endif; ?>
                     </label>
                     <br>
                     <small class="text-muted" style="margin-left: 20px;">
                         <?php echo htmlspecialchars($filterDesc); ?>
                         <?php if ($isDynamicType): ?>
                         <br><strong>Options untuk <?php echo htmlspecialchars($current['name']); ?>:</strong> <?php echo implode(', ', $dynamicTypeOptions); ?>
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
         <span class="glyphicon glyphicon-exclamation-sign"></span>
         <strong>Rekomendasi:</strong>
         <ul style="margin: 5px 0 0 0;">
             <li>Untuk submenu <strong>Production</strong> → Gunakan filter: Section (Production), Device, Process</li>
             <li>Untuk submenu <strong>Other/General</strong> → Gunakan filter: Section (Department), Status, Category</li>
             <?php if ($hasDynamicTypeOptions): ?>
             <li>Filter <strong>Type</strong> tersedia dengan options: <?php echo implode(', ', $dynamicTypeOptions); ?></li>
             <?php endif; ?>
         </ul>
     </div>

     <div class="form-group">
         <button type="submit" name="submit" class="btn btn-success btn-lg btn-block">
             <span class="glyphicon glyphicon-plus"></span> Add Submenu
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
});
</script>