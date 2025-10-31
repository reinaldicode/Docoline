<?php
// progress_detail.php - Detail Progress per Bawahan
include('header.php');
include('koneksi.php');

// ✅ SECURITY CHECK: Hanya Approver yang boleh akses
if (!isset($_SESSION['state']) || $_SESSION['state'] !== 'Approver') {
    echo "<script>alert('Access denied!'); window.location='index_login.php';</script>";
    exit;
}

// ✅ GET NRP from session
$nrp = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$user_id = isset($_GET['user_id']) ? mysqli_real_escape_string($link, $_GET['user_id']) : '';

if (empty($user_id)) {
    echo "<script>alert('Invalid user ID!'); window.location='progress.php';</script>";
    exit;
}

// ✅ GET APPROVER INFO
$sql_approver = "SELECT section, name FROM users WHERE username='$nrp' LIMIT 1";
$result_approver = mysqli_query($link, $sql_approver);

if ($result_approver && mysqli_num_rows($result_approver) > 0) {
    $approver_data = mysqli_fetch_assoc($result_approver);
    $approver_section = isset($approver_data['section']) ? trim($approver_data['section']) : '';
} else {
    $approver_section = '';
    echo "<script>alert('Approver data not found!'); window.location='index_login.php';</script>";
    exit;
}

// ✅ NORMALIZE SECTION
function normalizeSection($section) {
    if (empty($section)) return $section;
    $section = trim($section);
    $section = preg_replace('/\s+Section$/i', '', $section);
    return $section;
}

$approver_section_normalized = normalizeSection($approver_section);

// ✅ SPECIAL CASE: QA Section includes QC
$section_variants = array($approver_section_normalized);
if (strtolower($approver_section_normalized) === 'qa') {
    $section_variants[] = 'QC';
    $section_variants[] = 'QA Section';
}

// ✅ GET BAWAHAN INFO
$sql_bawahan = "SELECT username, name, email, state, section 
                FROM users 
                WHERE username='$user_id' LIMIT 1";
$result_bawahan = mysqli_query($link, $sql_bawahan);

if (!$result_bawahan || mysqli_num_rows($result_bawahan) == 0) {
    echo "<script>alert('User not found!'); window.location='progress.php';</script>";
    exit;
}

$bawahan = mysqli_fetch_assoc($result_bawahan);
$bawahan_name = isset($bawahan['name']) ? trim($bawahan['name']) : 'Unknown';
$bawahan_email = isset($bawahan['email']) ? trim($bawahan['email']) : '';
$bawahan_state = isset($bawahan['state']) ? trim($bawahan['state']) : '';
$bawahan_section = isset($bawahan['section']) ? trim($bawahan['section']) : '';
$bawahan_section_normalized = normalizeSection($bawahan_section);

// ✅ VALIDATE SECTION
$valid_section = false;
foreach ($section_variants as $variant) {
    if (strtolower(trim($variant)) === strtolower(trim($bawahan_section_normalized))) {
        $valid_section = true;
        break;
    }
}

if (!$valid_section) {
    echo "<script>alert('Access denied! This user is not in your section.'); window.location='progress.php';</script>";
    exit;
}

// ✅ FILTER PARAMETERS
$filter_status = isset($_GET['status']) ? mysqli_real_escape_string($link, $_GET['status']) : '';
$filter_type = isset($_GET['doc_type']) ? mysqli_real_escape_string($link, $_GET['doc_type']) : '';

// ✅ BUILD QUERY
$where_conditions = ["docu.user_id = '$user_id'"];
if (!empty($filter_status)) {
    $where_conditions[] = "docu.status = '$filter_status'";
}
if (!empty($filter_type)) {
    $where_conditions[] = "docu.doc_type = '$filter_type'";
}

$sql = "SELECT 
            docu.no_drf,
            docu.no_doc,
            docu.no_rev,
            docu.doc_type,
            docu.title,
            docu.status,
            docu.tgl_upload,
            docu.sos_file
        FROM docu
        WHERE " . implode(' AND ', $where_conditions) . "
        ORDER BY docu.tgl_upload DESC";

$result = mysqli_query($link, $sql);

// ✅ GET DOCUMENT TYPES
$sql_types = "SELECT DISTINCT doc_type FROM docu WHERE user_id='$user_id' ORDER BY doc_type";
$result_types = mysqli_query($link, $sql_types);

// ✅ GET STATISTICS
$sql_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Review' THEN 1 ELSE 0 END) as review,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending
              FROM docu
              WHERE user_id = '$user_id'";

$result_stats = mysqli_query($link, $sql_stats);
$stats = mysqli_fetch_assoc($result_stats);
?>

<style>
/* ===== SOFT COLOR PALETTE ===== */
:root {
    --soft-blue: #6B9BD1;
    --soft-purple: #9B8FC4;
    --soft-green: #7EC8A3;
    --soft-orange: #F4A261;
    --soft-red: #E76F51;
    --soft-gray: #95A5A6;
    --light-bg: #F8F9FB;
    --card-bg: #FFFFFF;
    --text-primary: #2C3E50;
    --text-secondary: #7F8C8D;
    --deep-blue: #4A7BA7;
    --bright-blue: #5B9FD8;
}

/* ===== STATUS BADGES - SOFT COLORS ===== */
.status-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
}
.status-review { background: var(--soft-orange); color: white; }
.status-approved { background: var(--soft-green); color: white; }
.status-pending { background: var(--soft-red); color: white; }
.status-edited { background: var(--soft-blue); color: white; }
.status-secured { background: var(--soft-gray); color: white; }

/* ===== USER HEADER - BLUE GRADIENT ===== */
.user-header {
    background: linear-gradient(135deg, #5B9FD8 0%, #4A7BA7 100%);
    color: white;
    padding: 36px;
    border-radius: 16px;
    margin-bottom: 32px;
    box-shadow: 0 8px 24px rgba(74, 123, 167, 0.35);
    position: relative;
    overflow: hidden;
}

.user-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
}

.user-header::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
}

.user-avatar {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 16px;
    border: 3px solid rgba(255,255,255,0.5);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 16px;
    margin-top: 24px;
    max-width: 800px;
    position: relative;
}

.stat-box {
    background: rgba(255,255,255,0.25);
    padding: 20px 16px;
    border-radius: 12px;
    text-align: center;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.4);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-box:hover {
    background: rgba(255,255,255,0.35);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-box .number {
    font-size: 32px;
    font-weight: 700;
    display: block;
    margin-bottom: 6px;
    line-height: 1;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-box .label {
    font-size: 12px;
    opacity: 0.95;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* ===== FILTER BOX - SOFT DESIGN ===== */
.filter-box {
    background: var(--card-bg);
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 1px solid rgba(0,0,0,0.04);
}

.filter-box h4 {
    color: var(--text-primary);
    font-weight: 600;
    margin-top: 0;
    margin-bottom: 20px;
}

.filter-box .form-control {
    border: 1px solid #E0E4E8;
    border-radius: 8px;
    padding: 10px 14px;
    transition: all 0.3s ease;
}

.filter-box .form-control:focus {
    border-color: var(--soft-blue);
    box-shadow: 0 0 0 3px rgba(107, 155, 209, 0.1);
}

/* ===== BACK BUTTON ===== */
.back-btn {
    margin-bottom: 24px;
}

.back-btn .btn {
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid #E0E4E8;
}

.back-btn .btn:hover {
    background: var(--light-bg);
    transform: translateX(-4px);
}

/* ===== TABLE STYLING - BLUE GRADIENT ===== */
.panel {
    border: none;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border-radius: 12px;
    overflow: hidden;
}

.panel-heading {
    background: linear-gradient(135deg, #5B9FD8 0%, #4A7BA7 100%) !important;
    color: white !important;
    border: none !important;
    padding: 20px 24px !important;
}

.panel-heading .panel-title {
    color: white !important;
    font-weight: 600 !important;
    font-size: 16px;
    text-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.panel-heading .badge {
    background: rgba(255,255,255,0.3) !important;
    color: white !important;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}

.table {
    margin-bottom: 0;
}

.table thead tr {
    background: var(--light-bg) !important;
}

.table thead th {
    color: var(--text-primary) !important;
    font-weight: 600 !important;
    border: none !important;
    padding: 16px 12px !important;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(107, 155, 209, 0.04) !important;
}

.table tbody td {
    padding: 16px 12px;
    vertical-align: middle;
    border-color: #F0F2F5 !important;
    color: var(--text-primary);
}

/* ===== BUTTONS - SOFT DESIGN ===== */
.btn-primary {
    background: var(--soft-blue) !important;
    border: none !important;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #5A89C1 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(107, 155, 209, 0.3);
}

.btn-info {
    background: var(--soft-blue) !important;
    border: none !important;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.btn-info:hover {
    background: #5A89C1 !important;
    transform: scale(1.1);
}

.btn-xs {
    padding: 5px 10px;
}

/* ===== ALERT STYLING ===== */
.alert-info {
    background: #E8F4F8;
    border: 1px solid #B8E0ED;
    color: var(--text-primary);
    border-radius: 10px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .user-header {
        padding: 24px;
    }
}
</style>

<br><br><br>
<div class="container-fluid">
    
    <!-- ✅ BACK BUTTON -->
    <div class="back-btn">
        <a href="progress.php" class="btn btn-default">
            <span class="glyphicon glyphicon-arrow-left"></span> Back to Progress
        </a>
    </div>

    <!-- ✅ USER HEADER - BLUE GRADIENT -->
    <div class="user-header">
        <div class="row">
            <div class="col-md-2 text-center">
                <div class="user-avatar" style="margin: 0 auto;">
                    <?php echo strtoupper(substr($bawahan_name, 0, 2)); ?>
                </div>
            </div>
            <div class="col-md-10">
                <h2 style="margin: 0 0 12px 0; font-weight: 700; position: relative; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <?php echo htmlspecialchars($bawahan_name); ?>
                </h2>
                <p style="margin: 0; font-size: 15px; opacity: 0.95; position: relative;">
                    <span class="glyphicon glyphicon-user"></span> 
                    <strong><?php echo htmlspecialchars($bawahan_state ?: 'Unknown'); ?></strong> | 
                    <span class="glyphicon glyphicon-envelope"></span> 
                    <?php echo htmlspecialchars($bawahan_email ?: $user_id); ?> | 
                    <span class="glyphicon glyphicon-briefcase"></span> 
                    <?php echo htmlspecialchars($bawahan_section ?: 'N/A'); ?>
                </p>

                <!-- ✅ STATS GRID -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <span class="number"><?php echo $stats['total']; ?></span>
                        <span class="label">Total Documents</span>
                    </div>
                    <div class="stat-box">
                        <span class="number"><?php echo $stats['review']; ?></span>
                        <span class="label">Review</span>
                    </div>
                    <div class="stat-box">
                        <span class="number"><?php echo $stats['pending']; ?></span>
                        <span class="label">Pending</span>
                    </div>
                    <div class="stat-box">
                        <span class="number"><?php echo $stats['approved']; ?></span>
                        <span class="label">Approved</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ FILTER SECTION -->
    <div class="filter-box">
        <h4>
            <span class="glyphicon glyphicon-filter"></span> Filter Documents
        </h4>
        <form method="GET" action="progress_detail.php">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
            <div class="row">
                <div class="col-md-4">
                    <label style="color: var(--text-secondary); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Status</label>
                    <select name="status" class="form-control">
                        <option value="">-- All Status --</option>
                        <option value="Review" <?php if($filter_status=='Review') echo 'selected'; ?>>Review</option>
                        <option value="Pending" <?php if($filter_status=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Approved" <?php if($filter_status=='Approved') echo 'selected'; ?>>Approved</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label style="color: var(--text-secondary); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Document Type</label>
                    <select name="doc_type" class="form-control">
                        <option value="">-- All Types --</option>
                        <?php
                        if ($result_types) {
                            while ($type_row = mysqli_fetch_assoc($result_types)) {
                                $selected = ($filter_type == $type_row['doc_type']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($type_row['doc_type']) . "' $selected>" . htmlspecialchars($type_row['doc_type']) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label style="color: var(--text-secondary); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="glyphicon glyphicon-search"></span> Apply Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ✅ DOCUMENTS TABLE -->
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <span class="glyphicon glyphicon-list"></span> 
                Document List 
                <span class="badge"><?php echo mysqli_num_rows($result); ?></span>
            </h4>
        </div>
        <div class="panel-body" style="padding: 0;">
            <?php if (mysqli_num_rows($result) == 0): ?>
                <div class="alert alert-info text-center" style="margin: 24px;">
                    <span class="glyphicon glyphicon-info-sign"></span> 
                    No documents found<?php echo (!empty($filter_status) || !empty($filter_type)) ? ' with current filter' : ''; ?>.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Upload Date</th>
                                <th>No. Draft</th>
                                <th>No. Document</th>
                                <th>Rev</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Action</th>
                                <th>Evidence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)):
                                $status_class = 'status-' . strtolower($row['status']);
                                $sos_file = isset($row['sos_file']) ? trim($row['sos_file']) : '';
                                $has_evidence = !empty($sos_file);
                            ?>
                            <tr>
                                <td><strong><?php echo $no++; ?></strong></td>
                                <td><?php echo date('d-M-Y', strtotime($row['tgl_upload'])); ?></td>
                                <td><strong style="color: var(--soft-blue);"><?php echo htmlspecialchars($row['no_drf']); ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($row['no_doc']); ?></strong></td>
                                <td><span class="badge" style="background: var(--soft-purple); color: white;"><?php echo htmlspecialchars($row['no_rev']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['doc_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td style="white-space:nowrap;">
                                    <a href="detail.php?drf=<?php echo urlencode($row['no_drf']); ?>&no_doc=<?php echo urlencode($row['no_doc']); ?>" 
                                       class="btn btn-xs btn-info" 
                                       title="View Detail">
                                        <span class="glyphicon glyphicon-search"></span>
                                    </a>
                                    <a href="lihat_approver.php?drf=<?php echo urlencode($row['no_drf']); ?>" 
                                       class="btn btn-xs btn-info" 
                                       title="View Approver">
                                        <span class="glyphicon glyphicon-user"></span>
                                    </a>
                                    <a href="radf.php?drf=<?php echo urlencode($row['no_drf']); ?>&section=<?php echo urlencode($bawahan_section); ?>" 
                                       class="btn btn-xs btn-info" 
                                       title="View RADF">
                                        <span class="glyphicon glyphicon-eye-open"></span>
                                    </a>
                                </td>
                                <td style="white-space:nowrap;">
                                    <?php if ($has_evidence): ?>
                                        <a href="lihat_evidence.php?drf=<?php echo urlencode($row['no_drf']); ?>" 
                                           class="btn btn-xs btn-info" 
                                           title="View Evidence">
                                            <span class="glyphicon glyphicon-file"></span> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:11px;">Belum ada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php 
if (file_exists('footer.php')) {
    include('footer.php');
} else {
    echo '</body></html>';
}
?>