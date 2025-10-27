<?php
// Tambalan untuk eregi()
if (!function_exists('eregi')) {
    function eregi($pattern, $string) {
        return preg_match('/' . preg_quote($pattern, '/') . '/i', $string);
    }
}

// Tambalan untuk each()
if (!function_exists('each')) {
    function each(&$array) {
        $key = key($array);
        if ($key === null) {
            return false;
        }
        $value = current($array);
        next($array);
        return [$key, $value, 'key' => $key, 'value' => $value, 1 => $value, 0 => $key];
    }
}

include 'header.php';
include 'koneksi.php';

// Tangkap 'drf' dan variabel lain dari URL (GET)
$drf = isset($_GET['drf']) ? (int)$_GET['drf'] : 0;
$no_doc = isset($_GET['no_doc']) ? $_GET['no_doc'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Validasi
if ($drf == 0) {
    die("Error: DRF tidak valid atau tidak ditemukan.");
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<body>
</br></br></br></br>

<h3>Suspend/Revise Document</h3>
<table>
    <form action="" method="post" enctype="multipart/form-data">	
        <tr>
            <td bgcolor="#E8E8E8"><span class="input-group-addon">Reason</span></td>
            <td width="10" align="center" bgcolor="#E8E8E8">:</td>
            <td bordercolorlight="#0000FF" bgcolor="#E8E8E8">
                <textarea class="form-control" name="reason" cols="40" rows="10" wrap="physical" required></textarea>
                <input type='hidden' name='drf' value='<?php echo htmlspecialchars($drf); ?>' />
                <input type='hidden' name='no_doc' id='no_doc' value='<?php echo htmlspecialchars($no_doc); ?>' />
                <input type='hidden' name='type' id='type' value='<?php echo htmlspecialchars($type); ?>' />
                <input type='hidden' name='name_app' id='name_app' value='<?php echo htmlspecialchars($name); ?>' />
            </td>
        </tr>
        <tr>
            <td bgcolor="#E8E8E8"></td>
            <td colspan="2" align="center" bgcolor="#E8E8E8">
                <input class="btn btn-warning btn-sm" type="submit" name="submit" value="Suspend" />
            </td>
        </tr>
    </form>
</table>			

</body>
</html>

<?php
if (isset($_POST['submit'])) {
    $id_doc = $_POST['drf'];
    $reason = mysqli_real_escape_string($link, $_POST['reason']);
    $name_app = mysqli_real_escape_string($link, $_POST['name_app']);
    $tglsekarang = date('Y-m-d'); // Format standar database
    
    // PERUBAHAN UTAMA: Hanya update approver yang suspend, tidak reset yang sudah approve
    $sql = "UPDATE rev_doc 
            SET status = 'Pending', 
                reason = '$reason', 
                tgl_approve = '$tglsekarang' 
            WHERE nrp = '$nrp' 
            AND id_doc = '$drf'";
    
    mysqli_query($link, $sql) or die("Error updating rev_doc: " . mysqli_error($link));
    
    // Update status dokumen utama menjadi Pending
    $perintah1 = "UPDATE docu 
                  SET status = 'Pending' 
                  WHERE no_drf = '$drf'";
    mysqli_query($link, $perintah1) or die("Error updating docu: " . mysqli_error($link));

    // Kirim email notifikasi
    require_once("class.smtp.php");
    require_once("class.phpmailer.php");

    $mail = new PHPMailer();
    $mail->IsSMTP();
    include 'smtp.php';
    
    $mail->From = "dc_admin@ssi.sharp-world.com";
    $mail->FromName = "Admin Document Online System";

    // Email ke semua approver dan admin
    $sql2 = "SELECT DISTINCT users.name, users.email 
             FROM rev_doc, users 
             WHERE (rev_doc.id_doc = '$drf' AND users.username = rev_doc.nrp) 
             OR users.state = 'admin'";
    $res2 = mysqli_query($link, $sql2) or die(mysqli_error($link));

    // Email ke originator
    $sql3 = "SELECT DISTINCT users.name, users.email 
             FROM docu, users 
             WHERE (docu.no_drf = '$drf' AND users.username = docu.user_id)";
    $res3 = mysqli_query($link, $sql3) or die(mysqli_error($link));

    if (mysqli_num_rows($res2) > 0 || mysqli_num_rows($res3) > 0) {	 
        while ($data2 = mysqli_fetch_row($res2)) {
            $mail->AddAddress($data2[1], $data2[0]);
        }

        while ($data3 = mysqli_fetch_row($res3)) {
            $mail->AddAddress($data3[1], $data3[0]);
        }
        
        $mail->WordWrap = 50;
        $mail->IsHTML(true);
        $mail->Subject = "Document Suspended";
        $mail->Body = "Attention Mr./Mrs. : Originator <br /> 
                       This following <span style='color:green'>".$type."</span> Document needs to be <span style='color:orange'>REVISED</span> <br /> 
                       No. Document : ".$no_doc."<br /> 
                       Reason : ".$reason."<br />
                       Suspended by : ".$name_app."<br />
                       <br />
                       <strong>Note:</strong> Only the approver who suspended needs to re-approve after revision.<br />
                       Please Login into <a href='http://192.168.132.15/document'>Document Online System</a>, Thank You";

        if (!$mail->Send()) {
            echo "Message was not sent <p>";
            echo "Mailer Error: " . $mail->ErrorInfo;
            exit;
        }
    }									
    ?>
    <script language='javascript'>
        alert('Document Suspended\n\nNote: Only the suspending approver needs to re-approve after revision.');
        document.location='index_login.php';
    </script>
    <?php				
}
?>