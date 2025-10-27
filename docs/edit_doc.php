<?php
include('header.php');
include('koneksi.php');
include_once('document_config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$drf = 0;
if (isset($_GET['drf']) && is_numeric($_GET['drf'])) {
    $drf = (int)$_GET['drf'];
} elseif (isset($_POST['drf']) && is_numeric($_POST['drf'])) {
    $drf = (int)$_POST['drf'];
}

if ($drf == 0) {
    die("Error: No valid DRF specified");
}

$docTypes = DocumentConfig::getFlattenedDocumentTypes();

// ============================================================================
// HANDLE UPDATE STATUS TO REVIEW (Tombol "Selesai")
// ============================================================================
if (isset($_POST['update_status']) && $_POST['update_status'] == '1' && isset($_POST['action']) && $_POST['action'] == 'finish') {
    
    if (!isset($_POST['drf']) || !is_numeric($_POST['drf'])) {
        die("Error: Invalid DRF for update");
    }
    
    $drf_update = (int)$_POST['drf'];
    
    if ($drf_update == 0) {
        die("Error: DRF cannot be zero");
    }
    
    // ========== STEP 1: CEK DATA SEBELUM UPDATE ==========
    $check_before = "SELECT id, nrp, status, tgl_approve, reason FROM rev_doc WHERE id_doc=$drf_update";
    $res_before = mysqli_query($link, $check_before);
    
    $debug_info = "<!-- DEBUG BEFORE UPDATE:\n";
    if ($res_before) {
        while ($row = mysqli_fetch_assoc($res_before)) {
            $debug_info .= "ID: {$row['id']}, NRP: {$row['nrp']}, Status: {$row['status']}, Date: {$row['tgl_approve']}\n";
        }
    }
    $debug_info .= "-->\n";
    echo $debug_info;
    
    // ========== STEP 2: Update status dokumen ke Review ==========
    $update_status = "UPDATE docu SET status='Review' WHERE no_drf=$drf_update";
    $result_update = mysqli_query($link, $update_status);
    
    if (!$result_update) {
        die("Error updating docu: " . mysqli_error($link) . "<br>Query: " . $update_status);
    }
    
    // ========== STEP 3: RESET APPROVER DENGAN CARA YANG BENAR ==========
    $reset_approvers = "UPDATE rev_doc 
                        SET status='Review', 
                            reason='', 
                            tgl_approve='-' 
                        WHERE id_doc=$drf_update 
                        AND status='Pending'";
    
    echo "<!-- RESET QUERY: " . $reset_approvers . " -->\n";
    
    $result_reset = mysqli_query($link, $reset_approvers);
    
    if (!$result_reset) {
        echo "<!-- Bulk update gagal, coba per-record -->\n";
        
        $get_pending = "SELECT id FROM rev_doc WHERE id_doc=$drf_update AND status='Pending'";
        $res_pending_ids = mysqli_query($link, $get_pending);
        
        $success_count = 0;
        $error_count = 0;
        
        if ($res_pending_ids && mysqli_num_rows($res_pending_ids) > 0) {
            while ($row_id = mysqli_fetch_assoc($res_pending_ids)) {
                $id = $row_id['id'];
                $update_single = "UPDATE rev_doc 
                                  SET status='Review', reason='', tgl_approve='-' 
                                  WHERE id=$id";
                
                if (mysqli_query($link, $update_single)) {
                    $success_count++;
                    echo "<!-- Updated record ID: $id -->\n";
                } else {
                    $error_count++;
                    echo "<!-- Failed to update record ID: $id - " . mysqli_error($link) . " -->\n";
                }
            }
        }
        
        $affected_rows = $success_count;
        
        if ($error_count > 0) {
            die("Error: Failed to update $error_count approver(s). Please check database permissions.");
        }
    } else {
        $affected_rows = mysqli_affected_rows($link);
    }
    
    echo "<!-- Affected rows: $affected_rows -->\n";
    
    // ========== STEP 4: CEK HASIL UPDATE ==========
    $check_after = "SELECT id, nrp, status, tgl_approve, reason FROM rev_doc WHERE id_doc=$drf_update";
    $res_after = mysqli_query($link, $check_after);
    
    $debug_after = "<!-- DEBUG AFTER UPDATE:\n";
    if ($res_after) {
        while ($row = mysqli_fetch_assoc($res_after)) {
            $debug_after .= "ID: {$row['id']}, NRP: {$row['nrp']}, Status: {$row['status']}, Date: {$row['tgl_approve']}\n";
        }
    }
    $debug_after .= "-->\n";
    echo $debug_after;
    
    // ========== STEP 5: Get document info ==========
    $get_doc = mysqli_query($link, "SELECT no_doc, title, doc_type FROM docu WHERE no_drf=$drf_update");
    
    if (!$get_doc) {
        die("Error getting document: " . mysqli_error($link));
    }
    
    $doc_data = mysqli_fetch_array($get_doc);
    if (!$doc_data) {
        die("Error: Document not found for DRF $drf_update");
    }
    
    $redirect_type = isset($doc_data['doc_type']) ? $doc_data['doc_type'] : '';
    
    // ========== STEP 6: Get approvers untuk notifikasi ==========
    $sql_pending_approvers = "SELECT DISTINCT u.name, u.email, u.username, rd.status
                              FROM rev_doc rd
                              JOIN users u ON u.username = rd.nrp 
                              WHERE rd.id_doc = $drf_update AND rd.status='Review'";
    
    $res_pending = mysqli_query($link, $sql_pending_approvers);
    $approvers_to_notify = [];
    
    if ($res_pending && mysqli_num_rows($res_pending) > 0) {
        while ($data = mysqli_fetch_assoc($res_pending)) {
            $approvers_to_notify[] = $data;
        }
    }
    
    $sql_still_approved = "SELECT COUNT(*) as approved_count 
                           FROM rev_doc 
                           WHERE id_doc=$drf_update AND status='Approved'";
    $res_approved = mysqli_query($link, $sql_still_approved);
    $row_approved = mysqli_fetch_assoc($res_approved);
    $approved_count = $row_approved['approved_count'];
    
    // ========== STEP 7: Send email (optional) ==========
    if (count($approvers_to_notify) > 0) {
        if (file_exists('PHPMailer/PHPMailerAutoload.php')) {
            require 'PHPMailer/PHPMailerAutoload.php';
            $mail = new PHPMailer();
            
            try {
                $mail->IsSMTP();
                include 'smtp.php';
                
                $mail->From = 'dc_admin@ssi.sharp-world.com';
                $mail->FromName = 'Admin Document Online System';

                foreach ($approvers_to_notify as $recipient) {
                    $mail->addAddress($recipient['email'], $recipient['name']);
                }
                
                $mail->IsHTML(true);
                $mail->Subject = "Document Updated - Re-Review Required: " . $doc_data['no_doc'];
                $mail->Body = "Attention Mr./Mrs. Reviewer,<br/><br/>" .
                              "The following document has been updated and requires your re-review:" .
                              "<br/><br/><b>No. Document:</b> " . htmlspecialchars($doc_data['no_doc']) .
                              "<br/><b>Title:</b> " . htmlspecialchars($doc_data['title']) .
                              "<br/><br/><b>Note:</b> You previously suspended this document." .
                              ($approved_count > 0 ? "<br/><b>Good News:</b> $approved_count approver(s) who already approved do NOT need to approve again!" : "") .
                              "<br/><br/>Please login into <a href='http://192.168.132.15/document'>Document Online System</a>. Thank You.";

                $mail->send();
            } catch (Exception $e) {
                error_log("Email send failed: " . $e->getMessage());
            }
        }
    }
    
    // ========== STEP 8: Success message ==========
    $message = "✓ Status berhasil diupdate!\\n\\n";
    
    if ($affected_rows > 0) {
        $message .= "✓ Updated $affected_rows approver(s) from Pending to Review\\n";
    } else {
        $message .= "⚠ No pending approvers to update\\n";
    }
    
    if (count($approvers_to_notify) > 0) {
        $message .= "\\nApprovers that need to re-review:\\n";
        foreach ($approvers_to_notify as $app) {
            $message .= "  - {$app['name']} ({$app['username']})\\n";
        }
    }
    
    if ($approved_count > 0) {
        $message .= "\\n✓ $approved_count approver(s) who already approved do NOT need to approve again";
    }
    
    ?>
    <script language='javascript'>
        alert('<?php echo $message; ?>');
        document.location='my_doc.php?tipe=<?php echo urlencode($redirect_type); ?>&submit=Show';
    </script>
    <?php
    exit;
}

// ========== HANDLE FORM SUBMISSION (Save Changes) ==========
if (isset($_POST['submit']) && $_POST['submit'] == 'save') {
    
    if (!isset($_POST['drf']) || !is_numeric($_POST['drf'])) {
        die("Error: Invalid DRF in form submission");
    }
    
    $drf_post = (int)$_POST['drf'];

    $nodoc = mysqli_real_escape_string($link, $_POST['nodoc']);
    $norev = mysqli_real_escape_string($link, $_POST['norev']);
    $revto = mysqli_real_escape_string($link, $_POST['revto']);
    $cat = mysqli_real_escape_string($link, $_POST['cat']);
    $type = mysqli_real_escape_string($link, $_POST['type']);
    $section = mysqli_real_escape_string($link, $_POST['section']);
    $device = mysqli_real_escape_string($link, $_POST['device']);
    $process = mysqli_real_escape_string($link, $_POST['process']);
    $title = mysqli_real_escape_string($link, $_POST['title']);
    $desc = mysqli_real_escape_string($link, $_POST['desc']);

    $iso = 0;
    if (isset($_POST['iso']) && $_POST['iso'] !== '' && $_POST['iso'] !== null) {
        $iso = intval($_POST['iso']);
    }

    $hist = mysqli_real_escape_string($link, $_POST['hist']);

    $sql = "UPDATE docu SET 
            no_doc='$nodoc', 
            no_rev='$norev', 
            rev_to='$revto', 
            category='$cat', 
            doc_type='$type', 
            section='$section', 
            device='$device',
            process='$process',
            title='$title',
            descript='$desc', 
            iso=$iso, 
            history='$hist'
            WHERE no_drf=$drf_post";

    $res = mysqli_query($link, $sql);

    if (!$res) {
        die("Update failed: " . mysqli_error($link));
    }

    mysqli_query($link, "DELETE FROM rel_doc WHERE no_drf=$drf_post");

    if (isset($_POST["rel"]) && is_array($_POST["rel"])) {
        foreach($_POST["rel"] as $no_doc_rel) {
            $no_doc_rel = trim($no_doc_rel);
            
            if (!empty($no_doc_rel)) {
                $no_doc_escaped = mysqli_real_escape_string($link, $no_doc_rel);
                mysqli_query($link, "INSERT INTO rel_doc(no_drf, no_doc) VALUES ($drf_post, '$no_doc_escaped')"); 
            }
        }
    }

    $showModal = true;
    $drf = $drf_post;
}

// ========== DISPLAY FORM ==========
$sql="SELECT * FROM docu WHERE no_drf=$drf";
$res=mysqli_query($link, $sql);

if (!$res) {
    die("Error loading document: " . mysqli_error($link));
}

while($data = mysqli_fetch_array($res)) 
{
    // Get approver status
    $sql_status = "SELECT 
                   SUM(CASE WHEN status='Approved' THEN 1 ELSE 0 END) as approved_count,
                   SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) as pending_count,
                   SUM(CASE WHEN status='Review' THEN 1 ELSE 0 END) as review_count,
                   COUNT(*) as total_count
                   FROM rev_doc WHERE id_doc=$drf";
    $res_status = mysqli_query($link, $sql_status);
    $status_data = mysqli_fetch_assoc($res_status);
    
    // Get detail approver list
    $sql_approvers = "SELECT rd.id, rd.nrp, u.name, rd.status, rd.tgl_approve, rd.reason
                      FROM rev_doc rd
                      LEFT JOIN users u ON u.username = rd.nrp
                      WHERE rd.id_doc=$drf
                      ORDER BY rd.status DESC";
    $res_approvers = mysqli_query($link, $sql_approvers);
?>

<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-7 well well-lg">
<h2>Edit Document</h2>

<div class="alert alert-warning" style="font-size: 11px;">
    <strong>DEBUG INFO (DRF: <?php echo $drf; ?>):</strong><br/>
    <table class="table table-condensed table-bordered" style="margin-top: 10px;">
        <tr style="background: #f5f5f5;">
            <th>ID</th>
            <th>Approver</th>
            <th>NRP</th>
            <th>Status</th>
            <th>Tgl Approve</th>
            <th>Reason</th>
        </tr>
        <?php 
        if ($res_approvers && mysqli_num_rows($res_approvers) > 0) {
            while($app = mysqli_fetch_assoc($res_approvers)) {
                $badge_class = $app['status'] == 'Approved' ? 'success' : ($app['status'] == 'Pending' ? 'warning' : 'info');
                ?>
                <tr>
                    <td><?php echo $app['id']; ?></td>
                    <td><?php echo htmlspecialchars($app['name'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($app['nrp']); ?></td>
                    <td><span class="label label-<?php echo $badge_class; ?>"><?php echo $app['status']; ?></span></td>
                    <td><?php echo htmlspecialchars($app['tgl_approve']); ?></td>
                    <td><?php echo htmlspecialchars(substr($app['reason'], 0, 30)); ?><?php echo strlen($app['reason']) > 30 ? '...' : ''; ?></td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='6'>No approvers found</td></tr>";
        }
        ?>
    </table>
</div>

<?php if ($status_data['approved_count'] > 0 || $status_data['pending_count'] > 0): ?>
<div class="alert alert-info">
    <span class="glyphicon glyphicon-info-sign"></span>
    <strong>Approval Status:</strong><br/>
    <ul>
        <?php if ($status_data['approved_count'] > 0): ?>
        <li>✓ <strong><?php echo $status_data['approved_count']; ?></strong> approver(s) already approved</li>
        <?php endif; ?>
        <?php if ($status_data['pending_count'] > 0): ?>
        <li>⚠ <strong><?php echo $status_data['pending_count']; ?></strong> approver(s) suspended (need re-review)</li>
        <?php endif; ?>
        <?php if ($status_data['review_count'] > 0): ?>
        <li>⏳ <strong><?php echo $status_data['review_count']; ?></strong> approver(s) pending review</li>
        <?php endif; ?>
    </ul>
</div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <table class="table">
        <input type="hidden" name="drf" value="<?php echo $drf; ?>">
        
        <tr>
            <td>No. Document</td>
            <td>:</td>
            <td><input type="text" class="form-control" name="nodoc" value="<?php echo htmlspecialchars($data['no_doc']); ?>"></td>
        </tr>
        <tr>
            <td>No. Revision</td>
            <td>:</td>
            <td><input type="text" class="form-control" name="norev" value="<?php echo htmlspecialchars($data['no_rev']); ?>"></td>
        </tr>
        <tr>
            <td>Review To</td>
            <td>:</td>
            <td>
                <select name="revto" class="form-control">
                    <option value="-">--- Select ---</option>
                    <option value="Issue" <?php if ($data['rev_to']=="Issue") echo 'selected'; ?>>Issue</option>
                    <option value="Revision" <?php if ($data['rev_to']=="Revision") echo 'selected'; ?>>Revision</option>
                    <option value="Cancel" <?php if ($data['rev_to']=="Cancel") echo 'selected'; ?>>Cancel</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Category</td>
            <td>:</td>
            <td>
                <select name="cat" class="form-control">
                    <option value="-">--- Select ---</option>
                    <option value="Internal" <?php if ($data['category']=="Internal") echo 'selected'; ?>>Internal</option>
                    <option value="External" <?php if ($data['category']=="External") echo 'selected'; ?>>External</option>
                </select>
            </td>
        </tr>
        
        <tr>
            <td>Document Type</td>
            <td>:</td>
            <td>
                <select name="type" class="form-control">
                    <option value="-">--- Select ---</option>
                    <?php 
                    if (is_array($docTypes)) {
                        foreach ($docTypes as $type): 
                            $type_esc = htmlspecialchars($type);
                            $selected = ($data['doc_type'] == $type) ? 'selected' : '';
                    ?>
                    <option value="<?php echo $type_esc; ?>" <?php echo $selected; ?>>
                        <?php echo $type_esc; ?>
                    </option>
                    <?php 
                        endforeach; 
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Section</td>
            <td>:</td>
            <td>
                <?php 
                $sect="SELECT * FROM section ORDER BY id_section";
                $sql_sect=mysqli_query($link, $sect);
                ?>
                <select name="section" class="form-control">
                    <option value="-">--- Select ---</option>
                    <?php while($data_sec = mysqli_fetch_array($sql_sect)): ?>
                    <option value="<?php echo htmlspecialchars($data_sec['id_section']); ?>" 
                            <?php if ($data['section']==$data_sec['id_section']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($data_sec['sect_name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Device</td>
            <td>:</td>
            <td>
                <?php 
                $dev="SELECT * FROM device WHERE status='Aktif' ORDER BY group_dev";
                $sql_dev=mysqli_query($link, $dev);
                ?>
                <select name="device" class="form-control">
                    <option value="-">--- Select ---</option>
                    <?php while($data_dev = mysqli_fetch_array($sql_dev)): ?>
                    <option value="<?php echo htmlspecialchars($data_dev['name']); ?>" 
                            <?php if ($data['device']==$data_dev['name']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($data_dev['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Process</td>
            <td>:</td>
            <td>
                <?php 
                $proc="SELECT * FROM process ORDER BY proc_name";
                $sql_proc=mysqli_query($link, $proc);
                ?>
                <select name="process" class="form-control">
                    <option value="-">--- Select ---</option>
                    <?php while($data_proc = mysqli_fetch_array($sql_proc)): ?>
                    <option value="<?php echo htmlspecialchars($data_proc['proc_name']); ?>" 
                            <?php if ($data['process']==$data_proc['proc_name']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($data_proc['proc_name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Doc. Title</td>
            <td>:</td>
            <td><input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($data['title']); ?>"></td>
        </tr>
        <tr>
            <td>Doc. Description</td>
            <td>:</td>
            <td><textarea class="form-control" name="desc" rows="5"><?php echo htmlspecialchars($data['descript']); ?></textarea></td>
        </tr>
        <tr>
            <td>Requirement</td>
            <td>:</td>
            <td>
                <input type="radio" name="iso" value="1" <?php if (isset($data['iso']) && $data['iso']==1) echo "checked"; ?>> ISO 9001<br/>
                <input type="radio" name="iso" value="2" <?php if (isset($data['iso']) && $data['iso']==2) echo "checked"; ?>> ISO 14001<br/>
                <input type="radio" name="iso" value="3" <?php if (isset($data['iso']) && $data['iso']==3) echo "checked"; ?>> OHSAS<br/>
                <input type="radio" name="iso" value="0" <?php if (!isset($data['iso']) || $data['iso']==0) echo "checked"; ?>> None
            </td>
        </tr>
        <tr>
            <td>Related Doc</td>
            <td>:</td>
            <td>
                <?php 
                // Get existing related documents
                $sql_rel = "SELECT no_doc FROM rel_doc WHERE no_drf=$drf";
                $res_rel = mysqli_query($link, $sql_rel);
                $existing_rel = array();
                if ($res_rel) {
                    while($row_rel = mysqli_fetch_array($res_rel)) {
                        $existing_rel[] = $row_rel['no_doc'];
                    }
                }
                
                for($i=0; $i<5; $i++): 
                    $value = isset($existing_rel[$i]) ? htmlspecialchars($existing_rel[$i]) : '';
                ?>
                <input type="text" class="form-control" name="rel[]" value="<?php echo $value; ?>" placeholder="Related Doc <?php echo $i+1; ?>">
                <?php endfor; ?>
            </td>
        </tr>
        <tr>
            <td>Revision History</td>
            <td>:</td>
            <td><textarea class="form-control" name="hist" rows="5"><?php echo htmlspecialchars($data['history']); ?></textarea></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>
                <button type="submit" name="submit" value="save" class="btn btn-success">
                    <span class="glyphicon glyphicon-floppy-disk"></span> Save Changes
                </button>
                <a href="my_doc.php?tipe=<?php echo urlencode($data['doc_type']); ?>&submit=Show" class="btn btn-default">
                    Cancel
                </a>
            </td>
        </tr>
    </table>
</form>

</div>
</div>

<div class="modal fade" id="actionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Pilih Aksi Selanjutnya</h4>
            </div>
            <div class="modal-body">
                <p><strong>✓ Edit berhasil disimpan!</strong></p>
                
                <div class="alert alert-info">
                    <strong>Current Status:</strong>
                    <ul>
                        <li>✓ <?php echo $status_data['approved_count']; ?> approved</li>
                        <li>⚠ <?php echo $status_data['pending_count']; ?> suspended</li>
                        <li>⏳ <?php echo $status_data['review_count']; ?> pending review</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <strong>Pilihan 1: Change Document</strong>
                    <p>Upload file baru. Hanya approver yang suspend yang perlu re-review.</p>
                </div>
                
                <div class="alert alert-success">
                    <strong>Pilihan 2: Selesai</strong>
                    <p>Kembalikan ke status Review tanpa upload. Approver yang suspend akan otomatis bisa review lagi.</p>
                </div>
            </div>
            <div class="modal-footer">
                <form method="GET" action="ganti_doc.php" style="display:inline;">
                    <input type="hidden" name="drf" value="<?php echo $drf; ?>">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($data['doc_type']); ?>">
                    <button type="submit" class="btn btn-warning">
                        <span class="glyphicon glyphicon-upload"></span> Change Document
                    </button>
                </form>
                
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="drf" value="<?php echo $drf; ?>">
                    <input type="hidden" name="action" value="finish">
                    <input type="hidden" name="update_status" value="1">
                    <button type="submit" class="btn btn-success">
                        <span class="glyphicon glyphicon-ok"></span> Selesai (Set to Review)
                    </button>
                </form>
                
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    <?php if (isset($showModal) && $showModal): ?>
    $('#actionModal').modal('show');
    <?php endif; ?>
});
</script>

<?php 
}

?>