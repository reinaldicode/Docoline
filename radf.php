<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>RADF - Review and Approval Document Form</title>
    <link href="bootstrap/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" media="all" type="text/css" rel="stylesheet">
    <link href="bootstrap/css/facebook.css" media="all" type="text/css" rel="stylesheet">
    
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="bootstrap/js/bootstrap-dropdown.js"></script>
    <script src="bootstrap/js/facebook.js"></script>
  
    <link rel="stylesheet" href="bootstrap/css/datepicker.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
    <script src="bootstrap/js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap-datepicker.js"></script>
    <style type="text/css">
      .container-non-responsive {
        margin-left: auto;
        margin-right: auto;
        padding-left: 15px;
        padding-right: 15px;
        width: 1170px;
      }
      h4 {
        text-align: center;
      }
    </style>
  </head>
<body>

<?php
include "koneksi.php";

// Ambil drf dari request, pastikan itu adalah integer
$drf = isset($_REQUEST['drf']) ? (int)$_REQUEST['drf'] : 0;

// Inisialisasi variabel dengan nilai default untuk mencegah error
$drf_val = $drf;
$tanggal = '';
$no_doc = '';
$title = '';
$rev = '';
$nama = '';
$iso = 0;
$seqtrain = 0;
$dirtrain = 0;
$type = '';
$hist = '';
$rev_to = '';
$file = '';
$history = '';
$display_dept = '';
$display_section = '';

if ($drf > 0) {
  // PERBAIKAN KEAMANAN: Menggunakan prepared statement untuk mencegah SQL Injection
  $sql_doc = "SELECT docu.*, 
                COALESCE(docu.uploader_name, users.name) as display_name,
                COALESCE(docu.original_dept, docu.dept) as display_dept,
                COALESCE(docu.original_section, docu.section) as display_section
              FROM docu 
              LEFT JOIN users ON docu.user_id=users.username 
              WHERE no_drf=?";
  
  $stmt_doc = mysqli_prepare($link, $sql_doc);
  mysqli_stmt_bind_param($stmt_doc, "i", $drf);
  mysqli_stmt_execute($stmt_doc);
  $hasil_doc = mysqli_stmt_get_result($stmt_doc);

  if ($data_doc = mysqli_fetch_array($hasil_doc)) {
    $drf_val = $data_doc['no_drf'];
    $tanggal = $data_doc['tgl_upload'];
    $no_doc = $data_doc['no_doc'];
    $title = $data_doc['title'];
    $rev = $data_doc['no_rev'];
    $nama = $data_doc['display_name'];
    $iso = $data_doc['iso'];
    $seqtrain = $data_doc['seqtrain'];
    $dirtrain = $data_doc['dirtrain'];
    $type = $data_doc['doc_type'];
    $hist = $data_doc['history'];
    $rev_to = $data_doc['rev_to'];
    $file = $data_doc['file'];
    $history = $data_doc['history'];
    $display_dept = $data_doc['display_dept'];
    $display_section = $data_doc['display_section'];
  }
  mysqli_stmt_close($stmt_doc);
}
?>
  
  <div style="width: 900px; margin-left:0px;">
    <div class="row" style="width: 900px; margin-left:0px;">
      <h4 style="text-align: center;">REVIEW AND APPROVAL DOCUMENT FORM (RADF)</h4>
    </div>
    <div class="col-xs-3">
    </div>
  </div>

  <div class="row" style="width: 900px; margin-left:0px;">
    <br />
    <div class="row" style="width: 900px; margin-left:0px;">
      <table class="" style="width: 900px; float:left;" border="0" cellpadding="" cellspacing="" width="">
        <tr>
          <td>No. Drf</td><td> : </td>
          <td><a href="document/<?php echo htmlspecialchars($file ?? ''); ?>" target="_blank">&nbsp;<?php echo htmlspecialchars($drf_val ?? ''); ?> </a></td>
        </tr>
        <tbody>
          <tr>
            <td>Issue Date</td><td> : </td>
            <td height="30px">&nbsp;<?php echo htmlspecialchars($tanggal ?? ''); ?></td>
          </tr>
          <tr>
            <td>No. Document</td><td> : </td>
            <td height="30px">&nbsp;<?php echo htmlspecialchars($no_doc ?? ''); ?></td>
          </tr>
          <tr>
            <td>Doc. Title</td><td> : </td>
            <td height="30px">&nbsp;<?php echo htmlspecialchars($title ?? ''); ?></td>
          </tr>
          <tr>
            <td>No. Rev.</td><td> : </td>
            <td height="30px">&nbsp;<?php echo htmlspecialchars($rev ?? ''); ?></td>
          </tr>
        </tbody>
      </table>
      <br />
      <br />
    </div>
  </div>
  
  <table width="900px" height="379" border="1">
    <tr>
      <td colspan="2" rowspan="2">
        <table width="100%" height="230" border="0">
          <tr>
            <td style="vertical-align:top;">Reason for issue / cancel / revise document (alasan mengeluarkan / membatalkan / merevisi dokumen): <br /><br /><?php echo htmlspecialchars($history ?? ''); ?></td>
          </tr>
          <tr>
            <td>&nbsp; <?php echo htmlspecialchars($hist ?? ''); ?></td>
          </tr>
          <tr>
            <td style="vertical-align:bottom;">
              Proposed by: <?php echo htmlspecialchars($nama ?? ''); ?><br />
              <small style="color: #666;">From: <?php echo htmlspecialchars($display_section ?? ''); ?> </small>
            </td>
          </tr>
        </table>
      </td>
      <td width="437" height="110">This document is appropriate with requirement of (dokumen ini sesuai dengan persyaratan):<font size="1" face="arial"></font><br>
        &nbsp;&nbsp;[<?php if($iso==1){?><span class="glyphicon glyphicon-ok"></span><?php } else{echo "&nbsp;&nbsp;&nbsp;&nbsp;";} ?>] ISO 9001<br>
        &nbsp;&nbsp;[<?php if($iso==2){?><span class="glyphicon glyphicon-ok"></span><?php } else{echo "&nbsp;&nbsp;&nbsp;&nbsp;";} ?>] ISO 14001<br>
        &nbsp;&nbsp;[<?php if($iso==4){?><span class="glyphicon glyphicon-ok"></span><?php } else{echo "&nbsp;&nbsp;&nbsp;&nbsp;";} ?>] ISO 45001<br>
        &nbsp;&nbsp;[<?php if($iso==3){?><span class="glyphicon glyphicon-ok"></span><?php } else{echo "&nbsp;&nbsp;&nbsp;&nbsp;";} ?>] OHSAS / SMK3<br>
        &nbsp;&nbsp;[<?php if($iso==5){?><span class="glyphicon glyphicon-ok"></span><?php } else{echo "&nbsp;&nbsp;&nbsp;&nbsp;";} ?>] Indonesian Law<br>
      </td>
      <tr>
        <td width="437" height="100" valign="top">Transfer system doc:<font size="1" face="arial"></font><br>
          &nbsp;&nbsp;[<span class="glyphicon glyphicon-ok"></span>] RADF<br>
          &nbsp;&nbsp;[<?php if($seqtrain=='1'){?><span class="glyphicon glyphicon-ok"></span><?php } else{echo "&nbsp;&nbsp;&nbsp;&nbsp;";} ?>] Sequence training<br>
          &nbsp;&nbsp;[<?php if($dirtrain=='1'){?><span class="glyphicon glyphicon-ok"></span><?php } else{echo "&nbsp;&nbsp;&nbsp;&nbsp;";} ?>] Direct training
        </td>
      </tr>
      <tr>
        <td width="200px" height="100px">&nbsp;
          <input name="" type="checkbox"> Ya / Yes<br> &nbsp;
          <input name="" type="checkbox"> Tidak / No<br> 
        </td>
        <td colspan="2" valign="top">
          Is this document adopted from external document (ex : Sharp Co. Instruction Doc., etc)? (Apakah dokumen ini diadopsi dari dokumen eksternal, misalnya Sharp Co. Instruction Doc., dll ?) If Yes, please 
          write document no. and its title Instruction Doc., dll(Jika Ya, tuliskan judul dan nomor dokumen tersebut):
        </td>
      </tr>
      <tr>
        <td width="200px" height="100px">&nbsp;
          <input name="" type="checkbox"> Ya / Yes<br> &nbsp;
          <input name="" type="checkbox"> Tidak / No<br> 
        </td>
        <td colspan="2">
          Is this issuing/cancelling/revising of document will affect to contents of related procedure/work instruction/ form? (apakah pengeluaran/pembatalan/perevisian dokumen ini akan mempengaruhi isi 
          prosedur/instruksi kerja/form catatan terkait?): 
          <br />
          <br />
          [Then do adjustment to related document against this issuing, cancelling or revising (kemudian lakukan penyesuaian terhadap dokumen terkait atas pengeluaran, pembatalan atau perevisian dokumen ini)]
        </td>
      </tr>
    </tr>
  </table>
  <table width="900px" border="1">
    <tr>
      <td height="23" colspan="3" align="center" bgcolor="#5C8A8A">&nbsp; <b>Document's Approval:</b></td>
    </tr>
    <tr align="center" height="30px">
      <strong>
        <td width="300px">Affected Section</td>
        <td width="300px">Status Review</td>
        <td width="300px">Reviewer</td>
      </strong>
    </tr>
    <?php
      if ($drf > 0) {
        // PERBAIKAN LOGIKA: Hanya menampilkan reviewer yang datanya ada (baik dari snapshot atau join)
        $sql_rev="SELECT 
                    COALESCE(rev_doc.reviewer_name, users.name) as reviewer_name,
                    COALESCE(rev_doc.reviewer_section, users.section) as reviewer_section,
                    rev_doc.status, 
                    rev_doc.tgl_approve,
                    rev_doc.nrp,
                    users.level
                  FROM rev_doc 
                  LEFT JOIN users ON users.username = rev_doc.nrp 
                  WHERE rev_doc.id_doc = ? 
                  AND (rev_doc.reviewer_name IS NOT NULL OR users.name IS NOT NULL)
                  ORDER BY users.level ASC";

        $stmt_rev = mysqli_prepare($link, $sql_rev);
        mysqli_stmt_bind_param($stmt_rev, "i", $drf);
        mysqli_stmt_execute($stmt_rev);
        $hasil_rev = mysqli_stmt_get_result($stmt_rev);
        
        while ($data_rev = mysqli_fetch_array($hasil_rev)) {
          ?>
          <tr align="center" height="50px">
            <td><?php echo htmlspecialchars($data_rev['reviewer_section'] ?? '')?></td>
            <td>
            <?php if ($data_rev['status']=='Review') {?> <img src="images/rev.png" alt="Review">  <?php }?>
            <?php if ($data_rev['status']=='Approved') {?>  <img src="images/approve.jpg" alt="Approved"> <?php }?>
            <?php if ($data_rev['status']=='Pending') {?>  <img src="images/pending.png" alt="Pending"> <?php }?>
            </td>
            <td><?php echo htmlspecialchars($data_rev['reviewer_name'] ?? '')?></td>
          </tr>
          <?php
        }
        mysqli_stmt_close($stmt_rev);
      }
    ?>
    <tr>
      <td>&nbsp;Remark:</td>
      <td>&nbsp;Remark:</td>
      <td>&nbsp;Remark:</td>
    </tr>
    <tr>
      <td>
        <table>
          <tr>
            <td width="" height="100px">
              &nbsp;[<?php if ($rev_to=='Issue'){ ?>  <span class="glyphicon glyphicon-ok"></span><?php } else{ echo "&nbsp;&nbsp;&nbsp;&nbsp;" ;} ?>] Issue new Document<br>
              &nbsp;[<?php if ($rev_to=='Revision'){ ?>  <span class="glyphicon glyphicon-ok"></span><?php } else{ echo "&nbsp;&nbsp;&nbsp;&nbsp;" ;} ?>] Revise Existing Document<br>
              &nbsp;[<?php if ($rev_to=='Cancel'){ ?>  <span class="glyphicon glyphicon-ok"></span><?php } else{ echo "&nbsp;&nbsp;&nbsp;&nbsp;" ;} ?>] Canceled<br> 
            </td>
          </tr>
          <tr>
            <td>
              <?php 
              $status1 = "";
              $nama_mr = "";
              if ($type == "Procedure" && $drf > 0) {
                // PERBAIKAN KEAMANAN: Menggunakan prepared statement
                $sql_mr="SELECT COALESCE(rev_doc.reviewer_name, users.name) as reviewer_name, rev_doc.status
                         FROM rev_doc 
                         LEFT JOIN users ON users.username = rev_doc.nrp 
                         WHERE rev_doc.id_doc = ? AND users.level < 2";
                $stmt_mr = mysqli_prepare($link, $sql_mr);
                mysqli_stmt_bind_param($stmt_mr, "i", $drf);
                mysqli_stmt_execute($stmt_mr);
                $hasil_mr = mysqli_stmt_get_result($stmt_mr);

                if ($data_mr = mysqli_fetch_array($hasil_mr)) {
                  $status1 = $data_mr['status'] ?? '';
                  $nama_mr = $data_mr['reviewer_name'] ?? '';
                }
                mysqli_stmt_close($stmt_mr);
              }
              ?>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <?php if ($status1=='Review') {?> <img src="images/rev.png" alt="Review">  <?php }?>
              <?php if ($status1=='Approved') {?>  <img src="images/approve.jpg" alt="Approved"> <?php }?>
              <?php if ($status1=='Pending') {?>  <img src="images/pending.png" alt="Pending"> <?php }?>
            </td>
          </tr>
          <tr>
            <td>Approved By: <?php echo htmlspecialchars($nama_mr ?? '');?> <br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (Top Management / MR committee)</td>
          </tr>
        </table>
      </td>
      <td>
        <table>
          <tr>
            <td width="" height="100px">
              &nbsp;[<?php if ($rev_to=='Issue'){ ?>  <span class="glyphicon glyphicon-ok"></span><?php } else{ echo "&nbsp;&nbsp;&nbsp;&nbsp;" ;} ?>] Issue new Document<br>
              &nbsp;[<?php if ($rev_to=='Revision'){ ?>  <span class="glyphicon glyphicon-ok"></span><?php } else{ echo "&nbsp;&nbsp;&nbsp;&nbsp;" ;} ?>] Revise Existing Document<br>
              &nbsp;[<?php if ($rev_to=='Cancel'){ ?>  <span class="glyphicon glyphicon-ok"></span><?php } else{ echo "&nbsp;&nbsp;&nbsp;&nbsp;" ;} ?>] Canceled<br> 
            </td>
          </tr>
          <tr>
            <td>
              <?php 
              $status2 = "";
              $nama_dir = "";
              if ($drf > 0) {
                // PERBAIKAN KEAMANAN: Menggunakan prepared statement
                $sql_dir="SELECT COALESCE(rev_doc.reviewer_name, users.name) as reviewer_name, rev_doc.status
                          FROM rev_doc 
                          LEFT JOIN users ON users.username = rev_doc.nrp 
                          WHERE rev_doc.id_doc = ? AND users.level = (
                              SELECT MIN(level) 
                              FROM users, rev_doc 
                              WHERE rev_doc.id_doc = ? AND users.username = rev_doc.nrp
                          )";
                $stmt_dir = mysqli_prepare($link, $sql_dir);
                mysqli_stmt_bind_param($stmt_dir, "ii", $drf, $drf);
                mysqli_stmt_execute($stmt_dir);
                $hasil_dir = mysqli_stmt_get_result($stmt_dir);

                if ($data_dir = mysqli_fetch_array($hasil_dir)) {
                  $status2 = $data_dir['status'] ?? '';
                  $nama_dir = $data_dir['reviewer_name'] ?? '';
                }
                mysqli_stmt_close($stmt_dir);
              }
              ?>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <?php if ($status2=='Review') {?> <img src="images/rev.png" alt="Review">  <?php }?>
              <?php if ($status2=='Approved') {?>  <img src="images/approve.jpg" alt="Approved"> <?php }?>
              <?php if ($status2=='Pending') {?>  <img src="images/pending.png" alt="Pending"> <?php }?>
            </td>
          </tr>
          <tr>
            <td>Approved By: <?php echo htmlspecialchars($nama_dir ?? '');?><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (Director / Dept. Head)</td>
          </tr>
        </table>
      </td>
      <td>
        <table>
          <tr>
            <td width="" height="100px">&nbsp;
              [<?php if ($rev_to=='Issue'){ ?>  <span class="glyphicon glyphicon-ok"></span><?php } else{ echo "&nbsp;&nbsp;&nbsp;&nbsp;" ;} ?>]
              Issue new Document<br> &nbsp;
              [<?php if ($rev_to=='Revision'){ ?>  <span class="glyphicon glyphicon-ok"></span><?php } else{ echo "&nbsp;&nbsp;&nbsp;&nbsp;" ;} ?>]
              Revise Existing Document<br> &nbsp;
              [<?php if ($rev_to=='Cancel'){ ?>  <span class="glyphicon glyphicon-ok"></span><?php } else{ echo "&nbsp;&nbsp;&nbsp;&nbsp;" ;} ?>]
              Canceled<br> 
            </td>
          </tr>
          <tr>
            <td>
              <?php 
                $status3 = "";
                $nama_head = "";
                if ($drf > 0) {
                  // PERBAIKAN KEAMANAN: Menggunakan prepared statement
                  $sql_head="SELECT COALESCE(rev_doc.reviewer_name, users.name) as reviewer_name, rev_doc.status
                             FROM rev_doc 
                             LEFT JOIN users ON users.username = rev_doc.nrp 
                             WHERE rev_doc.id_doc = ? AND users.level = 3";
                  $stmt_head = mysqli_prepare($link, $sql_head);
                  mysqli_stmt_bind_param($stmt_head, "i", $drf);
                  mysqli_stmt_execute($stmt_head);
                  $hasil_head = mysqli_stmt_get_result($stmt_head);
                  if ($data_head = mysqli_fetch_array($hasil_head)) {
                    $status3 = $data_head['status'] ?? '';
                    $nama_head = $data_head['reviewer_name'] ?? '';
                  }
                  mysqli_stmt_close($stmt_head);
                }
              ?>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <?php if ($status3=='Review') {?> <img src="images/rev.png" alt="Review">  <?php }?>
              <?php if ($status3=='Approved') {?>  <img src="images/approve.jpg" alt="Approved"> <?php }?>
              <?php if ($status3=='Pending') {?>  <img src="images/pending.png" alt="Pending"> <?php }?>
            </td>
          </tr>
          <tr>
            <td>Approved By: <?php echo htmlspecialchars($nama_head ?? '');?><br />
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; (Section Head)</td>
          </tr>
        </table>
      </td>      
    </tr>
  </table>
</body>
</html>