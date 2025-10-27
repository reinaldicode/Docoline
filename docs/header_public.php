<?php
// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<html>
<title>Document Control</title>
<head profile="http://www.global-sharp.com">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="bootstrap/css/facebook.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap/css/datepicker.css">

    <script src="bootstrap/js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="bootstrap/js/bootstrap-datepicker.js"></script>
    
    <script>
    $(document).ready(function(){
        // Inisialisasi dropdown Bootstrap
        $('.dropdown-toggle').dropdown();
        
        // Fix untuk mobile navigation
        $('.navbar-toggle').click(function() {
            $('.navbar-collapse').collapse('toggle');
        });
    });
    </script>

    <style> 
    body {
        background-image: url("images/white.jpeg");
        background-color: #cccccc;
        background-repeat: no-repeat;
        background-attachment: fixed;
    }
    
    /* CSS untuk Dropdown Submenu */
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
</head>

<body>
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <span class="navbar-brand"><h3>Document Control Online</h3></span>

                <ul class="nav navbar-nav">
                    <li><a href="index.php"><img src="images/home.png" class="img-responsive" alt="Home">Home</a></li>
                    
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <img src="images/document.png" alt="Documents"><br> Documents <span class="caret"></span>
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
                                                
                                                // PRIORITAS LEGACY FILE - Langsung ke file legacy tanpa login
                                                if (!empty($subLegacyFile)) {
                                                    $url = $subLegacyFile;
                                                } else {
                                                    $url = 'documents.php?type=' . urlencode($typeName) . '&subtype=' . urlencode($subName);
                                                }
                                                
                                                echo '<li><a href="'. htmlspecialchars($url) .'">'. htmlspecialchars($subName) .'</a></li>';
                                            }
                                            
                                            echo '</ul>';
                                            echo '</li>';
                                        } else {
                                            // Tidak punya submenu
                                            if (!empty($legacyFile)) {
                                                $url = $legacyFile;
                                            } else {
                                                $url = 'documents.php?type=' . urlencode($typeName);
                                            }
                                            
                                            echo '<li><a href="'. htmlspecialchars($url) .'">'. htmlspecialchars($typeName) .'</a></li>';
                                        }
                                    }
                                } else {
                                    // Fallback jika JSON kosong
                                    echo '<li><a href="dokawal.php">All Documents</a></li>';
                                    echo '<li role="separator" class="divider"></li>';
                                    echo '<li><a href="procedure_awal.php">Procedure</a></li>';
                                    echo '<li><a href="wi_awal.php">WI</a></li>';
                                    echo '<li><a href="form_awal.php">Form</a></li>';
                                }
                            } else {
                                // Fallback jika file tidak ada
                                echo '<li><a href="dokawal.php">All Documents</a></li>';
                                echo '<li role="separator" class="divider"></li>';
                                echo '<li><a href="procedure_awal.php">Procedure</a></li>';
                                echo '<li><a href="wi_awal.php">WI</a></li>';
                                echo '<li><a href="form_awal.php">Form</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    
                    <li><a href="search_awal.php" class="bg-success"><img src="images/search3.png" alt="Search"><br />Search</a></li>

                    <?php
                    // Tampilkan menu sesuai status login
                    if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
                        // User sudah login - tampilkan logout
                        echo '<li class="pull-right"><a href="logout.php"><img src="images/logout.png" class="img-responsive" alt="Logout"> &nbsp;Logout</a></li>';
                        echo '<li class="pull-right"><span class="navbar-text">Welcome, <strong>' . htmlspecialchars($_SESSION['username']) . '</strong></span></li>';
                    } else {
                        // User belum login - tampilkan login
                        echo '<li class="pull-right"><a href="login.php"><img src="images/login.png" class="img-responsive" alt="Login"> &nbsp;Login</a></li>';
                    }
                    ?>
                </ul>
            </div>
            
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1"></div>
        </div>
    </nav>
    
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
    
    <br /><br />
    
</body>
</html>