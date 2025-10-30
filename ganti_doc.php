<?php
include 'header.php';
include 'koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['upload'])) {
    
    $drf = isset($_POST['drf']) ? (int)$_POST['drf'] : 0;
    $type = isset($_POST['type']) ? mysqli_real_escape_string($link, $_POST['type']) : '';
    $nodoc = isset($_POST['nodoc']) ? mysqli_real_escape_string($link, $_POST['nodoc']) : '';
    $title = isset($_POST['title']) ? mysqli_real_escape_string($link, $_POST['title']) : '';

    if ($drf == 0) {
        die("Error: DRF tidak valid.");
    }

    $nma_file = '';
    $nma_file_master = '';
    
    // --- Proses Upload File Dokumen ---
    if (isset($_FILES['baru']) && $_FILES['baru']['error'] == UPLOAD_ERR_OK) {
        $tmp_file = $_FILES['baru']['tmp_name'];
        $nma_file = basename($_FILES['baru']['name']);
        
        // Sanitize folder name
        $safe_type = preg_replace('/[^A-Za-z0-9 _\-&]/', '', $type);
        $safe_type = str_replace(' ', '_', $safe_type);
        $target_dir = rtrim($safe_type, '/') . '/';
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (!move_uploaded_file($tmp_file, $target_dir . $nma_file)) {
            die("ERROR - File Dokumen gagal di-upload.<br>");
        }
    }

    // --- Proses Upload File Master (Optional) ---
    if (isset($_FILES['masterbaru']) && $_FILES['masterbaru']['error'] == UPLOAD_ERR_OK) {
        $tmp_file_master = $_FILES['masterbaru']['tmp_name'];
        $nma_file_master = basename($_FILES['masterbaru']['name']);
        
        $target_dir_master = 'master/' . rtrim($safe_type, '/') . '/';
        
        if (!is_dir($target_dir_master)) {
            mkdir($target_dir_master, 0777, true);
        }

        if (!move_uploaded_file($tmp_file_master, $target_dir_master . $nma_file_master)) {
            echo "WARNING - File Master gagal di-upload.<br>";
        }
    }

    // --- PERUBAHAN BARU: Cek status dokumen saat ini ---
    $sql_status_check = "SELECT status FROM docu WHERE no_drf = $drf";
    $res_status_check = mysqli_query($link, $sql_status_check);
    $doc_data_check = mysqli_fetch_assoc($res_status_check);
    $current_status = $doc_data_check ? $doc_data_check['status'] : '';
    
    $is_already_approved = ($current_status == 'Approved');
    
    // --- Update Database Dokumen ---
    $query_update_docu = "UPDATE docu SET ";
    
    // HANYA ubah status jika BELUM 'Approved'
    if (!$is_already_approved) {
        $query_update_docu .= "status='Review', ";
    }
    
    // Tambahkan file-file baru ke query
    if (!empty($nma_file)) {
        $query_update_docu .= "file='" . mysqli_real_escape_string($link, $nma_file) . "', ";
    }
    if (!empty($nma_file_master)) {
        // Ikuti pola upload.php - simpan master ke file_asli
        $query_update_docu .= "file_asli='" . mysqli_real_escape_string($link, $nma_file_master) . "', ";
    }
    
    // Hapus koma dan spasi terakhir
    $query_update_docu = rtrim($query_update_docu, ', '); 
    $query_update_docu .= " WHERE no_drf=$drf";
    
    mysqli_query($link, $query_update_docu) or die("Error updating docu: " . mysqli_error($link));

    // --- LOGIKA BERSYARAT ---
    // Jika status BUKAN 'Approved', lanjutkan logika reset approver & kirim email
    if (!$is_already_approved) {
        
        // Reset HANYA approver yang 'Pending'
        $query_reset_rev = "UPDATE rev_doc 
                        SET status='Review', reason='' 
                        WHERE id_doc=$drf AND status='Pending'";
        mysqli_query($link, $query_reset_rev) or die("Error resetting pending approvers: " . mysqli_error($link));

        // Ambil approver yang perlu direview ulang (yang statusnya Pending sebelumnya)
        $sql_pending_approvers = "SELECT DISTINCT u.name, u.email 
                                  FROM rev_doc rd
                                  JOIN users u ON u.username = rd.nrp 
                                  WHERE rd.id_doc = $drf AND rd.status='Review'";
        
        $res_pending = mysqli_query($link, $sql_pending_approvers);
        
        $approvers_to_notify = [];
        if ($res_pending && mysqli_num_rows($res_pending) > 0) {
            while ($data = mysqli_fetch_assoc($res_pending)) {
                $approvers_to_notify[] = $data;
            }
        }

        // Cek berapa approver yang masih Approved
        $sql_still_approved = "SELECT COUNT(*) as approved_count 
                               FROM rev_doc 
                               WHERE id_doc=$drf AND status='Approved'";
        $res_approved = mysqli_query($link, $sql_still_approved);
        $row_approved = mysqli_fetch_assoc($res_approved);
        $approved_count = $row_approved['approved_count'];

        // Kirim Email Notifikasi ke approver yang perlu review ulang
        if (count($approvers_to_notify) > 0) {
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
                $mail->Subject = "Re-Review Required for Revised Document: " . $nodoc;
                $mail->Body = "Attention Mr./Mrs. Reviewer,<br/><br/>" .
                              "The following document has been revised and requires your re-review:".
                              "<br/><br/><b>No. Document:</b> " . htmlspecialchars($nodoc) .
                              "<br/><b>Title:</b> " . htmlspecialchars($title) .
                              ($approved_count > 0 ? "<br/><b>Note:</b> $approved_count approver(s) who already approved do NOT need to approve again." : "") .
                              "<br/><br/>Please login into <a href='http://192.168.132.15/document'>Document Online System</a>. Thank You.";

                if ($mail->send()) {
                    $message = "File changed successfully!\\n\\n";
                    $message .= "✓ Notified " . count($approvers_to_notify) . " approver(s) who need to re-review\\n";
                    $message .= "✓ $approved_count approver(s) do NOT need to approve again\\n";
                    $message .= "\\nStatus changed to Review.";
                    
                    echo "<script>
                            alert('$message');
                            document.location='my_doc.php?tipe=" . urlencode($type) . "&submit=Show';
                          </script>";
                } else {
                    echo "<script>
                            alert('File changed successfully, but email failed to send!\\nError: ".addslashes($mail->ErrorInfo)."');
                            document.location='my_doc.php?tipe=" . urlencode($type) . "&submit=Show';
                          </script>";
                }
            } catch (Exception $e) {
                echo "<script>
                        alert('File changed, but an error occurred during email sending.\\nError: ".addslashes($e->getMessage())."');
                        document.location='my_doc.php?tipe=" . urlencode($type) . "&submit=Show';
                      </script>";
            }
        } else {
            // Tidak ada approver yang perlu review ulang
            $message = "File changed successfully!\\n\\n";
            $message .= "Status changed to Review (No pending approvers to reset).";
            
            echo "<script>
                    alert('$message');
                    document.location='my_doc.php?tipe=" . urlencode($type) . "&submit=Show';
                  </script>";
        }
        
    } else {
        // Jika SUDAH 'Approved', kirim alert sukses dan redirect
        echo "<script>
                alert('File berhasil diubah.\\nStatus dokumen tetap Approved.');
                document.location='my_doc.php?tipe=" . urlencode($type) . "&submit=Show';
              </script>";
        exit;
    }
    
} else {
    // Jika halaman diakses tanpa POST, tampilkan form
    $drf = isset($_GET['drf']) ? (int)$_GET['drf'] : 0;
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    
    if ($drf == 0) {
        die("Error: DRF tidak valid.");
    }
    
    // Ambil data dokumen
    $sql_doc = "SELECT * FROM docu WHERE no_drf='$drf'";
    $res_doc = mysqli_query($link, $sql_doc);
    $doc_data = mysqli_fetch_assoc($res_doc);
    
    // Cek status approver
    $sql_status = "SELECT 
                    SUM(CASE WHEN status='Approved' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) as pending_count,
                    COUNT(*) as total_count
                   FROM rev_doc WHERE id_doc=$drf";
    $res_status = mysqli_query($link, $sql_status);
    $status_data = mysqli_fetch_assoc($res_status);
    ?>

    <div class="row">
        <div class="col-xs-1"></div>
        <div class="col-xs-7 well well-lg">
            <h2>Change Document</h2>
            
            <div class="alert alert-info">
                <strong>Document:</strong> <?php echo htmlspecialchars($doc_data['no_doc']); ?> - <?php echo htmlspecialchars($doc_data['title']); ?>
            </div>
            
            <?php if ($status_data['approved_count'] > 0): ?>
            <div class="alert alert-warning">
                <span class="glyphicon glyphicon-info-sign"></span>
                <strong>Smart Approval System:</strong><br/>
                <?php echo $status_data['approved_count']; ?> approver(s) already approved this document.<br/>
                When you upload the revised file:
                <ul>
                    <li>✓ Approvers who already approved will <strong>keep their approval</strong></li>
                    <li>✗ Only approvers who suspended (<?php echo $status_data['pending_count']; ?>) will need to re-review</li>
                </ul>
            </div>
            <?php endif; ?>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="drf" value="<?php echo $drf; ?>">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
                <input type="hidden" name="nodoc" value="<?php echo htmlspecialchars($doc_data['no_doc']); ?>">
                <input type="hidden" name="title" value="<?php echo htmlspecialchars($doc_data['title']); ?>">
                
                <div class="form-group">
                    <label>File Document (.pdf/.xlsx): <span class="text-danger">*</span></label>
                    <input type="file" name="baru" class="form-control" required accept=".pdf,.xlsx">
                    <small class="help-block">Current file: <strong><?php echo htmlspecialchars($doc_data['file']); ?></strong></small>
                </div>
                
                <div class="form-group">
                    <label>File Master (.docx/.xlsx):</label>
                    <input type="file" name="masterbaru" class="form-control" accept=".docx,.xlsx">
                    <small class="help-block">Optional - for master document template</small>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="upload" class="btn btn-primary">
                        <span class="glyphicon glyphicon-upload"></span> Update Document
                    </button>
                    <a href="my_doc.php?tipe=<?php echo urlencode($type); ?>&submit=Show" class="btn btn-default">
                        <span class="glyphicon glyphicon-arrow-left"></span> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php
}
?>