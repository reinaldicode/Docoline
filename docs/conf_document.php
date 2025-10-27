<?php
include('header.php');
include('config_head.php');
include 'koneksi.php';

// path ke json
$jsonFile = __DIR__ . '/data/document_types.json';

// pastikan file ada dengan struktur baru
if (!file_exists($jsonFile)) {
    $defaultData = [
        [
            'id' => 'procedure',
            'name' => 'Procedure',
            'has_submenu' => false,
            'use_custom_file' => false,
            'custom_file' => '',
            'submenu' => [],
            'filter_config' => [
                'section' => true,
                'device' => false,
                'process' => false,
                'status' => true,
                'category' => false
            ]
        ]
    ];
    file_put_contents($jsonFile, json_encode($defaultData, JSON_PRETTY_PRINT));
}

$types = json_decode(file_get_contents($jsonFile), true);

// Tampilkan pesan sukses jika ada
$successMsg = '';
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'updated') $successMsg = "Document type berhasil diupdate!";
    if ($_GET['success'] == 'deleted') $successMsg = "Document type berhasil dihapus!";
    if ($_GET['success'] == 'submenu_added') $successMsg = "Submenu berhasil ditambahkan!";
    if ($_GET['success'] == 'submenu_deleted') $successMsg = "Submenu berhasil dihapus!";
    if ($_GET['success'] == 'added') $successMsg = "Document type berhasil ditambahkan!";
}

// Helper function untuk menampilkan filter config (simplified)
function displayFilterConfig($config) {
    if (empty($config)) return '<span class="text-muted">No filters</span>';
    
    $filters = [];
    if (!empty($config['section'])) $filters[] = 'Section';
    if (!empty($config['device'])) $filters[] = 'Device';
    if (!empty($config['process'])) $filters[] = 'Process';
    if (!empty($config['status'])) $filters[] = 'Status';
    if (!empty($config['category'])) $filters[] = 'Category';
    
    return empty($filters) ? '<span class="text-muted">No filters</span>' : '<small>' . implode(', ', $filters) . '</small>';
}
?>

<br /><br />

<?php if ($successMsg): ?>
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <strong>Success!</strong> <?php echo htmlspecialchars($successMsg); ?>
</div>

<?php endif; ?>

<h2><span class="glyphicon glyphicon-cog"></span> Manage Document Types</h2>
<p class="text-muted">Kelola tipe dokumen dan konfigurasi filter untuk sistem document control</p>
<br />
<a href="add_document.php" class="btn btn-primary btn-lg">
    <span class="glyphicon glyphicon-plus"></span> Add Document Type
</a>
<br /><br />

<table class="table table-hover table-bordered">
<thead style="background-color:#00FFFF;">
<tr>
    <th width="5%">No</th>
    <th width="20%">Document Type</th>
    <th width="12%">Submenu</th>
    <th width="15%">Filters</th>
    <th width="33%">Submenu List</th>
    <th width="15%">Action</th>
</tr>
</thead>
<tbody>
<?php
$i = 1;
if (is_array($types)) {
    foreach ($types as $idx => $t) {
        if (!is_array($t) || !isset($t['name'])) continue;
        
        $hasSubmenu = isset($t['has_submenu']) && $t['has_submenu'] === true;
        $submenu = isset($t['submenu']) && is_array($t['submenu']) ? $t['submenu'] : [];
        $submenuCount = $hasSubmenu && count($submenu) > 0 ? count($submenu) : 0;
        $filterConfig = isset($t['filter_config']) ? $t['filter_config'] : [];
        
        // Check if legacy
        $legacyFile = isset($t['legacy_file']) ? $t['legacy_file'] : '';
        $isLegacy = !empty($legacyFile);
        
        echo "<tr>";
        echo "<td class='text-center'><strong>{$i}</strong></td>";
        
        // Document Type Name
        echo "<td>";
        echo "<strong style='font-size:14px;'>" . htmlspecialchars($t['name']) . "</strong>";
        if ($isLegacy) {
            echo "<br><span class='label label-warning' style='font-size:10px;'>Legacy File</span>";
        }
        echo "</td>";
        
        // Has Submenu
        echo "<td class='text-center'>";
        if ($hasSubmenu && $submenuCount > 0) {
            echo '<span class="label label-success" style="font-size:11px;">Yes (' . $submenuCount . ')</span>';
        } else {
            echo '<span class="label label-default" style="font-size:11px;">No</span>';
        }
        echo "</td>";
        
        // Filter Config
        echo "<td>" . displayFilterConfig($filterConfig) . "</td>";
        
        // Kolom Submenus (SIMPLIFIED & CLEAN)
        echo "<td>";
        
        if ($hasSubmenu && $submenuCount > 0) {
            echo "<div style='font-size:12px;'>";
            foreach ($submenu as $subIdx => $sub) {
                if (!is_array($sub) || !isset($sub['name'])) continue;
                
                $subLegacyFile = isset($sub['legacy_file']) ? $sub['legacy_file'] : '';
                $isSubLegacy = !empty($subLegacyFile);
                
                // Nama submenu dengan icon
                echo "<div style='margin-bottom:6px; padding:4px; background:#f9f9f9; border-left:3px solid #5bc0de;'>";
                echo "<strong>" . htmlspecialchars($sub['name']) . "</strong> ";
                
                // Legacy badge (jika ada)
                if ($isSubLegacy) {
                    echo '<span class="label label-warning" style="font-size:9px;">Legacy</span> ';
                }
                
                // Auto-filter badge
                $subNameLower = strtolower($sub['name']);
                if (strpos($subNameLower, 'production') !== false) {
                    echo '<span class="label label-success" style="font-size:9px;">Auto: Production</span> ';
                } elseif (strpos($subNameLower, 'other') !== false) {
                    echo '<span class="label label-info" style="font-size:9px;">Auto: Other</span> ';
                }
                
                // Action buttons (INLINE & COMPACT)
                echo "<div style='margin-top:4px;'>";
                echo "<a href='edit_submenu.php?idx=" . intval($idx) . "&subidx=" . intval($subIdx) . "' class='btn btn-xs btn-primary' title='Edit Submenu' style='margin-right:3px;'>
                      <span class='glyphicon glyphicon-edit'></span> Edit</a>";
                echo "<a href='del_submenu.php?idx=" . intval($idx) . "&subidx=" . intval($subIdx) . "' class='btn btn-xs btn-danger' onclick=\"return confirm('Delete submenu " . addslashes($sub['name']) . "?')\" title='Delete Submenu'>
                      <span class='glyphicon glyphicon-trash'></span> Delete</a>";
                echo "</div>";
                
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<span class='text-muted'><em>No submenu</em></span>";
        }
        
        echo "</td>";
        
        // Action buttons (PARENT DOCUMENT TYPE)
        echo "<td>";
        echo "<a href='edit_document.php?idx={$idx}' class='btn btn-primary btn-sm btn-block' style='margin-bottom:4px;'>
                <span class='glyphicon glyphicon-edit'></span> Edit
              </a>";
        echo "<a href='add_submenu.php?idx={$idx}' class='btn btn-success btn-sm btn-block' style='margin-bottom:4px;'>
                <span class='glyphicon glyphicon-plus'></span> Add Submenu
              </a>";
        echo "<a href='del_document.php?idx={$idx}' class='btn btn-danger btn-sm btn-block' onclick=\"return confirm('Delete " . addslashes($t['name']) . " and all submenus?')\">
                <span class='glyphicon glyphicon-remove'></span> Delete
              </a>";
        echo "</td>";
        
        echo "</tr>";
        $i++;
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No document types found</td></tr>";
}
?>
</tbody>
</table>

<div class="panel panel-info">
    <div class="panel-heading">
        <h4 class="panel-title"><span class="glyphicon glyphicon-info-sign"></span> Information</h4>
    </div>
    <div class="panel-body">
        <ul style="margin-bottom:0;">
            <li><strong>Document Type:</strong> Kategori utama dokumen (contoh: Procedure, WI, Form)</li>
            <li><strong>Submenu:</strong> Sub-kategori dalam document type (contoh: WI Production, WI Other)</li>
            <li><strong>Filters:</strong> Dropdown filter yang tersedia untuk memfilter dokumen</li>
            <li><strong>Auto-Filter Production/Other:</strong>
                <ul>
                    <li><em>Production</em> = menampilkan dokumen dengan device production</li>
                    <li><em>Other</em> = menampilkan dokumen tanpa device spesifik</li>
                </ul>
            </li>
        </ul>
    </div>
</div>

<style>
.table > thead > tr > th {
    vertical-align: middle;
    font-weight: bold;
}
.table > tbody > tr > td {
    vertical-align: top;
}
</style>