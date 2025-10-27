<?php
// ===== PROSES POST DULU =====
// ini add_document.php - FIXED VERSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $jsonFile = __DIR__ . '/data/document_types.json';
    $types = json_decode(file_get_contents($jsonFile), true);
    
    if (!is_array($types)) {
        $types = [];
    }
    
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
        // Check duplicate
        $existingNames = array_column($types, 'name');
        $lower = array_map('strtolower', $existingNames);
        
        if (in_array(strtolower($name), $lower)) {
            header("Location: add_document.php?error=duplicate");
            exit;
        }
        
        // Generate ID dari nama
        $id = strtolower(str_replace(' ', '_', $name));
        
        // Create new document type
        $newType = [
            'id' => $id,
            'name' => $name,
            'has_submenu' => $hasSubmenu,
            'submenu' => [],
            'filter_config' => $filterConfig,
            'use_custom_file' => false,
            'custom_file' => ''
        ];
        
        // Add to array
        $types[] = $newType;
        
        // Save
        if (file_put_contents($jsonFile, json_encode($types, JSON_PRETTY_PRINT))) {
            header("Location: conf_document.php?success=added");
            exit;
        } else {
            header("Location: add_document.php?error=save_failed");
            exit;
        }
    } else {
        header("Location: add_document.php?error=empty");
        exit;
    }
}

// ===== LOAD HEADER =====
include('header.php');
include('config_head.php');

// Load filter options from JSON
$filterOptionsFile = __DIR__ . '/data/filter_options.json';
$availableFilters = [];

if (file_exists($filterOptionsFile)) {
    $availableFilters = json_decode(file_get_contents($filterOptionsFile), true);
} else {
    echo "<div class='alert alert-danger'>Error: filter_options.json not found!</div>";
    exit;
}

// Pesan error
$error = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'duplicate') {
        $error = "Document type dengan nama tersebut sudah ada.";
    } elseif ($_GET['error'] == 'empty') {
        $error = "Silakan masukkan nama document type.";
    } elseif ($_GET['error'] == 'save_failed') {
        $error = "Gagal menyimpan ke file JSON. Periksa permission folder.";
    }
}
?>

<br /><br />
<div class="row">
    <div class="col-xs-1"></div>
    <div class="col-xs-6 well well-lg">
        <h2><span class="glyphicon glyphicon-plus-sign"></span> Add New Document Type</h2>
        <p class="text-muted">Tambahkan tipe dokumen baru ke sistem</p>
        <hr>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label><strong>Document Type Name</strong> <span class="text-danger">*</span></label>
                <input type="text" class="form-control input-lg" name="name" required 
                       placeholder="Contoh: Quality Report, Training Record, dll">
                <p class="help-block">
                    <span class="glyphicon glyphicon-info-sign"></span> 
                    Nama tipe dokumen yang akan ditampilkan di menu navigation
                </p>
            </div>
            
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="has_submenu" id="has_submenu"> 
                        <strong>Dokumen ini memiliki Sub-Menu</strong>
                    </label>
                </div>
                <p class="help-block">
                    <span class="glyphicon glyphicon-info-sign"></span> 
                    Centang jika dokumen ini akan memiliki kategori sub-menu (misalnya: Production, Other, dll)
                </p>
            </div>

            <div class="alert alert-info" id="submenu_info" style="display:none;">
                <span class="glyphicon glyphicon-info-sign"></span> 
                <strong>Info:</strong> Setelah document type dibuat, Anda bisa menambahkan submenu melalui tombol <strong>"Add Submenu"</strong> di halaman config.
            </div>

            <hr>

            <h4>
                <span class="glyphicon glyphicon-filter"></span> Filter Configuration
            </h4>
            <p class="text-muted">Pilih filter yang akan tersedia di halaman document list:</p>

            <div class="well">
                <div class="row">
                    <?php 
                    $count = 0;
                    $totalFilters = count($availableFilters);
                    $halfPoint = ceil($totalFilters / 2);
                    
                    foreach ($availableFilters as $key => $filter): 
                        if ($count == 0) echo '<div class="col-xs-6">';
                        if ($count == $halfPoint) echo '</div><div class="col-xs-6">';
                        
                        // Default checked untuk section_dept & status
                        $defaultChecked = in_array($key, ['section_dept', 'status']) ? 'checked' : '';
                        
                        // Get label dan description
                        $filterLabel = isset($filter['label']) ? $filter['label'] : ucfirst(str_replace('_', ' ', $key));
                        $filterDesc = isset($filter['description']) ? $filter['description'] : '';
                        
                        // Check if dynamic
                        $isDynamic = isset($filter['dynamic']) && $filter['dynamic'] === true;
                    ?>
                        <div class="checkbox" style="margin-bottom: 15px;">
                            <label>
                                <input type="checkbox" name="filter_<?php echo $key; ?>" 
                                       class="filter-checkbox" data-key="<?php echo $key; ?>"
                                       <?php echo $defaultChecked; ?>>
                                <strong><?php echo htmlspecialchars($filterLabel); ?></strong>
                                <?php if ($isDynamic): ?>
                                <?php endif; ?>
                            </label>
                            <br>
                            <small class="text-muted" style="margin-left: 20px;">
                                <?php echo htmlspecialchars($filterDesc); ?>
                                <?php if ($isDynamic && isset($filter['options_by_doctype'])): ?>
                                <br><em>Options berbeda untuk setiap document type</em>
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
                    <li><strong>Type</strong> - Filter dinamis yang options-nya berbeda tergantung document type</li>
                </ul>
            </div>

            <div class="form-group">
                <button type="submit" name="submit" class="btn btn-success btn-lg btn-block">
                    <span class="glyphicon glyphicon-save"></span> Add Document Type
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
    margin-bottom: 0;
}
</style>

<script>
$(document).ready(function(){
    // Show submenu info
    $('#has_submenu').change(function(){
        if($(this).is(':checked')) {
            $('#submenu_info').slideDown();
        } else {
            $('#submenu_info').slideUp();
        }
    });

    // Dependency handling
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