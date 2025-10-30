<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'header.php';
include 'koneksi.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<body>
</br></br>

<h3>Approve Document</h3>
<table>
    <form action="" method="post" enctype="multipart/form-data">	
        <tr>
            <td bgcolor="#E8E8E8"><span class="input-group-addon">Comment</span></td>
            <td width="10" align="center" bgcolor="#E8E8E8">:</td>
            <td bordercolorlight="#0000FF" bgcolor="#E8E8E8">
                <textarea class="form-control" name="reason" cols="40" rows="10" wrap="physical"></textarea>
                <input type='hidden' name='no_doc' id='no_doc' value='<?php echo htmlspecialchars($no_doc); ?>' />
                <input type='hidden' name='title' id='title' value='<?php echo htmlspecialchars($title); ?>' />
                <input type='hidden' name='drf' id='drf' value='<?php echo htmlspecialchars($drf); ?>' />
            </td>
        </tr>
        <tr>
            <td bgcolor="#E8E8E8"></td>
            <td colspan="2" align="center" bgcolor="#E8E8E8">
                <input class="btn btn-success btn-sm" type="submit" name="approve" value="Approve" />
            </td>
        </tr>
    </form>
</table>			

</body>
</html>

<?php
if (isset($_POST['approve'])) {
    $reason = mysqli_real_escape_string($link, $_POST['reason']);
    $tglsekarang = date('Y-m-d'); // Format standar database
    
    // Update status approver yang login menjadi Approved
    $sql = "UPDATE rev_doc 
            SET status = 'Approved', 
                reason = '$reason', 
                tgl_approve = '$tglsekarang' 
            WHERE nrp = '$nrp' 
            AND id_doc = '$drf'";
    
    mysqli_query($link, $sql) or die("Error updating approval: " . mysqli_error($link));
    
    // PERUBAHAN UTAMA: Hitung approver dengan logika yang lebih baik
    // Hitung total approver
    $sql1 = "SELECT COUNT(DISTINCT nrp) as total 
             FROM rev_doc 
             WHERE id_doc = $drf";
    $res1 = mysqli_query($link, $sql1);
    $row1 = mysqli_fetch_assoc($res1);
    $jumlah_drf = $row1['total'];
    
    // Hitung approver yang sudah approve
    $sql2 = "SELECT COUNT(DISTINCT nrp) as approved 
             FROM rev_doc 
             WHERE id_doc = $drf 
             AND status = 'Approved'";
    $res2 = mysqli_query($link, $sql2);
    $row2 = mysqli_fetch_assoc($res2);
    $jumlah_approved = $row2['approved'];
    
    // Hitung approver yang masih pending (yang suspend)
    $sql_pending = "SELECT COUNT(DISTINCT nrp) as pending 
                    FROM rev_doc 
                    WHERE id_doc = $drf 
                    AND status = 'Pending'";
    $res_pending = mysqli_query($link, $sql_pending);
    $row_pending = mysqli_fetch_assoc($res_pending);
    $jumlah_pending = $row_pending['pending'];
    
    echo "<!-- Debug: Total=$jumlah_drf, Approved=$jumlah_approved, Pending=$jumlah_pending -->";
    
    // LOGIKA BARU: Dokumen fully approved jika:
    // 1. Semua approver sudah approve DAN
    // 2. Tidak ada yang pending
    if ($jumlah_approved == $jumlah_drf && $jumlah_pending == 0) {
        // Semua approver sudah approve, set dokumen menjadi Approved
        $perintah1 = "UPDATE docu 
                      SET status = 'Approved', 
                          final = '$tglsekarang' 
                      WHERE no_drf = '$drf'";
        mysqli_query($link, $perintah1) or die("Error approving document: " . mysqli_error($link));
        
        // Kirim email notifikasi
        require 'PHPMailer/PHPMailerAutoload.php';
        $mail = new PHPMailer();
        $mail->IsSMTP();
        include 'smtp.php';
        
        $mail->From = "dc_admin@ssi.sharp-world.com";
        $mail->FromName = "Admin Document Online System";

        // Email ke admin dan PIC
        $sql_email = "SELECT DISTINCT users.name, users.email 
                      FROM users 
                      WHERE users.state = 'admin' OR users.state = 'PIC'";
        $res_email = mysqli_query($link, $sql_email) or die(mysqli_error($link));

        // Email ke originator
        $sql3 = "SELECT DISTINCT users.name, users.email 
                 FROM docu, users 
                 WHERE (docu.no_drf = '$drf' AND users.username = docu.user_id)";
        $res3 = mysqli_query($link, $sql3) or die(mysqli_error($link));

        if (mysqli_num_rows($res_email) > 0 || mysqli_num_rows($res3) > 0) {	 
            while ($data_email = mysqli_fetch_row($res_email)) {
                $mail->AddAddress($data_email[1], $data_email[0]);
            }

            while ($data3 = mysqli_fetch_row($res3)) {
                $mail->AddAddress($data3[1], $data3[0]);
            }
            
            $mail->WordWrap = 50;
            $mail->IsHTML(true);
            $mail->Subject = "Document Approved";
            $mail->Body = "Attention Mr./Mrs. : Originator <br /> 
                           This following Document was <span style='color:green'>APPROVED</span> by all reviewers <br /> 
                           No. Document : ".$no_doc."<br /> 
                           Title : ".$title."<br />
                           Please Login into <a href='http://192.168.132.15/document'>Document Online System</a>, Thank You";

            if (!$mail->Send()) {
                echo "Message was not sent <p>";
                echo "Mailer Error: " . $mail->ErrorInfo;
                exit;
            }
        }
        
        ?>
        <script language='javascript'>
            alert('Document was fully approved by all reviewers!');
            document.location='my_doc.php?tipe=<?php echo urlencode($tipe); ?>&submit=Show';
        </script>
        <?php
    } else {
        // Dokumen belum disetujui sepenuhnya.
        
        // -- LOGIKA BARU: Cek sisa approver --
        // Hitung approver yang statusnya MASIH 'Review' (belum suspend/approve)
        $sql_check = "SELECT COUNT(*) as sisa_review 
                      FROM rev_doc 
                      WHERE id_doc = '$drf' AND status = 'Review'";
        $res_check = mysqli_query($link, $sql_check);
        $row_check = mysqli_fetch_assoc($res_check);
        $sisa_review = $row_check['sisa_review'];

        $alert_message = "Your approval has been recorded.\\n\\n";
        
        // sisa_review == 0 berarti semua sudah action (approve/suspend)
        // Ubah status dokumen utama ke 'Pending' agar originator bisa edit
        if ($sisa_review == 0) {
            $perintah_status = "UPDATE docu 
                                SET status = 'Pending' 
                                WHERE no_drf = '$drf'";
            
            $alert_message .= "All other approvers have finished.\\nDocument is set to 'Pending' for revision.";
            
            // Hitung approver yang pending untuk pesan alert
            $sisa_pending = $jumlah_drf - $jumlah_approved; 
            if ($sisa_pending > 0) {
                 $alert_message .= "\\nWaiting for " . $sisa_pending . " approver(s) who suspended.";
            }

        } else {
            // Jika masih ada sisa_review > 0, biarkan 'Review'
            $perintah_status = "UPDATE docu 
                                SET status = 'Review' 
                                WHERE no_drf = '$drf'";
                                
            $alert_message .= "Waiting for " . $sisa_review . " other approver(s).";
        }
        
        mysqli_query($link, $perintah_status) or die("Error updating docu status: " . mysqli_error($link));
        
        ?>
        <script language='javascript'>
            alert('<?php echo $alert_message; ?>');
            document.location='my_doc.php?tipe=<?php echo urlencode($tipe); ?>&submit=Show';
        </script>
        <?php
    }
}
?>