<?php
// detail.php - akses publik dengan background sama seperti header.php
extract($_REQUEST);

// deteksi akses publik
$public_access = (isset($_GET['public']) && $_GET['public'] === '1') || (isset($_REQUEST['public']) && $_REQUEST['public'] === '1');

if ($public_access) {
    // PUBLIC: setup minimal tanpa redirect login
    if (session_status() === PHP_SESSION_NONE) session_start();
    include "koneksi.php";
    $state = "";
    $nrp = "";
    
    // Include styling sama seperti di header.php
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Document Control - Detail</title>
        
        <!-- CSS sama seperti di header.php -->
        <link href="bootstrap/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
        <link href="bootstrap/css/bootstrap-responsive.min.css" media="all" type="text/css" rel="stylesheet">
        <link href="bootstrap/css/facebook.css" media="all" type="text/css" rel="stylesheet">
        <link rel="stylesheet" href="bootstrap/css/datepicker.css">
        <link rel="stylesheet" href="bootstrap/css/bootstrap-select.min.css">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
        
        <!-- Background styling sama seperti header.php -->
        <style> 
        body {
            background-image: url("images/white.jpeg");
            background-color: #cccccc;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        .back-button {
            margin: 20px 0;
            padding: 0 15px;
        }
        .back-button .btn {
            background: #337ab7;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .back-button .btn:hover {
            background: #286090;
            color: white;
            text-decoration: none;
        }
        </style>
        
        <!-- JavaScript -->
        <script src="bootstrap/js/jquery.min.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <script src="bootstrap/js/bootstrap-datepicker.js"></script>
    </head>
    <body>
    <br /><br />
    
    <!-- Tombol Back -->
    <div class="back-button">
        <a href="javascript:history.back()" class="btn">
            <span class="glyphicon glyphicon-arrow-left"></span> Kembali
        </a>
    </div>
    
    <?php
} else {
    // akses normal: flow lama
    if (isset($log) && $log == 1) {
        include "index.php";
        $state = "";
        $nrp = "";
    } else {
        include("header.php");
    }
    include "koneksi.php";
}

// sanitasi input DRF
$drf = isset($drf) ? mysqli_real_escape_string($link, trim($drf)) : '';

// ambil data dokumen
$sql = "SELECT * FROM docu WHERE no_drf='$drf'";
$res = mysqli_query($link, $sql);

while ($info = mysqli_fetch_array($res)) { 
?>

<title>Detail Document : <?php echo htmlspecialchars($info['title']);?></title>

<?php if (!$public_access): ?>
<br />
<br />
<br />
<?php endif; ?>

<head><h1>Detail Document : <?php echo htmlspecialchars($info['title']);?></h1></head>

<body>
<table border="0" cellpadding="0" cellspacing="0" class="table table-hover">
<tr>
    <td>RADF&nbsp;&nbsp;</td><td>:</td><td>&nbsp;&nbsp;<?php echo htmlspecialchars($info['no_drf']);?></td>
</tr>
<tr>
    <td>Title&nbsp;&nbsp;</td><td>:</td><td>&nbsp;&nbsp;<b><?php echo htmlspecialchars($info['title']);?></b></td>
</tr>
<tr>
    <td>Date&nbsp;&nbsp;</td><td>:</td><td>&nbsp;&nbsp;<?php echo htmlspecialchars($info['tgl_upload']);?></td>
</tr>
<tr>
    <td>Doc.Number&nbsp;&nbsp;</td><td>:</td><td>&nbsp;&nbsp;<?php echo htmlspecialchars($info['no_doc']);?></td>
</tr>

<?php if ($state == 'Admin' || ($state == 'Originator' && $nrp == $info['user_id'])) { ?>
<tr>
    <td>File Master&nbsp;&nbsp;</td><td>:</td><td>&nbsp;&nbsp;<a href="master/<?php echo htmlspecialchars($info['file_asli']);?>" class="btn btn-xs btn-primary" title="Download File Master"><span class="glyphicon glyphicon-cloud-download"></span>&nbsp;Download</a></td>
</tr>
<?php } ?>

<tr>
    <td>Description&nbsp;&nbsp;</td><td>:</td><td>&nbsp;&nbsp;<?php echo nl2br(htmlspecialchars($info['descript']));?></td>
</tr>
<tr>
    <td>Related/Addopted Doc.&nbsp;&nbsp;</td><td>:</td>
    <td>
        &nbsp;&nbsp;<?php 
        $sql2 = "SELECT rel_doc.no_doc,do.file,do.no_rev FROM docu DO,rel_doc WHERE rel_doc.no_drf='".mysqli_real_escape_string($link, $info['no_drf'])."' AND rel_doc.no_doc=do.no_doc AND no_rev=(SELECT max(no_rev) FROM docu dm WHERE dm.no_doc=do.no_doc)";
        $res2 = mysqli_query($link, $sql2);
        if ($res2) {
            while ($info2 = mysqli_fetch_array($res2)) { ?>
                <a href="document/<?php echo htmlspecialchars($info2['file']); ?>">
                <?php echo htmlspecialchars($info2['no_doc']); echo ", "; ?>
                </a>
            <?php }
            mysqli_free_result($res2);
        }
        ?>
    </td>
</tr>
<tr>
    <td>Review to &nbsp;&nbsp;</td><td>:</td><td>&nbsp;&nbsp;<?php echo htmlspecialchars($info['rev_to']);?></td>
</tr>
<tr>
    <td>Revision Cause &nbsp;&nbsp;</td><td>:</td><td>&nbsp;&nbsp;<?php echo htmlspecialchars($info['history']);?></td>
</tr>
<tr>
    <td>Doc. History&nbsp;&nbsp;</td><td>:</td>
    <td>
        &nbsp;&nbsp;<?php 
        $sql3 = "SELECT no_rev,file,history FROM docu WHERE no_doc='".mysqli_real_escape_string($link, $info['no_doc'])."' AND no_rev<>'".mysqli_real_escape_string($link, $info['no_rev'])."' ORDER BY no_rev";
        $res3 = mysqli_query($link, $sql3);
        if ($res3) {
            while ($info3 = mysqli_fetch_array($res3)) { ?>
                <a href="document/<?php echo htmlspecialchars($info3['file']); ?>" title="<?php echo htmlspecialchars($info3['history']);?>">
                <?php echo htmlspecialchars($info3['no_rev']); ?>
                </a>,
            <?php }
            mysqli_free_result($res3);
        }
        ?>
    </td>
</tr>
<?php } 

if (isset($res) && $res) mysqli_free_result($res);

// Close body dan html untuk public access
if ($public_access) {
    echo '</body></html>';
}
?>