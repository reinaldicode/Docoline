<?php
/**
 * DEBUG HELPER untuk Document Management System
 * Gunakan file ini untuk troubleshooting masalah dokumen
 * 
 * Cara pakai: 
 * 1. Upload file ini ke root directory
 * 2. Akses: http://192.168.132.15/document/debug_document.php?drf=123
 * 3. Ganti 123 dengan no_drf dokumen yang bermasalah
 */

include 'koneksi.php';

// Ambil DRF dari URL
$drf = isset($_GET['drf']) ? (int)$_GET['drf'] : 0;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Document Debug Helper</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .debug-section { margin-bottom: 30px; }
        .status-review { background-color: #d9edf7; }
        .status-pending { background-color: #fcf8e3; }
        .status-approved { background-color: #dff0d8; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <h1><span class="glyphicon glyphicon-wrench"></span> Document Debug Helper</h1>
    <hr>

    <?php if($drf == 0): ?>
    
    <!-- FORM INPUT DRF -->
    <div class="well">
        <h3>Enter Document DRF Number</h3>
        <form method="GET" class="form-inline">
            <div class="form-group">
                <label>DRF Number:</label>
                <input type="number" name="drf" class="form-control" placeholder="e.g., 12345" required>
            </div>
            <button type="submit" class="btn btn-primary">Debug Document</button>
        </form>
    </div>

    <!-- LIST DOKUMEN PENDING -->
    <div class="debug-section">
        <h3>Recent Pending Documents</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>DRF</th>
                    <th>No Doc</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql_pending = "SELECT no_drf, no_doc, title, doc_type, status 
                           FROM docu 
                           WHERE status='Pending' 
                           ORDER BY no_drf DESC 
                           LIMIT 20";
            $res_pending = mysqli_query($link, $sql_pending);
            
            if(mysqli_num_rows($res_pending) > 0) {
                while($doc = mysqli_fetch_assoc($res_pending)) {
                    echo "<tr class='status-pending'>";
                    echo "<td>{$doc['no_drf']}</td>";
                    echo "<td>{$doc['no_doc']}</td>";
                    echo "<td>".substr($doc['title'], 0, 50)."...</td>";
                    echo "<td>{$doc['doc_type']}</td>";
                    echo "<td><span class='label label-warning'>{$doc['status']}</span></td>";
                    echo "<td><a href='?drf={$doc['no_drf']}' class='btn btn-xs btn-info'>Debug</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No pending documents found</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    
    <!-- DEBUG DOKUMEN SPESIFIK -->
    <div class="alert alert-info">
        <strong>Debugging Document DRF: <?php echo $drf; ?></strong>
        <a href="?" class="btn btn-xs btn-default pull-right">Back to List</a>
    </div>

    <?php
    // 1. CEK DATA DOKUMEN
    $sql_doc = "SELECT * FROM docu WHERE no_drf='$drf'";
    $res_doc = mysqli_query($link, $sql_doc);
    $doc_data = mysqli_fetch_assoc($res_doc);
    
    if(!$doc_data) {
        echo "<div class='alert alert-danger'>Document not found!</div>";
        exit;
    }
    ?>

    <!-- INFORMASI DOKUMEN -->
    <div class="debug-section">
        <h3><span class="glyphicon glyphicon-file"></span> Document Information</h3>
        <table class="table table-bordered">
            <tr>
                <td width="200"><strong>DRF Number</strong></td>
                <td><?php echo $doc_data['no_drf']; ?></td>
            </tr>
            <tr>
                <td><strong>No Document</strong></td>
                <td><?php echo $doc_data['no_doc']; ?></td>
            </tr>
            <tr>
                <td><strong>Title</strong></td>
                <td><?php echo $doc_data['title']; ?></td>
            </tr>
            <tr>
                <td><strong>Type</strong></td>
                <td><span class="label label-info"><?php echo $doc_data['doc_type']; ?></span></td>
            </tr>
            <tr class="<?php echo 'status-'.strtolower($doc_data['status']); ?>">
                <td><strong>Status</strong></td>
                <td>
                    <span class="label label-<?php 
                        echo ($doc_data['status']=='Review' ? 'info' : 
                             ($doc_data['status']=='Pending' ? 'warning' : 'success')); 
                    ?>">
                        <?php echo $doc_data['status']; ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>User ID (Originator)</strong></td>
                <td><?php echo $doc_data['user_id']; ?></td>
            </tr>
            <tr>
                <td><strong>Upload Date</strong></td>
                <td><?php echo $doc_data['tgl_upload']; ?></td>
            </tr>
            <tr>
                <td><strong>File</strong></td>
                <td><?php echo $doc_data['file']; ?></td>
            </tr>
        </table>
    </div>

    <!-- INFORMASI APPROVER -->
    <div class="debug-section">
        <h3><span class="glyphicon glyphicon-user"></span> Approvers Status</h3>
        <?php
        $sql_approvers = "SELECT r.*, u.name, u.email, u.state 
                         FROM rev_doc r
                         LEFT JOIN users u ON r.nrp = u.username
                         WHERE r.id_doc = '$drf'
                         ORDER BY r.id";
        $res_approvers = mysqli_query($link, $sql_approvers);
        ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>NRP</th>
                    <th>Name</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th>Approve Date</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $no = 1;
            $total_approvers = 0;
            $review_count = 0;
            $pending_count = 0;
            $approved_count = 0;
            
            while($approver = mysqli_fetch_assoc($res_approvers)) {
                $total_approvers++;
                
                if($approver['status'] == 'Review') $review_count++;
                elseif($approver['status'] == 'Pending') $pending_count++;
                elseif($approver['status'] == 'Approved') $approved_count++;
                
                $status_class = 'status-'.strtolower($approver['status']);
                echo "<tr class='$status_class'>";
                echo "<td>{$no}</td>";
                echo "<td>{$approver['nrp']}</td>";
                echo "<td>{$approver['name']}</td>";
                echo "<td>{$approver['reviewer_section']}</td>";
                echo "<td><span class='label label-";
                echo ($approver['status']=='Review' ? 'info' : 
                     ($approver['status']=='Pending' ? 'warning' : 'success'));
                echo "'>{$approver['status']}</span></td>";
                echo "<td>{$approver['tgl_approve']}</td>";
                echo "<td>".substr($approver['reason'], 0, 50)."</td>";
                echo "</tr>";
                $no++;
            }
            
            if($total_approvers == 0) {
                echo "<tr><td colspan='7' class='text-center text-danger'>
                      <strong>⚠ WARNING: No approvers found for this document!</strong>
                      </td></tr>";
            }
            ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4"><strong>Summary:</strong></td>
                    <td colspan="3">
                        Total: <?php echo $total_approvers; ?> | 
                        <span class="label label-info">Review: <?php echo $review_count; ?></span>
                        <span class="label label-warning">Pending: <?php echo $pending_count; ?></span>
                        <span class="label label-success">Approved: <?php echo $approved_count; ?></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- DIAGNOSIS -->
    <div class="debug-section">
        <h3><span class="glyphicon glyphicon-warning-sign"></span> Diagnosis & Issues</h3>
        <?php
        $issues = array();
        
        // Cek 1: Status dokumen vs approver tidak sinkron
        if($doc_data['status'] == 'Review' && $pending_count > 0) {
            $issues[] = "⚠ Document status is 'Review' but {$pending_count} approver(s) have 'Pending' status. This will cause document to disappear from approver's view.";
        }
        
        if($doc_data['status'] == 'Pending' && $review_count > 0) {
            $issues[] = "⚠ Document status is 'Pending' but {$review_count} approver(s) have 'Review' status. Status inconsistency detected.";
        }
        
        // Cek 2: Tidak ada approver
        if($total_approvers == 0) {
            $issues[] = "❌ CRITICAL: No approvers assigned to this document!";
        }
        
        // Cek 3: Semua approver approved tapi dokumen belum
        if($total_approvers > 0 && $approved_count == $total_approvers && $doc_data['status'] != 'Approved') {
            $issues[] = "⚠ All approvers have approved but document status is still '{$doc_data['status']}'";
        }
        
        if(count($issues) > 0) {
            echo "<div class='alert alert-warning'>";
            echo "<strong>Issues Found:</strong><ul>";
            foreach($issues as $issue) {
                echo "<li>{$issue}</li>";
            }
            echo "</ul></div>";
        } else {
            echo "<div class='alert alert-success'>
                  <span class='glyphicon glyphicon-ok'></span> 
                  No issues detected. Document status is consistent.
                  </div>";
        }
        ?>
    </div>

    <!-- QUICK FIX ACTIONS -->
    <?php if(count($issues) > 0): ?>
    <div class="debug-section">
        <h3><span class="glyphicon glyphicon-wrench"></span> Quick Fix Options</h3>
        <div class="well">
            <h4>Fix Inconsistent Status</h4>
            <p>If document status and approver status are not synchronized, run these queries:</p>
            
            <div class="panel panel-default">
                <div class="panel-heading">SQL Query to Fix</div>
                <div class="panel-body">
                    <pre><?php
echo "-- Reset ALL approvers to Review status\n";
echo "UPDATE rev_doc \n";
echo "SET status='Review', reason='', tgl_approve='-' \n";
echo "WHERE id_doc={$drf};\n\n";

echo "-- Update document status to Review\n";
echo "UPDATE docu \n";
echo "SET status='Review' \n";
echo "WHERE no_drf={$drf};";
                    ?></pre>
                    
                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to reset this document to Review status?');">
                        <input type="hidden" name="action" value="fix_status">
                        <input type="hidden" name="drf" value="<?php echo $drf; ?>">
                        <button type="submit" class="btn btn-warning">
                            <span class="glyphicon glyphicon-refresh"></span> 
                            Execute Fix (Reset to Review)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- QUERY TESTING -->
    <div class="debug-section">
        <h3><span class="glyphicon glyphicon-search"></span> Query Testing</h3>
        
        <div class="panel panel-info">
            <div class="panel-heading">Test Originator Query</div>
            <div class="panel-body">
                <pre><?php
$originator_nrp = $doc_data['user_id'];
$doc_type = $doc_data['doc_type'];

$test_query = "SELECT DISTINCT docu.* FROM docu 
WHERE user_id='{$originator_nrp}' 
AND (docu.status='Review' OR docu.status='Pending') 
AND doc_type='{$doc_type}'
ORDER BY no_drf DESC";

echo $test_query;
                ?></pre>
                
                <?php
                $test_res = mysqli_query($link, $test_query);
                $found = false;
                while($row = mysqli_fetch_assoc($test_res)) {
                    if($row['no_drf'] == $drf) {
                        $found = true;
                        break;
                    }
                }
                ?>
                
                <div class="alert alert-<?php echo $found ? 'success' : 'danger'; ?>">
                    <?php if($found): ?>
                        ✓ Document WILL BE VISIBLE to Originator
                    <?php else: ?>
                        ✗ Document WILL NOT BE VISIBLE to Originator
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="panel panel-info">
            <div class="panel-heading">Test Approver Query</div>
            <div class="panel-body">
                <?php
                // Get first approver NRP
                $sql_first_approver = "SELECT nrp FROM rev_doc WHERE id_doc='$drf' LIMIT 1";
                $res_first = mysqli_query($link, $sql_first_approver);
                $first_app = mysqli_fetch_assoc($res_first);
                $approver_nrp = $first_app['nrp'] ?? 'N/A';
                ?>
                
                <pre><?php
$test_query2 = "SELECT DISTINCT docu.* 
FROM docu 
INNER JOIN rev_doc ON docu.no_drf = rev_doc.id_doc 
WHERE rev_doc.nrp='{$approver_nrp}' 
AND docu.status='Review' 
AND rev_doc.status='Review' 
AND doc_type='{$doc_type}'
ORDER BY docu.no_drf DESC";

echo $test_query2;
                ?></pre>
                
                <?php
                if($approver_nrp != 'N/A') {
                    $test_res2 = mysqli_query($link, $test_query2);
                    $found2 = false;
                    while($row2 = mysqli_fetch_assoc($test_res2)) {
                        if($row2['no_drf'] == $drf) {
                            $found2 = true;
                            break;
                        }
                    }
                    ?>
                    
                    <div class="alert alert-<?php echo $found2 ? 'success' : 'danger'; ?>">
                        <?php if($found2): ?>
                            ✓ Document WILL BE VISIBLE to Approver (<?php echo $approver_nrp; ?>)
                        <?php else: ?>
                            ✗ Document WILL NOT BE VISIBLE to Approver (<?php echo $approver_nrp; ?>)
                        <?php endif; ?>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-danger">✗ No approver found to test</div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- NAVIGATION -->
    <div class="text-center">
        <a href="my_doc.php" class="btn btn-primary">
            <span class="glyphicon glyphicon-arrow-left"></span> Back to My Documents
        </a>
        <a href="edit_doc.php?drf=<?php echo $drf; ?>" class="btn btn-warning">
            <span class="glyphicon glyphicon-pencil"></span> Edit Document
        </a>
    </div>

    <?php endif; ?>

</div>

<?php
// Handle POST request untuk fix
if(isset($_POST['action']) && $_POST['action'] == 'fix_status') {
    $drf_fix = (int)$_POST['drf'];
    
    // Reset approvers
    $fix1 = "UPDATE rev_doc SET status='Review', reason='', tgl_approve='-' WHERE id_doc='$drf_fix'";
    mysqli_query($link, $fix1);
    
    // Reset document
    $fix2 = "UPDATE docu SET status='Review' WHERE no_drf='$drf_fix'";
    mysqli_query($link, $fix2);
    
    echo "<script>
            alert('Status has been reset to Review for document DRF {$drf_fix}');
            window.location.href = '?drf={$drf_fix}';
          </script>";
}
?>

</body>
</html>