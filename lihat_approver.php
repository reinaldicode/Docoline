<?php
// lihat_approver.php - Public Access (NO LOGIN REQUIRED)
if (session_status() === PHP_SESSION_NONE) session_start();

if (!file_exists('koneksi.php')) { 
    die("koneksi.php tidak ditemukan."); 
}
include 'koneksi.php';

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING | E_DEPRECATED));

// ===== PARAMETER =====
$return_url = isset($_GET['return_url']) ? $_GET['return_url'] : 'search_awal.php';
$drf = isset($_GET['drf']) ? mysqli_real_escape_string($link, $_GET['drf']) : '';
$nodoc = isset($_GET['nodoc']) ? htmlspecialchars($_GET['nodoc']) : '';
$title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : '';
$type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : '';

// Validasi DRF
if (empty($drf)) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Error - Approver List</title>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body style="padding: 50px;">
        <div class="alert alert-danger">Parameter DRF tidak ditemukan.</div>
        <a href="search_awal.php" class="btn btn-default">Kembali ke Search</a>
    </body>
    </html>
    <?php
    exit;
}

// ===== CEK LOGIN STATUS (TIDAK WAJIB) =====
$isLoggedIn = false;
$state = '';
$level = 0;

if ((isset($_SESSION['state']) && !empty($_SESSION['state'])) || 
    (isset($_SESSION['nrp']) && !empty($_SESSION['nrp']))) {
    $isLoggedIn = true;
    $state = $_SESSION['state'] ?? '';
    $level = $_SESSION['level'] ?? 0;
}

// Query untuk ambil data approver
$sql = "SELECT users.*, rev_doc.* FROM users, rev_doc WHERE rev_doc.id_doc='$drf' AND rev_doc.nrp=users.username";
$hasil = mysqli_query($link, $sql) or die("ada error sql: " . mysqli_error($link));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approver List</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    
    <style type="text/css">
    body {
        background-image: url("images/white.jpeg");
        background-color: #cccccc;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }
    .style13 {font-size: 11px}
    </style>
</head>

<body>
<br /><br />
<br /><br />

<div id="content">
    <div id="content_inside">
        <div id="content_inside_main">
            <h1>Approver List</h1>
            <p><br />
            
            <script>
            function goBack() {
                <?php if (!empty($return_url)): ?>
                    window.location.href = '<?php echo addslashes($return_url); ?>';
                <?php else: ?>
                    window.history.back();
                <?php endif; ?>
            }
            </script>

            <button class="btn btn-primary" onclick="goBack()">
                <span class="glyphicon glyphicon-arrow-left"></span>&nbsp;Back
            </button>
            <br />
            
            <br />
            
            <style type="text/css">
            .style1 {font-size: 12px}
            .style4 {font-weight: bold}
            .style5 {font-weight: bold}
            </style>

            <table border="0" cellpadding="3" cellspacing="3" width="780" class="table-responsive">
            <?php
                // Hanya Admin dan Originator yang bisa tambah approver
                if ($isLoggedIn && (($state == 'Admin' || $state == 'Originator') || 
                    ($state == 1 || $state == 7) || 
                    ($level == 42 || $level == 52 || $level == 62)))
                {
            ?>
            <tr>
                <td colspan="13">
                    <a href="upd_approver.php?id_doc=<?php echo $drf;?>&nodoc=<?php echo $nodoc?>&title=<?php echo $title?>&type=<?php echo $type?>" 
                       title="tambah approver" 
                       class="btn btn-primary btn-lg">
                        <i class="glyphicon glyphicon-plus"></i>
                    </a>
                </td>
            </tr>
            <?php
                }
            ?>
            </table>
            
            <table border="0" cellpadding="3" cellspacing="3" width="780" class="table table-hover table-bordered">
            <tr>
            <thead>    
                <td width="5" height="50" class="btn-primary btn-small">
                    <div align="center" class="">Number</div>
                </td>
                <td width="140" height="50" class="btn-primary btn-small">
                    <div align="center" class="">Approver Name</div>
                </td>
                <td width="59" height="50" class="btn-primary btn-small">
                    <div align="center" class="">Approval Status</div>
                </td>
                <td width="59" height="50" class="btn-primary btn-small col-sm-5">
                    <div align="center" class="">Reason</div>
                </td>
                <td width="59" height="50" class="btn-primary btn-small">
                    <div align="center" class="">Section</div>
                </td>
                <td width="59" height="50" class="btn-primary btn-small">
                    <div align="center" class="">Approval Date</div>
                </td>
                <td width="40" height="50" class="btn-primary btn-small" colspan="1">
                    <div align="center" class="">Action</div>
                </td>
            </thead>
            </tr>

            <tbody>
            <?php
                $no = 1;
                while ($data = mysqli_fetch_array($hasil))
                {
            ?>
            <tr>
                <td><div align="justify" class="">
                    <?php echo "$no"; ?>
                </div></td>
                
                <td><div align="justify" class="">
                    <?php echo "$data[name]"; ?>
                </div></td>
                
                <td><div align="justify" class="">
                    <?php if ($data['status'] == 'Review') { ?>
                        <span class="label label-info">
                    <?php } ?>
                    <?php if ($data['status'] == 'Pending') { ?>
                        <span class="label label-warning">
                    <?php } ?>
                    <?php if ($data['status'] == 'Approved') { ?>
                        <span class="label label-success">
                    <?php } ?>
                        <?php echo "$data[status]"; ?>
                    </span>
                </div></td>
                
                <td><div align="justify" class="">
                    <?php echo "$data[reason]"; ?>
                </div></td>
                
                <td><div align="justify" class="">
                    <?php echo "$data[section]"; ?>
                </div></td>
                
                <td><div align="justify" class="">
                    <?php echo "$data[tgl_approve]"; ?>
                </div></td>
                
                <td width="10"><div align="justify"><span class="">
                    <?php
                        // Hanya Admin dan Originator yang bisa delete
                        if ($isLoggedIn && ($state == 'Admin' || $state == 'Originator'))
                        {
                    ?>
                        <a href="del_approver.php?id=<?php echo $data[8];?>&return_url=<?php echo urlencode($return_url); ?>" 
                           title="Delete approver" 
                           class="btn btn-danger btn-xs" 
                           onClick="return confirm('Delete Approver?')">
                            <span class="glyphicon glyphicon-remove"></span> &nbsp; Delete
                        </a>
                    <?php
                        }

                        // Hanya Admin yang bisa add remark
                        if ($isLoggedIn && (($data[8] != "-" && $data[8] != '') && ($state == 'Admin' || $state == 1)))
                        { 
                    ?>
                        <a href="add_remark.php?id_app=<?php echo $data[1];?>&id_dok=<?php echo $data[2];?>" 
                           title="add remark" 
                           class="btn btn-info btn-xs">
                            <span class="glyphicon glyphicon-plus"></span> &nbsp; remark
                        </a>
                    <?php 
                        }
                    ?>
                </span></div></td>
            </tr>
            <?php
                $no++;
                }
            ?>
            </tbody>
            </table>
            
            <?php if (!$isLoggedIn): ?>
            <br />
            <div class="alert alert-warning" style="max-width: 780px;">
                <span class="glyphicon glyphicon-lock"></span> 
                <strong>Info:</strong> Anda sedang melihat dalam mode <strong>Public View</strong>. 
                Untuk menambah atau menghapus approver, silakan <a href="login.php" class="alert-link"><strong>Login terlebih dahulu</strong></a>.
            </div>
            <?php endif; ?>
            
            </p>
        </div>
    </div>    
</div>

<br />
<br />
<br />
<br />

<script src="bootstrap/js/jquery.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
</body>
</html>