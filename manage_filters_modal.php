<?php
// Pure AJAX handler - no HTML output
header('Content-Type: application/json');

$jsonFile = __DIR__ . '/data/filter_options.json';
$filters = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

if (!is_array($filters)) {
    $filters = [];
}

$action = $_POST['action'] ?? '';

// Get all filters
if ($action === 'get_filters') {
    echo json_encode(['success' => true, 'data' => $filters]);
    exit;
}

// Add new filter
if ($action === 'add_filter') {
    $key = strtolower(trim($_POST['key'] ?? ''));
    $label = trim($_POST['label'] ?? '');
    
    if (empty($key) || empty($label)) {
        echo json_encode(['success' => false, 'message' => 'Key dan Label tidak boleh kosong']);
        exit;
    }
    
    if (isset($filters[$key])) {
        echo json_encode(['success' => false, 'message' => 'Filter dengan key tersebut sudah ada']);
        exit;
    }
    
    $filters[$key] = ['label' => $label, 'options' => []];
    file_put_contents($jsonFile, json_encode($filters, JSON_PRETTY_PRINT));
    
    echo json_encode(['success' => true, 'message' => 'Filter berhasil ditambahkan', 'data' => $filters]);
    exit;
}

// Add option to filter
if ($action === 'add_option') {
    $key = $_POST['key'] ?? '';
    $option = trim($_POST['option'] ?? '');
    
    if (empty($option)) {
        echo json_encode(['success' => false, 'message' => 'Opsi tidak boleh kosong']);
        exit;
    }
    
    if (!isset($filters[$key])) {
        echo json_encode(['success' => false, 'message' => 'Filter tidak ditemukan']);
        exit;
    }
    
    if (in_array($option, $filters[$key]['options'])) {
        echo json_encode(['success' => false, 'message' => 'Opsi sudah ada']);
        exit;
    }
    
    $filters[$key]['options'][] = $option;
    file_put_contents($jsonFile, json_encode($filters, JSON_PRETTY_PRINT));
    
    echo json_encode(['success' => true, 'message' => 'Opsi berhasil ditambahkan', 'data' => $filters[$key]]);
    exit;
}

// Delete option
if ($action === 'delete_option') {
    $key = $_POST['key'] ?? '';
    $option = $_POST['option'] ?? '';
    
    if (isset($filters[$key])) {
        $index = array_search($option, $filters[$key]['options']);
        if ($index !== false) {
            array_splice($filters[$key]['options'], $index, 1);
            file_put_contents($jsonFile, json_encode($filters, JSON_PRETTY_PRINT));
            
            echo json_encode(['success' => true, 'message' => 'Opsi berhasil dihapus', 'data' => $filters[$key]]);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus opsi']);
    exit;
}

// Delete filter
if ($action === 'delete_filter') {
    $key = $_POST['key'] ?? '';
    
    if (isset($filters[$key])) {
        unset($filters[$key]);
        file_put_contents($jsonFile, json_encode($filters, JSON_PRETTY_PRINT));
        
        echo json_encode(['success' => true, 'message' => 'Filter berhasil dihapus', 'data' => $filters]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Filter tidak ditemukan']);
    exit;
}

// Update label
if ($action === 'update_label') {
    $key = $_POST['key'] ?? '';
    $label = trim($_POST['label'] ?? '');
    
    if (empty($label)) {
        echo json_encode(['success' => false, 'message' => 'Label tidak boleh kosong']);
        exit;
    }
    
    if (isset($filters[$key])) {
        $filters[$key]['label'] = $label;
        file_put_contents($jsonFile, json_encode($filters, JSON_PRETTY_PRINT));
        
        echo json_encode(['success' => true, 'message' => 'Label berhasil diupdate', 'data' => $filters[$key]]);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Filter tidak ditemukan']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
exit;