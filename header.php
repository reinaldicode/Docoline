<?php
//header.php
include 'session.php';
extract($_REQUEST);
?>

<html>
<title>Document Control</title>

<head profile="http://www.global-sharp.com">
 
  <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSS Files -->
  <link href="bootstrap/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" media="all" type="text/css" rel="stylesheet">
    <link href="bootstrap/css/facebook.css" media="all" type="text/css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap/css/datepicker.css">
  <link rel="stylesheet" href="bootstrap/css/bootstrap-select.min.css">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.css">

  <!-- JavaScript Files - PROPER ORDER -->
    <script src="bootstrap/js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="bootstrap/js/bootstrap-datepicker.js"></script>

<?php if (isset($name) && $name=='Vitria') {?>
  <style> 
body {
    background-image: url("images/white.jpeg");
    background-color: #cccccc;
}
</style>
  <?php } 
  else   { ?>
  <style> 
body {
    background-image: url("images/white.jpeg");
    background-color: #cccccc;
    background-repeat: no-repeat;
    background-attachment: fixed;
}
</style>
  <?php } ?>

<script type="text/javascript">
$(document).ready(function () {
    // Initialize dropdowns
    $('.dropdown-toggle').dropdown();
    
    // Change password modal handler
    $('.chg-pass').click(function () {
        $('span.user-id').text($(this).data('user'));
        var usercp = $(this).data('usercp');
        $(".modal-body #usercp").val( usercp );
        var passcp = $(this).data('passcp');
        $(".modal-body #passcp").val( passcp );
        var title = $(this).data('title');
        $(".modal-body #title").val( title );
    });
    
    // Fix for dropdowns in mobile
    $('.navbar-toggle').click(function() {
        $('.navbar-collapse').collapse('toggle');
    });
});
</script>

<!-- ✅ CUSTOM STYLE UNTUK ICON -->
<style>
/* Styling untuk icon di navbar */
.navbar-nav > li > a {
    text-align: center;
    padding-top: 10px;
    padding-bottom: 10px;
}

.navbar-nav > li > a img {
    display: block;
    margin: 0 auto 5px;
    max-width: 40px;
    max-height: 40px;
}

/* Style untuk badge notifikasi */
.notification-badge {
    position: relative;
    top: -5px;
    background: #ff0000;
    color: white;
    padding: 3px 7px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: bold;
    margin-left: 5px;
}

/* Fix untuk img-responsive di navbar */
.navbar-nav > li > a img.img-responsive {
    display: block;
    margin: 0 auto 5px;
    max-width: 100%;
    height: auto;
}
</style>
 
</head>

<body >
<br />
<br />

  <?php 
  // ✅ LOGIKA NOTIFIKASI YANG TELAH DISINKRONKAN DENGAN my_doc.php
  $sql="";
  $rows = 0;
  
  if (isset($state)) {
    if ($state=='Admin') {
      // ✅ Admin: hitung dokumen Review + Pending + Edited (SUDAH SINKRON)
      $sql="SELECT * FROM docu WHERE status IN ('Review', 'Pending', 'Edited') ORDER BY no_drf";
      
      if (!empty($sql)) {
        $res=mysqli_query($link, $sql);
        if ($res) {
          $rows = mysqli_num_rows($res);
        }
      }
    }
    elseif ($state=='Originator') {
      // ✅ Originator: hitung dokumen miliknya yang Review + Pending + Edited (SUDAH SINKRON)
      $sql="SELECT * FROM docu WHERE status IN ('Review', 'Pending', 'Edited') AND user_id='$nrp' ORDER BY no_drf";
      
      if (!empty($sql)) {
        $res=mysqli_query($link, $sql);
        if ($res) {
          $rows = mysqli_num_rows($res);
        }
      }
    }
    elseif ($state=='Approver') {
      // ✅ Approver: hitung dokumen yang perlu di-approve + dokumen milik sendiri (SUDAH SINKRON)
      // Logic sama persis dengan function getNotificationCounts() di my_doc.php
      
      // 1. Hitung dokumen yang perlu di-review
      $sql_review = "SELECT COUNT(DISTINCT docu.no_drf) as count 
                     FROM docu
                     INNER JOIN rev_doc ON docu.no_drf=rev_doc.id_doc
                     WHERE docu.status='Review' 
                     AND rev_doc.status='Review' 
                     AND rev_doc.nrp='$nrp'";
      
      $result_review = mysqli_query($link, $sql_review);
      $count_review = 0;
      if ($result_review) {
        $row_review = mysqli_fetch_assoc($result_review);
        $count_review = (int)$row_review['count'];
      }
      
      // 2. Hitung dokumen milik sendiri
      $sql_my = "SELECT COUNT(*) as count 
                 FROM docu 
                 WHERE user_id='$nrp' 
                 AND status IN ('Review', 'Pending', 'Edited')";
      
      $result_my = mysqli_query($link, $sql_my);
      $count_my = 0;
      if ($result_my) {
        $row_my = mysqli_fetch_assoc($result_my);
        $count_my = (int)$row_my['count'];
      }
      
      // 3. Total notifikasi = dokumen to review + dokumen milik sendiri
      $rows = $count_review + $count_my;
    }
  }
  ?>

  <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <span class="navbar-brand" href="index.php"><h3><img src="images/doc-21.png" style="margin-top:-25px;" size="40px" alt="Logo"></h3></span>

      <ul class="nav navbar-nav">
        <!-- HOME -->
        <li>
            <a href="index_login.php" class="bg-info">
                <img src="images/home.png" alt="Home">
                Home
            </a>
        </li>
        
        <!-- REVIEW dengan Notifikasi -->
        <?php if ($rows > 0 && isset($state) && in_array($state, ['Approver', 'Admin', 'Originator'])){?>
          <li>
            <a href="my_doc.php">
                <img src="images/notif.gif" alt="Notification">
                Review <span class="notification-badge"><?php echo $rows; ?></span>
            </a>
          </li>
        <?php } else {?>
          <li>
            <a href="my_doc.php">
                <img src="images/text.png" alt="Review">
                Review
            </a>
          </li>
        <?php } ?>
        
        <!-- UPLOAD -->
        <li>
            <a href="upload.php" class="bg-info mute">
                <img src="images/up.png" alt="Upload">
                Upload
            </a>
        </li>

        <!-- PROGRESS - KHUSUS APPROVER -->
        <?php if (isset($state) && $state == 'Approver'): ?>
        <li>
            <a href="progress.php">
                <img src="images/icon_progress.png" alt="Progress" style="width:40px;height:40px;">
                Progress
            </a>
        </li>
        <?php endif; ?>

        <!-- DOCUMENTS DROPDOWN -->
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                <img src="images/document.png" alt="Documents">
                Documents <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
                <?php
                // Load document types dari JSON
                $jsonFile = __DIR__ . '/data/document_types.json';
                if (file_exists($jsonFile)) {
                    $docTypes = json_decode(file_get_contents($jsonFile), true);
                    if (is_array($docTypes) && count($docTypes) > 0) {
                        foreach ($docTypes as $dt) {
                            if (!is_array($dt) || !isset($dt['name'])) continue;
                            
                            $typeName = $dt['name'];
                            $hasSubmenu = isset($dt['has_submenu']) && $dt['has_submenu'] === true;
                            $submenu = isset($dt['submenu']) && is_array($dt['submenu']) ? $dt['submenu'] : [];
                            $legacyFile = isset($dt['legacy_file']) ? $dt['legacy_file'] : '';
                            
                            // Jika punya submenu, buat dropdown
                            if ($hasSubmenu && count($submenu) > 0) {
                                echo '<li class="dropdown-submenu">';
                                echo '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'. htmlspecialchars($typeName) .' <span class="caret"></span></a>';
                                echo '<ul class="dropdown-menu">';
                                
                                foreach ($submenu as $sub) {
                                    if (!is_array($sub) || !isset($sub['name'])) continue;
                                    $subName = $sub['name'];
                                    $subLegacyFile = isset($sub['legacy_file']) ? $sub['legacy_file'] : '';
                                    
                                    // SMART ROUTING: Cek legacy file dulu
                                    if (!empty($subLegacyFile) && file_exists(__DIR__ . '/' . $subLegacyFile)) {
                                        // Legacy: Link ke file custom
                                        $url = $subLegacyFile;
                                    } else {
                                        // Modern: Link ke documents.php
                                        $url = 'documents.php?type=' . urlencode($typeName) . '&subtype=' . urlencode($subName);
                                    }
                                    
                                    echo '<li><a href="'. htmlspecialchars($url) .'">'. htmlspecialchars($subName) .'</a></li>';
                                }
                                
                                echo '</ul>';
                                echo '</li>';
                            } else {
                                // Tidak punya submenu
                                // SMART ROUTING: Cek legacy file dulu
                                if (!empty($legacyFile) && file_exists(__DIR__ . '/' . $legacyFile)) {
                                    // Legacy: Link ke file custom
                                    $url = $legacyFile;
                                } else {
                                    // Modern: Link ke documents.php
                                    $url = 'documents.php?type=' . urlencode($typeName);
                                }
                                
                                echo '<li><a href="'. htmlspecialchars($url) .'">'. htmlspecialchars($typeName) .'</a></li>';
                            }
                        }
                    } else {
                        // Fallback jika JSON kosong
                        echo '<li><a href="documents.php?type=Procedure">Procedure</a></li>';
                        echo '<li><a href="documents.php?type=WI">WI</a></li>';
                        echo '<li><a href="documents.php?type=Form">Form</a></li>';
                    }
                } else {
                    // Fallback jika file tidak ada
                    echo '<li><a href="documents.php?type=Procedure">Procedure</a></li>';
                    echo '<li><a href="documents.php?type=WI">WI</a></li>';
                    echo '<li><a href="documents.php?type=Form">Form</a></li>';
                }
                ?>
            </ul>
        </li>

        <!-- SEARCH -->
        <li>
            <a href="search.php">
                <img src="images/search3.png" alt="Search">
                Search
            </a>
        </li>

        <!-- GRAFIK -->
        <li>
            <a href="grafik.php" class="bg-info">
                <img src="images/graph.png" alt="Grafik">
                Grafik
            </a>
        </li>

        <!-- CHANGE PASSWORD -->
        <li>
            <a href="#" data-toggle="modal" data-target="#myModal3" data-usercp="<?php echo $nrp2;?>" data-passcp="<?php echo $pass2;?>">
                <img src="images/logoff.png" alt="Change Pass">
                Change
            </a>
        </li>
    
        <!-- MENU KHUSUS BERDASARKAN ROLE -->
        <?php if (isset($state) && $state=="Admin"){?>
            <li>
                <a href="config_head.php">
                    <img src="images/config.png" alt="Config">
                    Config
                </a>
            </li>
            <li class="pull-right">
                <a href="logout.php" onClick="return confirm('Logout?')">
                    <img src="images/logout.png" class="img-responsive" alt="Logout">
                    Logout
                </a>
            </li>
        <?php } else if (isset($state) && $state=="PIC"){ ?>
            <li class="pull-right">
                <a href="logout.php" onClick="return confirm('Logout?')" class="bg-info">
                    <img src="images/logout.png" class="img-responsive" alt="Logout">
                    Logout
                </a>
            </li>
        <?php } else { ?>
            <li>
                <a href="document/manual_approver.pdf">
                    <img src="images/help.png" class="img-responsive" alt="Help">
                    Help
                </a>
            </li>      
            <li class="pull-right bg-info">
                <a href="logout.php" onClick="return confirm('Logout?')">
                    <img src="images/logout.png" class="img-responsive" alt="Logout">
                    Logout
                </a>
            </li> 
        <?php } ?>
        </ul>

    </div>
    <br /><br /><br /><br />

    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1"></div>
  </div>
</nav>

<!-- CSS untuk Dropdown Submenu -->
<style>
.dropdown-submenu {
    position: relative;
}

.dropdown-submenu > .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -6px;
    margin-left: -1px;
}

.dropdown-submenu:hover > .dropdown-menu {
    display: block;
}

.dropdown-submenu > a:after {
    display: block;
    content: " ";
    float: right;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
    border-width: 5px 0 5px 5px;
    border-left-color: #ccc;
    margin-top: 5px;
    margin-right: -10px;
}

.dropdown-submenu:hover > a:after {
    border-left-color: #fff;
}

.dropdown-submenu.pull-left {
    float: none;
}

.dropdown-submenu.pull-left > .dropdown-menu {
    left: -100%;
    margin-left: 10px;
}
</style>

<!-- JavaScript untuk Dropdown Submenu -->
<script>
$(document).ready(function(){
    $('.dropdown-submenu a').on("click", function(e){
        var $submenu = $(this).next(".dropdown-menu");
        if($submenu.length) {
            $submenu.toggle();
            e.stopPropagation();
            e.preventDefault();
        }
    });
});
</script>

</body>

<div class="modal fade" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                 <h4 class="modal-title" id="myModalLabel">Change Password</h4>
            </div>
            <div class="modal-body">
                <form name="chg_pass" method="POST" action="chg_pass.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="user" id="user" class="form-control" value="<?php echo isset($nrp) ? htmlspecialchars($nrp) : ''; ?>"/>
                        <input type="hidden" name="pass" id="pass" class="form-control" value="<?php echo isset($pass) ? htmlspecialchars($pass) : ''; ?>"/>
                        <input type="password" name="lama" placeholder="Type Old Password" id="lama" class="form-control" value=""/>
                        <input type="password" name="baru" id="baru" placeholder="Type New Password " class="form-control">
                        <input type="password" name="conf" id="conf" placeholder="Confirm New Password" class="form-control">
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-default" data-dismiss="modal">Cancel</a>
                        <input type="submit" name="upload" value="Update" class="btn btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</html>