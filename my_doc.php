<?php
// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Essential includes with error handling
$required_files = ['header.php', 'koneksi.php', 'document_config.php'];
foreach ($required_files as $file) {
    if (file_exists($file)) {
        include_once $file;
    } else {
        die("Error: Required file '$file' not found.");
    }
}

/**
 * ============================================================================
 * DATABASE QUERY OPTIMIZER - UPDATED WITH USER NAMES
 * ============================================================================
 */
class DocumentQuery
{
    private $link;
    private $nrp;
    private $state;

    public function __construct($link, $nrp, $state)
    {
        $this->link = $link;
        $this->nrp = mysqli_real_escape_string($link, $nrp);
        $this->state = $state;
    }

    /**
     * Get notification counts with single optimized query
     */
    public function getNotificationCounts($docTypes)
    {
        $counts = [
            'total' => 0,
            'my_documents' => 0,
            'to_review' => 0
        ];

        if (!is_array($docTypes)) {
            $docTypes = [];
        }

        foreach ($docTypes as $type) {
            $counts[$type] = 0;
        }

        try {
            if ($this->state == 'Admin') {
                $sql = "SELECT doc_type, COUNT(*) as count 
                        FROM docu 
                        WHERE status IN ('Review', 'Pending', 'Edited')
                        GROUP BY doc_type";

                $result = mysqli_query($this->link, $sql);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $type = $row['doc_type'];
                        $count = (int)$row['count'];
                        if (isset($counts[$type])) {
                            $counts[$type] = $count;
                            $counts['total'] += $count;
                        }
                    }
                    mysqli_free_result($result);
                }
            } elseif ($this->state == 'Originator') {
                $sql = "SELECT doc_type, COUNT(*) as count 
                        FROM docu 
                        WHERE user_id='{$this->nrp}' 
                        AND status IN ('Review', 'Pending', 'Edited')
                        GROUP BY doc_type";

                $result = mysqli_query($this->link, $sql);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $type = $row['doc_type'];
                        $count = (int)$row['count'];
                        if (isset($counts[$type])) {
                            $counts[$type] = $count;
                            $counts['total'] += $count;
                        }
                    }
                    mysqli_free_result($result);
                }
            } elseif ($this->state == 'Approver') {
                $sql = "
                    SELECT 'to_review' as type, COUNT(DISTINCT docu.no_drf) as count
                    FROM docu
                    INNER JOIN rev_doc ON docu.no_drf = rev_doc.id_doc
                    WHERE docu.status = 'Review' 
                    AND rev_doc.status = 'Review' 
                    AND rev_doc.nrp = '{$this->nrp}'
                    
                    UNION ALL
                    
                    SELECT 'my_documents' as type, COUNT(*) as count
                    FROM docu 
                    WHERE user_id = '{$this->nrp}' 
                    AND status IN ('Review', 'Pending', 'Edited')
                    
                    UNION ALL
                    
                    SELECT doc_type as type, COUNT(*) as count
                    FROM (
                        SELECT DISTINCT docu.no_drf, docu.doc_type
                        FROM docu
                        INNER JOIN rev_doc ON docu.no_drf = rev_doc.id_doc
                        WHERE docu.status = 'Review' 
                        AND rev_doc.status = 'Review' 
                        AND rev_doc.nrp = '{$this->nrp}'
                        
                        UNION
                        
                        SELECT no_drf, doc_type
                        FROM docu 
                        WHERE user_id = '{$this->nrp}' 
                        AND status IN ('Review', 'Pending', 'Edited')
                    ) as combined
                    GROUP BY doc_type
                ";

                $result = mysqli_query($this->link, $sql);
                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $type = $row['type'];
                        $count = (int)$row['count'];

                        if ($type == 'to_review' || $type == 'my_documents') {
                            $counts[$type] = $count;
                        } elseif (isset($counts[$type])) {
                            $counts[$type] = $count;
                            $counts['total'] += $count;
                        }
                    }
                    mysqli_free_result($result);
                }
            }
        } catch (Exception $e) {
            error_log("Error in getNotificationCounts: " . $e->getMessage());
        }

        return $counts;
    }

    /**
     * Get recent notifications (limited) - WITH USER NAMES
     */
    public function getRecentNotifications($limit = 5)
    {
        $notifications = [];
        $limit = (int)$limit;

        try {
            if ($this->state == 'Admin') {
                $sql = "SELECT docu.no_drf, docu.no_doc, docu.title, docu.doc_type, 
                        docu.status, docu.tgl_upload, docu.user_id,
                        users.name as user_name,
                        DATEDIFF(NOW(), docu.tgl_upload) as days_passed
                        FROM docu 
                        LEFT JOIN users ON docu.user_id = users.username
                        WHERE docu.status IN ('Review', 'Pending', 'Edited') 
                        ORDER BY docu.tgl_upload DESC 
                        LIMIT $limit";
            } elseif ($this->state == 'Originator') {
                $sql = "SELECT docu.no_drf, docu.no_doc, docu.title, docu.doc_type, 
                        docu.status, docu.tgl_upload, docu.user_id,
                        users.name as user_name,
                        DATEDIFF(NOW(), docu.tgl_upload) as days_passed
                        FROM docu 
                        LEFT JOIN users ON docu.user_id = users.username
                        WHERE docu.user_id = '{$this->nrp}' 
                        AND docu.status IN ('Review', 'Pending', 'Edited')
                        ORDER BY docu.tgl_upload DESC 
                        LIMIT $limit";
            } elseif ($this->state == 'Approver') {
                $sql = "SELECT no_drf, no_doc, title, doc_type, status, tgl_upload, user_id, user_name,
                        DATEDIFF(NOW(), tgl_upload) as days_passed
                        FROM (
                            SELECT DISTINCT docu.no_drf, docu.no_doc, docu.title, 
                                   docu.doc_type, docu.status, docu.tgl_upload, docu.user_id,
                                   users.name as user_name
                            FROM docu
                            INNER JOIN rev_doc ON docu.no_drf = rev_doc.id_doc
                            LEFT JOIN users ON docu.user_id = users.username
                            WHERE docu.status = 'Review' 
                            AND rev_doc.status = 'Review' 
                            AND rev_doc.nrp = '{$this->nrp}'
                            
                            UNION
                            
                            SELECT docu.no_drf, docu.no_doc, docu.title, docu.doc_type, 
                                   docu.status, docu.tgl_upload, docu.user_id,
                                   users.name as user_name
                            FROM docu 
                            LEFT JOIN users ON docu.user_id = users.username
                            WHERE docu.user_id = '{$this->nrp}' 
                            AND docu.status IN ('Review', 'Pending', 'Edited')
                        ) as combined
                        ORDER BY tgl_upload DESC 
                        LIMIT $limit";
            }

            $result = mysqli_query($this->link, $sql);
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $notifications[] = $row;
                }
                mysqli_free_result($result);
            }
        } catch (Exception $e) {
            error_log("Error in getRecentNotifications: " . $e->getMessage());
        }

        return $notifications;
    }

    /**
     * Get documents with optimized query - WITH USER NAMES
     */
    public function getDocuments($tipe = '-', $status = '')
    {
        $typeFilter = '';
        if ($tipe != '-') {
            $tipe_escaped = mysqli_real_escape_string($this->link, $tipe);
            $typeFilter = "AND docu.doc_type = '$tipe_escaped'";
        }

        try {
            if ($this->state == 'Admin') {
                if (empty($status) || $status == '-') {
                    $sql = "SELECT docu.*, users.name as user_name 
                            FROM docu 
                            LEFT JOIN users ON docu.user_id = users.username
                            WHERE docu.status IN ('Review', 'Pending', 'Edited') 
                            $typeFilter 
                            ORDER BY docu.no_drf DESC";
                } else {
                    $status_escaped = mysqli_real_escape_string($this->link, $status);
                    $sql = "SELECT docu.*, users.name as user_name 
                            FROM docu 
                            LEFT JOIN users ON docu.user_id = users.username
                            WHERE docu.status = '$status_escaped' 
                            $typeFilter 
                            ORDER BY docu.no_drf DESC";
                }

                return mysqli_query($this->link, $sql);
            } elseif ($this->state == 'Originator') {
                $sql = "SELECT docu.*, users.name as user_name 
                        FROM docu 
                        LEFT JOIN users ON docu.user_id = users.username
                        WHERE docu.user_id = '{$this->nrp}' 
                        AND docu.status IN ('Review', 'Pending', 'Edited') 
                        $typeFilter 
                        ORDER BY docu.no_drf DESC";

                return mysqli_query($this->link, $sql);
            } elseif ($this->state == 'Approver') {
                $results = [];

                // Documents to review - WITH USER NAMES
                $sql_review = "SELECT DISTINCT docu.*, users.name as user_name
                               FROM docu
                               INNER JOIN rev_doc ON docu.no_drf = rev_doc.id_doc
                               LEFT JOIN users ON docu.user_id = users.username
                               WHERE docu.status = 'Review' 
                               AND rev_doc.status = 'Review' 
                               AND rev_doc.nrp = '{$this->nrp}'
                               $typeFilter
                               ORDER BY docu.no_drf DESC";

                $results['to_review'] = mysqli_query($this->link, $sql_review);

                // My documents - WITH USER NAMES
                $sql_my = "SELECT docu.*, users.name as user_name 
                           FROM docu 
                           LEFT JOIN users ON docu.user_id = users.username
                           WHERE docu.user_id = '{$this->nrp}' 
                           AND docu.status IN ('Review', 'Pending', 'Edited')
                           $typeFilter
                           ORDER BY docu.no_drf DESC";

                $results['my_documents'] = mysqli_query($this->link, $sql_my);

                return $results;
            }
        } catch (Exception $e) {
            error_log("Error in getDocuments: " . $e->getMessage());
        }

        return false;
    }
}

/**
 * ============================================================================
 * UTILITY FUNCTIONS
 * ============================================================================
 */
function calculateDaysPassed($uploadDate)
{
    $timestamp = strtotime($uploadDate);
    if ($timestamp === false) return 0;

    $days = floor((time() - $timestamp) / 86400);
    return max(0, $days);
}

function getRowClass($info)
{
    $days = calculateDaysPassed($info['tgl_upload']);

    if ($days >= 3 && $info['status'] == 'Review') {
        return 'danger';
    } elseif ($days >= 1 && $info['status'] == 'Review') {
        return 'warning';
    } elseif ($info['status'] == 'Pending') {
        return 'warning';
    } elseif ($info['status'] == 'Edited') {
        return 'info';
    }

    return '';
}

function getDocumentPath($info)
{
    $doc_type = $info['doc_type'];
    $safe_type = preg_replace('/[^A-Za-z0-9 _\-&]/', '', $doc_type);
    $safe_type = str_replace(' ', '_', $safe_type);

    return ($info['no_drf'] > 12967) ? $safe_type : "document";
}

function escapeHtml($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * ============================================================================
 * RENDER FUNCTIONS - UPDATED WITH USER NAMES
 * ============================================================================
 */
function renderTableRow($info, $nrp, $state, $tipe, $section_type, $index)
{
    $days = calculateDaysPassed($info['tgl_upload']);
    $is_doc_originator = ($info['user_id'] == $nrp);
    $rowClass = getRowClass($info);
    $docPath = getDocumentPath($info);

    // Pre-escape values - WITH USER NAME
    $esc = [
        'tgl_upload' => escapeHtml($info['tgl_upload']),
        'no_drf' => escapeHtml($info['no_drf']),
        'user_id' => escapeHtml($info['user_id']),
        'user_name' => escapeHtml($info['user_name'] ?? $info['user_id']),
        'no_doc' => escapeHtml($info['no_doc']),
        'no_rev' => escapeHtml($info['no_rev']),
        'title' => escapeHtml($info['title']),
        'process' => escapeHtml($info['process']),
        'section' => escapeHtml($info['section']),
        'doc_type' => escapeHtml($info['doc_type']),
        'rev_to' => escapeHtml($info['rev_to']),
        'status' => escapeHtml($info['status']),
        'file' => escapeHtml($info['file']),
        'device' => escapeHtml($info['device'] ?? ''),
    ];

    $url = [
        'no_doc' => urlencode($info['no_doc']),
        'title' => urlencode($info['title']),
        'doc_type' => urlencode($info['doc_type']),
        'section' => urlencode($info['section']),
        'device' => urlencode($info['device'] ?? ''),
    ];

?>
    <tr <?php if ($rowClass) echo 'class="' . $rowClass . '"'; ?>>
        <td><?php echo $index; ?></td>
        <td><?php echo $esc['tgl_upload']; ?></td>
        <td><?php echo $esc['no_drf']; ?></td>
        <td>
            <strong><?php echo $esc['user_name']; ?></strong>
            <br>
            <small class="text-muted"><?php echo $esc['user_id']; ?></small>
        </td>
        <td><?php echo $esc['no_doc']; ?></td>
        <td><?php echo $esc['no_rev']; ?></td>
        <td>
            <a href="<?php echo $docPath; ?>/<?php echo $esc['file']; ?>" target="_blank">
                <?php echo $esc['title']; ?>
            </a>
        </td>
        <td><?php echo $esc['process']; ?></td>
        <td><?php echo $esc['section']; ?></td>
        <td><?php echo $esc['doc_type']; ?></td>
        <td><?php echo $esc['rev_to']; ?></td>
        <td>
            <?php echo $days; ?>
            <?php if ($days >= 1 && $info['status'] == 'Review' && ($state == 'Admin' || $is_doc_originator)): ?>
                <a href="reminder.php?drf=<?php echo $info['no_drf']; ?>&type=<?php echo $url['doc_type']; ?>&nodoc=<?php echo $url['no_doc']; ?>&title=<?php echo $url['title']; ?>"
                    class="btn btn-xs btn-warning">
                    <span class="glyphicon glyphicon-envelope"></span> Reminder <strong><?php echo (int)$info['reminder']; ?>x</strong>
                </a>
            <?php endif; ?>
        </td>
        <td>
            <?php
            $statusClass = 'success';
            if ($info['status'] == 'Review') $statusClass = 'info';
            elseif ($info['status'] == 'Pending') $statusClass = 'warning';
            elseif ($info['status'] == 'Edited') $statusClass = 'primary';
            ?>
            <span class="label label-<?php echo $statusClass; ?>">
                <?php echo $esc['status']; ?>
            </span>
        </td>
        <td class="action-buttons">
            <a href="detail.php?drf=<?php echo $info['no_drf']; ?>&no_doc=<?php echo $url['no_doc']; ?>"
                class="btn btn-xs btn-info" title="View Detail">
                <span class="glyphicon glyphicon-search"></span>
            </a>
            <a href="radf.php?drf=<?php echo $info['no_drf']; ?>&section=<?php echo $url['section']; ?>"
                class="btn btn-xs btn-info" title="View RADF">
                <span class="glyphicon glyphicon-eye-open"></span>
            </a>
            <a href="lihat_approver.php?drf=<?php echo $info['no_drf']; ?>&nodoc=<?php echo $url['no_doc']; ?>&title=<?php echo $url['title']; ?>&type=<?php echo $url['doc_type']; ?>"
                class="btn btn-xs btn-info" title="View Approver">
                <span class="glyphicon glyphicon-user"></span>
            </a>

            <?php if ($section_type == 'reviewer' && !$is_doc_originator): ?>
                <a href="approve.php?drf=<?php echo $info['no_drf']; ?>&device=<?php echo $url['device']; ?>&no_doc=<?php echo $url['no_doc']; ?>&title=<?php echo $url['title']; ?>&tipe=<?php echo urlencode($tipe); ?>"
                    class="btn btn-xs btn-success" title="Approve">
                    <span class="glyphicon glyphicon-thumbs-up"></span>
                </a>
                <a href="pending.php?drf=<?php echo $info['no_drf']; ?>&no_doc=<?php echo $url['no_doc']; ?>&type=<?php echo $url['doc_type']; ?>"
                    class="btn btn-xs btn-warning" title="Suspend">
                    <span class="glyphicon glyphicon-warning-sign"></span>
                </a>
            <?php elseif ($section_type == 'originator' || $state == 'Originator' || $state == 'Admin'): ?>
                <?php if ($state == 'Admin' || ($is_doc_originator && $info['status'] != "Approved")): ?>
                    <a href="edit_doc.php?drf=<?php echo $info['no_drf']; ?>"
                        class="btn btn-xs btn-primary" title="Edit">
                        <span class="glyphicon glyphicon-pencil"></span>
                    </a>
                    <a href="del_doc.php?drf=<?php echo $info['no_drf']; ?>"
                        class="btn btn-xs btn-danger"
                        onclick="return confirm('Delete document <?php echo $esc['no_doc']; ?>?')"
                        title="Delete">
                        <span class="glyphicon glyphicon-remove"></span>
                    </a>

                    <?php if ($info['status'] == 'Approved'): ?>
                        <a data-toggle="modal" data-target="#myModal2"
                            data-id="<?php echo $info['no_drf']; ?>"
                            data-lama="<?php echo $esc['file']; ?>"
                            data-type="<?php echo $esc['doc_type']; ?>"
                            data-status="<?php echo $esc['status']; ?>"
                            data-rev="<?php echo $esc['rev_to']; ?>"
                            class="btn btn-xs btn-success sec-file" title="Secure Document">
                            <span class="glyphicon glyphicon-play"></span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($info['status'] == 'Pending' && ($is_doc_originator || $state == 'Admin')): ?>
                    <button data-toggle="modal" data-target="#myModal"
                        data-id="<?php echo $info['no_drf']; ?>"
                        data-type="<?php echo $esc['doc_type']; ?>"
                        data-nodoc="<?php echo $esc['no_doc']; ?>"
                        data-title="<?php echo $esc['title']; ?>"
                        data-lama="<?php echo $esc['file']; ?>"
                        class="btn btn-xs btn-warning upload-file">
                        <span class="glyphicon glyphicon-upload"></span> Change Document
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
<?php
}

function renderDocumentTable($result, $nrp, $state, $tipe, $section_type = '')
{
    if (!$result || mysqli_num_rows($result) == 0) {
        echo '<div class="empty-state">
                  <span class="glyphicon glyphicon-folder-open" style="font-size: 48px; color: #ccc;"></span>
                  <h4>No Documents Found</h4>
                  <p>No documents match your criteria.</p>
              </div>';
        return;
    }
?>
    <div class="table-responsive">
        <table class="table table-hover table-condensed table-striped">
            <thead class="bg-info">
                <tr>
                    <th width="40">No</th>
                    <th width="150">Date</th>
                    <th width="300">No. DRF</th>
                    <th width="200">User Name</th>
                    <th width="150">No Document</th>
                    <th width="100">Rev.</th>
                    <th>Title</th>
                    <th width="100">Process</th>
                    <th width="100">Section</th>
                    <th width="100">Type</th>
                    <th width="100">Review To</th>
                    <th width="120">Days</th>
                    <th width="80">Status</th>
                    <th width="200">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $index = 1;
                while ($info = mysqli_fetch_assoc($result)):
                    renderTableRow($info, $nrp, $state, $tipe, $section_type, $index);
                    $index++;
                endwhile;
                ?>
            </tbody>
        </table>
    </div>
<?php
}

// ============================================================================
// MAIN EXECUTION
// ============================================================================

// Get request parameters
$tipe = $_GET['tipe'] ?? '-';
$status = $_GET['status'] ?? '';
$submit = $_GET['submit'] ?? '';

// Initialize
$docTypes = DocumentConfig::getFlattenedDocumentTypes();
$docQuery = new DocumentQuery($link, $nrp, $state);

// Get notification counts
$notifCounts = $docQuery->getNotificationCounts($docTypes);
$recentNotifications = $docQuery->getRecentNotifications(5);

// Auto show documents if there are notifications and no manual submit
$autoShow = false;
if ($notifCounts['total'] > 0 && empty($submit)) {
    $autoShow = true;
    $submit = 'Show';
    $tipe = '-';
}

?>

<style>
    .bg-info {
        background-color: #d9edf7;
    }

    .danger {
        background-color: #f8d7da;
    }

    .warning {
        background-color: #fff3cd;
    }

    .info {
        background-color: #d1ecf1;
    }

    .notification-badge {
        position: relative;
        display: inline-block;
    }

    .notification-count {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .notification-panel {
        position: fixed;
        top: 70px;
        right: 20px;
        width: 350px;
        max-height: 400px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: 1050;
        display: none;
        border: 1px solid #dee2e6;
    }

    .notification-header {
        padding: 15px;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
    }

    .notification-body {
        max-height: 300px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f1f3f4;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
    }

    .notification-item.urgent {
        background-color: #fff3cd;
        border-left: 3px solid #ffc107;
    }

    .notification-item.overdue {
        background-color: #f8d7da;
        border-left: 3px solid #dc3545;
    }

    .notification-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .section-header {
        background: #fff;
        border-left: 4px solid #667eea;
        padding: 12px 20px;
        margin: 30px 0 15px 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border-radius: 4px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-header.my-docs {
        border-left-color: #28a745;
    }

    .section-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-badge {
        background: #f8f9fa;
        color: #666;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #999;
        background: #fafafa;
        border-radius: 4px;
        margin-bottom: 25px;
    }

    .action-buttons {
        white-space: nowrap;
    }

    .action-buttons .btn {
        margin: 2px;
    }

    .table-responsive {
        margin-bottom: 15px;
        overflow-x: auto;
    }

    .table {
        margin-bottom: 0;
    }

    .table>thead>tr>th {
        white-space: nowrap;
    }

    /* Style untuk User Name/ID */
    td strong {
        display: block;
        color: #333;
        font-weight: 600;
    }

    td .text-muted {
        color: #6c757d;
        font-size: 11px;
    }
</style>

<div id="profile">
    <div class="alert alert-info">
        <b>Welcome: <i><?php echo escapeHtml($name); ?>, <?php echo escapeHtml($state); ?></i></b>

        <div style="float: right;">
            <button class="btn btn-outline-primary notification-badge" id="notificationBell" type="button">
                <span class="glyphicon glyphicon-bell"></span>
                <?php if ($notifCounts['total'] > 0): ?>
                    <span class="notification-count"><?php echo $notifCounts['total']; ?></span>
                <?php endif; ?>
            </button>
        </div>
    </div>
</div>

<?php if ($notifCounts['total'] > 0): ?>
    <div class="notification-summary">
        <h5 style="margin: 0 0 15px 0;">
            <span class="glyphicon glyphicon-dashboard"></span> Quick Overview
        </h5>

        <?php if ($state == 'Approver'): ?>
            <div style="display: flex; gap: 15px; margin-bottom: 15px; flex-wrap: wrap;">
                <div style="flex: 1; background: rgba(255,255,255,0.2); padding: 12px 15px; border-radius: 6px; min-width: 180px;">
                    <div style="font-size: 11px; opacity: 0.85; text-transform: uppercase;">To Review</div>
                    <div style="font-size: 24px; font-weight: 600; margin-top: 4px;"><?php echo $notifCounts['to_review']; ?></div>
                </div>
                <div style="flex: 1; background: rgba(255,255,255,0.2); padding: 12px 15px; border-radius: 6px; min-width: 180px;">
                    <div style="font-size: 11px; opacity: 0.85; text-transform: uppercase;">My Documents</div>
                    <div style="font-size: 24px; font-weight: 600; margin-top: 4px;"><?php echo $notifCounts['my_documents']; ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php
        $hasTypes = false;
        if (is_array($docTypes)) {
            foreach ($docTypes as $type) {
                if (isset($notifCounts[$type]) && $notifCounts[$type] > 0) {
                    $hasTypes = true;
                    break;
                }
            }
        }

        if ($hasTypes): ?>
            <div style="font-size: 11px; opacity: 0.85; margin-bottom: 8px; text-transform: uppercase;">Filter by Type</div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <?php foreach ($docTypes as $type): ?>
                    <?php if (isset($notifCounts[$type]) && $notifCounts[$type] > 0): ?>
                        <a href="?tipe=<?php echo urlencode($type); ?>&submit=Show<?php echo ($state == 'Admin' && !empty($status)) ? '&status=' . urlencode($status) : ''; ?>"
                            style="background: rgba(255,255,255,0.25); padding: 6px 12px; border-radius: 4px; font-size: 13px; text-decoration: none; color: white; display: inline-flex; align-items: center; gap: 6px;">
                            <span><?php echo escapeHtml($type); ?></span>
                            <span style="background: white; color: #667eea; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;">
                                <?php echo $notifCounts[$type]; ?>
                            </span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="notification-panel" id="notificationPanel">
    <div class="notification-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h6 style="margin: 0;"><span class="glyphicon glyphicon-bell"></span> Recent Updates</h6>
            <button class="btn btn-xs btn-default" onclick="$('#notificationPanel').fadeOut();" type="button">×</button>
        </div>
    </div>
    <div class="notification-body">
        <?php if (empty($recentNotifications)): ?>
            <div class="notification-item">
                <small class="text-muted">No recent notifications</small>
            </div>
        <?php else: ?>
            <?php foreach ($recentNotifications as $notif): ?>
                <div class="notification-item <?php echo ($notif['days_passed'] >= 3) ? 'overdue' : (($notif['days_passed'] >= 1) ? 'urgent' : ''); ?>"
                    onclick="window.location.href='detail.php?drf=<?php echo $notif['no_drf']; ?>&no_doc=<?php echo urlencode($notif['no_doc']); ?>'">
                    <div style="display: flex; justify-content: space-between;">
                        <strong><?php echo escapeHtml($notif['doc_type']); ?> - <?php echo escapeHtml($notif['status']); ?></strong>
                        <small class="text-muted"><?php echo (int)$notif['days_passed']; ?> days</small>
                    </div>
                    <small><?php echo escapeHtml($notif['no_doc']); ?>: <?php echo escapeHtml(substr($notif['title'], 0, 50)); ?>...</small>
                    <br>
                    <small class="text-muted"><strong><?php echo escapeHtml($notif['user_name'] ?? $notif['user_id']); ?></strong></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<h2>My Document</h2>

<form action="" method="GET" class="form-inline" style="margin-bottom: 20px;">
    <div class="form-group">
        <select name="tipe" class="form-control">
            <option value="-">--- Select Type ---</option>
            <?php
            if (is_array($docTypes)) {
                foreach ($docTypes as $type):
                    if (!isset($notifCounts[$type]) || $notifCounts[$type] == 0) {
                        continue;
                    }

                    $type_esc = escapeHtml($type);
                    $count_label = ' (' . $notifCounts[$type] . ' new)';
            ?>
                    <option value="<?php echo $type_esc; ?>" <?php echo ($tipe == $type) ? 'selected' : ''; ?>>
                        <?php echo $type_esc . $count_label; ?>
                    </option>
            <?php
                endforeach;
            }
            ?>
        </select>

        <?php if ($state == 'Admin'): ?>
            <select name="status" class="form-control">
                <option value="-">--- Select Status ---</option>
                <option <?php echo ($status == 'Review') ? 'selected' : ''; ?> value="Review">Review</option>
                <option <?php echo ($status == 'Pending') ? 'selected' : ''; ?> value="Pending">Pending</option>
                <option <?php echo ($status == 'Edited') ? 'selected' : ''; ?> value="Edited">Edited</option>
                <option <?php echo ($status == 'Approved') ? 'selected' : ''; ?> value="Approved">Approved</option>
            </select>
        <?php endif; ?>

        <button type="submit" name="submit" value="Show" class="btn btn-primary">
            <span class="glyphicon glyphicon-search"></span> Show
        </button>
    </div>
</form>

<?php
if (!empty($submit)) {
    try {
        if ($state == 'Admin' || $state == 'Originator') {
            $result = $docQuery->getDocuments($tipe, $status);

            if ($result && mysqli_num_rows($result) > 0) {
                renderDocumentTable($result, $nrp, $state, $tipe, $state == 'Admin' ? '' : 'originator');
                mysqli_free_result($result);
            } else {
                echo '<div class="empty-state">
                          <span class="glyphicon glyphicon-folder-open" style="font-size: 48px; color: #ccc;"></span>
                          <h4>No Documents Found</h4>
                          <p>No documents match your criteria.</p>
                      </div>';
            }
        } elseif ($state == 'Approver') {
            $results = $docQuery->getDocuments($tipe, '');

            if ($results && is_array($results)) {
                $count_review = $results['to_review'] ? mysqli_num_rows($results['to_review']) : 0;
                $count_my = $results['my_documents'] ? mysqli_num_rows($results['my_documents']) : 0;

                // Documents to Review Section
                echo '<div class="section-header">
                          <h3><span class="glyphicon glyphicon-check"></span> Documents to Review</h3>
                          <span class="section-badge">' . $count_review . '</span>
                      </div>';

                if ($count_review > 0) {
                    renderDocumentTable($results['to_review'], $nrp, $state, $tipe, 'reviewer');
                    mysqli_free_result($results['to_review']);
                } else {
                    echo '<div class="empty-state">
                              <span class="glyphicon glyphicon-ok-circle" style="font-size: 48px; color: #5cb85c;"></span>
                              <h4>All Clear!</h4>
                              <p>No documents waiting for your review.</p>
                          </div>';
                }

                // My Documents Section
                echo '<div class="section-header my-docs">
                          <h3><span class="glyphicon glyphicon-folder-open"></span> My Documents</h3>
                          <span class="section-badge">' . $count_my . '</span>
                      </div>';

                if ($count_my > 0) {
                    renderDocumentTable($results['my_documents'], $nrp, $state, $tipe, 'originator');
                    mysqli_free_result($results['my_documents']);
                } else {
                    echo '<div class="empty-state">
                              <span class="glyphicon glyphicon-folder-open" style="font-size: 48px; color: #ccc;"></span>
                              <h4>No Documents</h4>
                              <p>You haven\'t created any documents yet.</p>
                          </div>';
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error displaying documents: " . $e->getMessage());
        echo '<div class="alert alert-danger">
                  <strong>Error:</strong> Unable to load documents. Please try again later.
              </div>';
    }
}
?>

<script>
    $(document).ready(function() {
        // Upload file modal
        $(document).on('click', '.upload-file', function() {
            var drf = $(this).data('id');
            var nodoc = $(this).data('nodoc');
            var type = $(this).data('type');
            var lama = $(this).data('lama');
            var title = $(this).data('title');

            $("#myModal #drf").val(drf);
            $("#myModal #nodoc").val(nodoc);
            $("#myModal #type").val(type);
            $("#myModal #lama").val(lama);
            $("#myModal #title").val(title);
        });

        // Secure file modal
        $(document).on('click', '.sec-file', function() {
            var drf = $(this).data('id');
            var lama = $(this).data('lama');
            var type = $(this).data('type');
            var rev = $(this).data('rev');
            var status = $(this).data('status');

            $("#myModal2 #drf").val(drf);
            $("#myModal2 #lama").val(lama);
            $("#myModal2 #type").val(type);
            $("#myModal2 #rev").val(rev);
            $("#myModal2 #status").val(status);
        });

        // Notification bell
        $('#notificationBell').click(function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('#notificationPanel').fadeToggle(200);
        });

        // Close notification panel when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('#notificationPanel, #notificationBell').length) {
                $('#notificationPanel').fadeOut(200);
            }
        });

        // Prevent notification panel clicks from closing it
        $('#notificationPanel').click(function(e) {
            e.stopPropagation();
        });
    });
</script>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="uploadModalLabel">Upload Document</h4>
            </div>
            <form name="ganti_doc" method="POST" action="ganti_doc.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="drf" id="drf" class="form-control">
                    <input type="hidden" name="nodoc" id="nodoc" class="form-control">
                    <input type="hidden" name="type" id="type" class="form-control">
                    <input type="hidden" name="title" id="title" class="form-control">
                    <input type="hidden" name="lama" id="lama" class="form-control">

                    <div class="form-group">
                        <label>File Document (.pdf/.xlsx):</label>
                        <input type="file" name="baru" class="form-control" required accept=".pdf,.xlsx">
                        <small class="help-block">Maximum file size: 10MB</small>
                    </div>

                    <div class="form-group">
                        <label>File Master (.docx/.xlsx):</label>
                        <input type="file" name="masterbaru" class="form-control" accept=".docx,.xlsx">
                        <small class="help-block">Optional - Maximum file size: 10MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="upload" class="btn btn-primary">
                        <span class="glyphicon glyphicon-upload"></span> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="secureModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="secureModalLabel">Secure Document</h4>
            </div>
            <form name="secure_doc" method="POST" action="process.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="drf" id="drf" class="form-control">
                    <input type="hidden" name="rev" id="rev" class="form-control">
                    <input type="hidden" name="type" id="type" class="form-control">
                    <input type="hidden" name="status" id="status" class="form-control">

                    <div class="form-group">
                        <label>Upload Secure File:</label>
                        <input type="file" name="baru" class="form-control" required accept=".pdf">
                        <small class="help-block">Only PDF files - Maximum file size: 10MB</small>
                    </div>

                    <div class="alert alert-info">
                        <strong>Note:</strong> This will secure the approved document and make it available for distribution.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="upload" class="btn btn-primary">
                        <span class="glyphicon glyphicon-lock"></span> Secure & Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer if it exists
if (file_exists('footer.php')) {
    include 'footer.php';
}
?>