<?php
// lihat_sosialisasi.php
// START: Session check
if (session_status() === PHP_SESSION_NONE) session_start();

// Include koneksi dulu (sebelum ada output)
if (!file_exists('koneksi.php')) { 
    die("koneksi.php tidak ditemukan."); 
}
include 'koneksi.php';

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));

// HANDLE AJAX DELETE REQUEST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_delete']) && $_POST['ajax_delete'] === '1') {
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => ''];
    
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
    $uploadDir = __DIR__ . '/sosialisasi/';
    
    // ambil record
    $q = mysqli_query($link, "SELECT sos_file, sos_uploaded_by FROM docu WHERE no_drf = '$drf_db' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $row_del = mysqli_fetch_assoc($q);
        $file = $row_del['sos_file'];
        $uploaded_by = $row_del['sos_uploaded_by'];

        // Cek user state untuk permission
        $user_state_check = $_SESSION['state'] ?? ($_SESSION['role'] ?? 'User');
        $user_nrp_check = $_SESSION['nrp'] ?? ($_SESSION['user'] ?? ($_SESSION['username'] ?? ''));

        $allowed_to_delete = false;
        if ($user_state_check === 'Admin') $allowed_to_delete = true;
        if (!empty($user_nrp_check) && $user_nrp_check === $uploaded_by) $allowed_to_delete = true;

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
                    $response['message'] = 'File dan record sosialisasi berhasil dihapus.';
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

// Validasi DRF dulu sebelum semua proses
if (empty($_GET['drf'])) {
    $_SESSION['upload_error'] = "Parameter DRF tidak ditemukan.";
    if (!headers_sent()) {
        header('Location: wi_prod.php');
        exit;
    }
}
$drf = mysqli_real_escape_string($link, $_GET['drf']);

// SETELAH SEMUA REDIRECT SELESAI, BARU INCLUDE HEADER & CEK LOGIN
$isLoggedIn = false;
$user_state = '';
$user_nrp = '';

// Cek berbagai kemungkinan nama session variable
if ((isset($_SESSION['state']) && !empty($_SESSION['state'])) || 
    (isset($_SESSION['login']) && $_SESSION['login'] === true) ||
    (isset($_SESSION['user']) && !empty($_SESSION['user'])) ||
    (isset($_SESSION['username']) && !empty($_SESSION['username'])) ||
    (isset($_SESSION['nrp']) && !empty($_SESSION['nrp']))) {
    
    $isLoggedIn = true;
    $user_state = $_SESSION['state'] ?? ($_SESSION['role'] ?? 'User');
    $user_nrp = $_SESSION['nrp'] ?? ($_SESSION['user'] ?? ($_SESSION['username'] ?? 'Unknown'));
}

// Jika masih belum login, coba include header.php dulu (mungkin ada auto-login)
if (!$isLoggedIn) {
    if (file_exists('header.php')) include 'header.php';
    
    // Cek lagi setelah include header
    if ((isset($_SESSION['state']) && !empty($_SESSION['state'])) || 
        (isset($_SESSION['nrp']) && !empty($_SESSION['nrp']))) {
        $isLoggedIn = true;
        $user_state = $_SESSION['state'] ?? 'User';
        $user_nrp = $_SESSION['nrp'] ?? 'Unknown';
    }
} else {
    // Jika sudah login, tetap include header.php untuk background
    if (file_exists('header.php')) include 'header.php';
}

// Jika tetap belum login, redirect
if (!$isLoggedIn) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    if (!headers_sent()) {
        header('Location: index_login.php');
        exit;
    }
}

$webUploadDir = 'sosialisasi/';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    else $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

// ambil data docu
$q = mysqli_query($link, "SELECT no_doc, sos_file, sos_uploaded_by, sos_upload_date, sos_notes FROM docu WHERE no_drf = '" . mysqli_real_escape_string($link, $drf) . "' LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    echo "<div class='alert alert-danger'>Dokumen tidak ditemukan.</div>";
    exit;
}
$row = mysqli_fetch_assoc($q);
$has_sos = !empty($row['sos_file']);
?>

<!-- CSS untuk notifikasi toast -->
<style>
.notification-container {
    position: fixed;
    top: 70px;
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

.notification.error {
    border-left: 4px solid #ef4444;
}

.notification.success {
    border-left: 4px solid #22c55e;
}

.notification-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
}

.notification-icon.error {
    color: #ef4444;
}

.notification-icon.success {
    color: #22c55e;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    margin-bottom: 4px;
    font-size: 14px;
}

.notification-message {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

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
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-close:hover {
    color: #333;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.notification.hiding {
    animation: slideOut 0.3s ease-out forwards;
}

/* Loading animation untuk button */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.glyphicon-refresh-animate {
    animation: spin 1s linear infinite;
}
</style>

<!-- Container untuk notifikasi -->
<div class="notification-container" id="notificationContainer"></div>

<br><br><br>
<div class="container" style="padding-top:20px;">
    <h3>Bukti Sosialisasi untuk DRF: <strong><?php echo htmlspecialchars($drf); ?></strong></h3>

    <p>
        <button class="btn btn-primary" onclick="goBack()">
            <span class="glyphicon glyphicon-arrow-left"></span>&nbsp;Back
        </button>
    </p>

    <?php if ($has_sos): 
        $fileUrl = $webUploadDir . rawurlencode($row['sos_file']);
    ?>
        <table class="table table-hover table-bordered" id="sosTable">
            <tr><th>No. Document</th><td><?php echo htmlspecialchars($row['no_doc']); ?></td></tr>
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
                    <a class="btn btn-xs btn-info" target="_blank" href="<?php echo $fileUrl;?>" title="View"><span class="glyphicon glyphicon-eye-open"></span></a>
                    <a class="btn btn-xs btn-success" href="<?php echo $fileUrl;?>" download="<?php echo htmlspecialchars($row['sos_file']); ?>" title="Download"><span class="glyphicon glyphicon-download"></span></a>

                    <?php
                    // Cek permission untuk delete (Admin atau user yang upload)
                    if ($user_state === 'Admin' || (!empty($user_nrp) && $user_nrp === $row['sos_uploaded_by'])):
                    ?>
                        <button class="btn btn-xs btn-danger" onclick="deleteFile()" title="Hapus">
                            <span class="glyphicon glyphicon-trash"></span>
                        </button>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php else: ?>
        <div class="alert alert-info" id="noFileAlert">Belum ada bukti sosialisasi untuk DRF ini.</div>
    <?php endif; ?>

    <hr />
    <h4>Replace bukti sosialisasi</h4>
    
    <?php if ($isLoggedIn): ?>
        <form id="uploadForm" onsubmit="return false;">
            <input type="hidden" name="drf" value="<?php echo htmlspecialchars($drf); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf; ?>">
            <div class="form-group">
                <label>Pilih file (pdf/jpg/jpeg/png). Max 10MB.</label>
                <input type="file" name="sos_file" id="sos_file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <div class="form-group">
                <label>Catatan / Keterangan (optional)</label>
                <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
            </div>
            <button class="btn btn-primary" type="button" id="uploadBtn" onclick="uploadFile(this)">Upload Sosialisasi</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning">
            <strong>Info:</strong> Anda harus login terlebih dahulu untuk dapat mengupload bukti sosialisasi.
            <br><br>
            <a href="index_login.php" class="btn btn-primary">Login Sekarang</a>
        </div>
    <?php endif; ?>

</div>

<!-- Script untuk menampilkan notifikasi dan handle delete -->
<script>
// Notifikasi functions
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
    
    // Auto close setelah 5 detik
    setTimeout(() => {
        closeNotification(notification.querySelector('.notification-close'));
    }, 5000);
}

function closeNotification(button) {
    const notification = button.closest('.notification');
    notification.classList.add('hiding');
    setTimeout(() => {
        notification.remove();
    }, 300);
}

// Handle AJAX Delete
function deleteFile() {
    if (!confirm('Hapus bukti ini?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('ajax_delete', '1');
    formData.append('csrf_token', '<?php echo $csrf; ?>');
    formData.append('drf', '<?php echo htmlspecialchars($drf); ?>');
    
    fetch('lihat_sosialisasi.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            // Reload halaman tanpa menambah history
            setTimeout(() => {
                window.location.replace(window.location.href);
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

// Handle AJAX Upload
function uploadFile() {
    const fileInput = document.getElementById('sos_file');
    const notesInput = document.getElementById('notes');
    
    // Validasi file
    if (!fileInput.files.length) {
        showNotification('error', 'Silakan pilih file terlebih dahulu.');
        return;
    }
    
    const file = fileInput.files[0];
    const allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    // Cek ekstensi
    const fileName = file.name.toLowerCase();
    const ext = fileName.split('.').pop();
    
    if (!allowedExts.includes(ext)) {
        showNotification('error', `Format file .${ext} tidak diizinkan. Silakan upload file dengan format: PDF, JPG, JPEG, atau PNG.`);
        return;
    }
    
    // Cek ukuran
    if (file.size > maxSize) {
        const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
        showNotification('error', `File terlalu besar (${sizeMB} MB). Maksimal ukuran file adalah 10 MB.`);
        return;
    }
    
    // Prepare FormData
    const formData = new FormData();
    formData.append('sos_file', file);
    formData.append('drf', '<?php echo htmlspecialchars($drf); ?>');
    formData.append('csrf_token', '<?php echo $csrf; ?>');
    formData.append('notes', notesInput.value);
    
    // Disable button dan tampilkan loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Uploading...';
    
    // Send AJAX request
    fetch('upload_sosialisasi.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        // Check apakah response adalah redirect atau error message
        if (text.includes('upload_success') || text.includes('upload_error')) {
            // Ada session message, reload untuk tampilkan notifikasi
            window.location.replace(window.location.href);
        } else {
            // Kemungkinan ada error message langsung
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

// Client-side validation untuk upload form (backup, karena pakai button onclick)
document.getElementById('uploadForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    return false;
});

function goBack() {
    if (document.referrer !== "") {
        window.history.back();
    } else {
        window.location.href = "detail.php?drf=<?php echo urlencode($drf); ?>";
    }
}

// Cek apakah ada pesan dari session
<?php if (isset($_SESSION['upload_error'])): ?>
    showNotification('error', <?php echo json_encode($_SESSION['upload_error']); ?>);
    <?php unset($_SESSION['upload_error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['upload_success'])): ?>
    showNotification('success', <?php echo json_encode($_SESSION['upload_success']); ?>);
    <?php unset($_SESSION['upload_success']); ?>
<?php endif; ?>
</script>

<script src="bootstrap/js/jquery.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>