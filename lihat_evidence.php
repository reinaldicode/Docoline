<?php
// lihat_evidence.php - Public Access (NO LOGIN REQUIRED)
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ TAMBAHAN: Refresh session untuk user yang login
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    $_SESSION['last_activity'] = time();
}

if (!file_exists('koneksi.php')) { 
    die("koneksi.php tidak ditemukan."); 
}
include 'koneksi.php';

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));

// ===== PARAMETER =====
// ✅ FIX: Improved return_url handling dengan fallback ke HTTP_REFERER
$return_url = '';
if (isset($_GET['return_url']) && !empty($_GET['return_url'])) {
    $return_url = $_GET['return_url'];
} elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    // Fallback ke HTTP_REFERER jika return_url tidak ada
    $referer = $_SERVER['HTTP_REFERER'];
    $parsed = parse_url($referer);
    $currentHost = $_SERVER['HTTP_HOST'];
    
    // Pastikan referer dari domain yang sama
    if (isset($parsed['host']) && $parsed['host'] === $currentHost) {
        $return_url = $referer;
    } else {
        $return_url = 'search_awal.php';
    }
} else {
    $return_url = 'search_awal.php';
}

$drf = isset($_GET['drf']) ? mysqli_real_escape_string($link, $_GET['drf']) : '';

// HANDLE AJAX DELETE REQUEST (HANYA UNTUK USER YANG LOGIN)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_delete']) && $_POST['ajax_delete'] === '1') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    $isLoggedIn = (isset($_SESSION['state']) && !empty($_SESSION['state'])) || (isset($_SESSION['nrp']) && !empty($_SESSION['nrp']));
    
    if (!$isLoggedIn) {
        $response['message'] = 'Anda harus login untuk menghapus file.';
        echo json_encode($response);
        exit;
    }
    
    $csrf_post = $_POST['csrf_token'] ?? '';
    $drf_post = $_POST['drf'] ?? '';
    
    if ($csrf_post === '' || $csrf_post !== ($_SESSION['csrf_token'] ?? '')) {
        $response['message'] = 'Token invalid. Aksi dibatalkan.';
        echo json_encode($response);
        exit;
    }
    
    if (empty($drf_post)) {
        $response['message'] = 'DRF tidak ditemukan.';
        echo json_encode($response);
        exit;
    }
    
    $drf_db = mysqli_real_escape_string($link, $drf_post);
    $uploadDir = __DIR__ . '/evidence/';
    
    $q = mysqli_query($link, "SELECT sos_file, sos_uploaded_by FROM docu WHERE no_drf = '$drf_db' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $row_del = mysqli_fetch_assoc($q);
        $file = $row_del['sos_file'];
        $uploaded_by = $row_del['sos_uploaded_by'];

        $user_state_check = $_SESSION['state'] ?? 'User';
        $user_nrp_check = $_SESSION['nrp'] ?? '';

        $allowed_to_delete = false;
        if ($user_state_check === 'Admin') {
            $allowed_to_delete = true;
        } elseif ($user_state_check === 'Originator' && !empty($user_nrp_check) && $user_nrp_check === $uploaded_by) {
            $allowed_to_delete = true;
        }

        if (!$allowed_to_delete) {
            $response['message'] = 'Anda tidak mempunyai izin untuk menghapus file ini.';
        } else {
            $fullpath = $uploadDir . $file;
            $deleted = false;
            if (file_exists($fullpath)) {
                $deleted = @unlink($fullpath);
            } else {
                $deleted = true;
            }

            if ($deleted) {
                $delQ = mysqli_query($link, "UPDATE docu SET sos_file = NULL, sos_uploaded_by = NULL, sos_upload_date = NULL, sos_notes = NULL WHERE no_drf = '$drf_db' LIMIT 1");
                if ($delQ) {
                    $response['success'] = true;
                    $response['message'] = 'File dan record evidence berhasil dihapus.';
                } else {
                    $response['message'] = 'File dihapus, tapi gagal update database.';
                }
            } else {
                $response['message'] = 'Gagal menghapus file dari server.';
            }
        }
    } else {
        $response['message'] = 'Record tidak ditemukan.';
    }
    
    echo json_encode($response);
    exit;
}

// Validasi DRF
if (empty($drf)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Error - Evidence</title>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body style="padding: 50px;">
        <div class="alert alert-danger">Parameter DRF tidak ditemukan.</div>
        <a href="search_awal.php" class="btn btn-default">Kembali ke Search</a>
    </body>
    </html>
    <?php
    exit;
}

// ===== CEK LOGIN STATUS =====
$isLoggedIn = false;
$user_state = 'Guest';
$user_nrp = '';
$isAdmin = false;
$isOriginator = false;

if ((isset($_SESSION['state']) && !empty($_SESSION['state'])) || 
    (isset($_SESSION['nrp']) && !empty($_SESSION['nrp']))) {
    $isLoggedIn = true;
    $user_state = $_SESSION['state'] ?? 'User';
    $user_nrp = $_SESSION['nrp'] ?? '';
    $isAdmin = ($user_state === 'Admin');
    $isOriginator = ($user_state === 'Originator');
}

$webUploadDir = 'evidence/';

// CSRF token (hanya untuk user yang login)
if ($isLoggedIn && empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $isLoggedIn ? ($_SESSION['csrf_token'] ?? '') : '';

// Ambil data docu
$q = mysqli_query($link, "SELECT no_doc, sos_file, sos_uploaded_by, sos_upload_date, sos_notes FROM docu WHERE no_drf = '$drf' LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Error - Evidence</title>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body style="padding: 50px;">
        <div class="alert alert-danger">Dokumen tidak ditemukan.</div>
        <a href="<?php echo htmlspecialchars($return_url); ?>" class="btn btn-default">Kembali</a>
    </body>
    </html>
    <?php
    exit;
}
$row = mysqli_fetch_assoc($q);
$has_sos = !empty($row['sos_file']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Evidence - DRF <?php echo htmlspecialchars($drf); ?></title>
    
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    
    <style>
    body {
        background-image: url("images/white.jpeg");
        background-color: #cccccc;
        background-repeat: no-repeat;
        background-attachment: fixed;
        padding-top: 50px;
    }
    .container-main {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .login-status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }
    .login-status-badge.guest {
        background-color: #e5e7eb;
        color: #6b7280;
    }
    .login-status-badge.logged-in {
        background-color: #dcfce7;
        color: #16a34a;
    }
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    }
    .notification {
        background: white;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideIn 0.3s ease-out;
    }
    .notification.error { border-left: 4px solid #ef4444; }
    .notification.success { border-left: 4px solid #22c55e; }
    .notification-icon { flex-shrink: 0; width: 24px; height: 24px; }
    .notification-icon.error { color: #ef4444; }
    .notification-icon.success { color: #22c55e; }
    .notification-content { flex: 1; }
    .notification-title { font-weight: 600; margin-bottom: 4px; font-size: 14px; }
    .notification-message { font-size: 13px; color: #666; line-height: 1.4; }
    .notification-close {
        flex-shrink: 0;
        background: none;
        border: none;
        color: #999;
        cursor: pointer;
        font-size: 20px;
        padding: 0;
        width: 24px;
        height: 24px;
    }
    .notification-close:hover { color: #333; }
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
    .notification.hiding { animation: slideOut 0.3s ease-out forwards; }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .glyphicon-refresh-animate { animation: spin 1s linear infinite; }
    </style>
</head>
<body>

<div class="notification-container" id="notificationContainer"></div>

<div class="container-main">
    <h3>
        Evidence untuk DRF: <strong><?php echo htmlspecialchars($drf); ?></strong>
        <?php if ($isLoggedIn): ?>
            <span class="login-status-badge logged-in">
                <span class="glyphicon glyphicon-user"></span> <?php echo htmlspecialchars($user_state); ?>
            </span>
        <?php else: ?>
            <span class="login-status-badge guest">
                <span class="glyphicon glyphicon-eye-open"></span> Public View
            </span>
        <?php endif; ?>
    </h3>

    <p>
        <a href="<?php echo htmlspecialchars($return_url); ?>" class="btn btn-primary">
            <span class="glyphicon glyphicon-arrow-left"></span>&nbsp;Back
        </a>
    </p>

    <?php if ($has_sos): 
        $fileUrl = $webUploadDir . rawurlencode($row['sos_file']);
    ?>
        <table class="table table-hover table-bordered">
            <tr><th width="200">No. Document</th><td><?php echo htmlspecialchars($row['no_doc']); ?></td></tr>
            <tr><th>Uploaded by</th><td><?php echo htmlspecialchars($row['sos_uploaded_by']); ?></td></tr>
            <tr><th>Upload date</th><td><?php echo htmlspecialchars($row['sos_upload_date']); ?></td></tr>
            <tr><th>File</th>
                <td>
                    <a target="_blank" href="<?php echo $fileUrl;?>">
                        <span class="glyphicon glyphicon-file"></span> <?php echo htmlspecialchars($row['sos_file']); ?>
                    </a>
                </td>
            </tr>
            <tr><th>Notes</th><td><?php echo nl2br(htmlspecialchars($row['sos_notes'])); ?></td></tr>
            <tr><th>Actions</th>
                <td>
                    <a class="btn btn-xs btn-info" target="_blank" href="<?php echo $fileUrl;?>" title="View">
                        <span class="glyphicon glyphicon-eye-open"></span> View
                    </a>
                    <a class="btn btn-xs btn-success" href="<?php echo $fileUrl;?>" download="<?php echo htmlspecialchars($row['sos_file']); ?>" title="Download">
                        <span class="glyphicon glyphicon-download"></span> Download
                    </a>

                    <?php if ($isLoggedIn && ($isAdmin || ($isOriginator && !empty($user_nrp) && $user_nrp === $row['sos_uploaded_by']))): ?>
                        <button class="btn btn-xs btn-danger" onclick="deleteFile()" title="Hapus">
                            <span class="glyphicon glyphicon-trash"></span> Hapus
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            <span class="glyphicon glyphicon-info-sign"></span> 
            Belum ada Evidence untuk DRF ini.
        </div>
    <?php endif; ?>

    <hr />
    
    <?php if (!$isLoggedIn): ?>
        <div class="alert alert-warning">
            <span class="glyphicon glyphicon-lock"></span> 
            <strong>Info:</strong> Anda sedang melihat dalam mode <strong>Public View</strong>. 
            Untuk mengupload atau mengganti evidence, silakan <a href="login.php" class="alert-link"><strong>Login terlebih dahulu</strong></a>.
        </div>
    <?php elseif ($isAdmin || $isOriginator): ?>
        <h4>Replace Evidence</h4>
        
        <form id="uploadForm" onsubmit="return false;">
            <input type="hidden" name="drf" value="<?php echo htmlspecialchars($drf); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
            <input type="hidden" name="return_url" value="<?php echo htmlspecialchars($return_url); ?>">
            <div class="form-group">
                <label>Pilih file (pdf/jpg/jpeg/png). Max 10MB.</label>
                <input type="file" name="sos_file" id="sos_file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <div class="form-group">
                <label>Catatan / Keterangan (optional)</label>
                <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
            </div>
            <button class="btn btn-primary" type="button" onclick="uploadFile()">
                <span class="glyphicon glyphicon-upload"></span> Upload Evidence
            </button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">
            <span class="glyphicon glyphicon-info-sign"></span> 
            <strong>Info:</strong> Hanya <strong>Admin</strong> dan <strong>Originator</strong> yang dapat mengupload atau mengganti evidence.
        </div>
    <?php endif; ?>

</div>

<script src="bootstrap/js/jquery.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script>
function showNotification(type, message) {
    const container = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    
    const icon = type === 'error' 
        ? '<svg class="notification-icon error" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
        : '<svg class="notification-icon success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    
    const title = type === 'error' ? 'Error' : 'Berhasil';
    
    notification.innerHTML = `
        ${icon}
        <div class="notification-content">
            <div class="notification-title">${title}</div>
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" onclick="closeNotification(this)">&times;</button>
    `;
    
    container.appendChild(notification);
    setTimeout(() => closeNotification(notification.querySelector('.notification-close')), 5000);
}

function closeNotification(button) {
    const notification = button.closest('.notification');
    notification.classList.add('hiding');
    setTimeout(() => notification.remove(), 300);
}

function deleteFile() {
    if (!confirm('Hapus bukti ini? File akan dihapus permanen.')) return;
    
    const formData = new FormData();
    formData.append('ajax_delete', '1');
    formData.append('csrf_token', '<?php echo $csrf; ?>');
    formData.append('drf', '<?php echo htmlspecialchars($drf); ?>');
    
    fetch('lihat_evidence.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => {
                window.location.href = window.location.href;
            }, 1000);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        showNotification('error', 'Terjadi kesalahan saat menghapus file.');
        console.error('Error:', error);
    });
}

function uploadFile() {
    const fileInput = document.getElementById('sos_file');
    const notesInput = document.getElementById('notes');
    
    if (!fileInput.files.length) {
        showNotification('error', 'Silakan pilih file terlebih dahulu.');
        return;
    }
    
    const file = fileInput.files[0];
    const allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
    const maxSize = 10 * 1024 * 1024;
    
    const fileName = file.name.toLowerCase();
    const ext = fileName.split('.').pop();
    
    if (!allowedExts.includes(ext)) {
        showNotification('error', `Format file .${ext} tidak diizinkan. Silakan upload file dengan format: PDF, JPG, JPEG, atau PNG.`);
        return;
    }
    
    if (file.size > maxSize) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        showNotification('error', `File terlalu besar (${sizeMB} MB). Maksimal ukuran file adalah 10 MB.`);
        return;
    }
    
    const formData = new FormData();
    formData.append('sos_file', file);
    formData.append('drf', '<?php echo htmlspecialchars($drf); ?>');
    formData.append('csrf_token', '<?php echo $csrf; ?>');
    formData.append('notes', notesInput.value);
    formData.append('return_url', '<?php echo htmlspecialchars($return_url); ?>');
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Uploading...';
    
    fetch('upload_evidence.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        if (text.includes('upload_success') || text.includes('upload_error')) {
            window.location.href = window.location.href;
        } else {
            showNotification('error', 'Terjadi kesalahan saat upload. Silakan coba lagi.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        showNotification('error', 'Terjadi kesalahan saat upload file.');
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

<?php if (isset($_SESSION['upload_error'])): ?>
    showNotification('error', <?php echo json_encode($_SESSION['upload_error']); ?>);
    <?php unset($_SESSION['upload_error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['upload_success'])): ?>
    showNotification('success', <?php echo json_encode($_SESSION['upload_success']); ?>);
    <?php unset($_SESSION['upload_success']); ?>
<?php endif; ?>
</script>
</body>
</html>