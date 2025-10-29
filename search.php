<?php
// ===== BYPASS LOGIN FOR PUBLIC ACCESS =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['username']) && !empty($_SESSION['username']);
$isAdmin = $isLoggedIn && (isset($_SESSION['state']) && $_SESSION['state'] === 'Admin');
$isOriginator = $isLoggedIn && (isset($_SESSION['state']) && $_SESSION['state'] === 'Originator');

// Jika tidak login, set mode public (read-only)
if (!$isLoggedIn) {
    if (file_exists('header_public.php')) {
        include('header_public.php');
    } else {
        include('header.php');
    }
} else {
    include('header.php');
}

include 'koneksi.php';
require_once('Connections/config.php');

$state = $isLoggedIn && isset($_SESSION['state']) ? $_SESSION['state'] : '';
$user_id = $isLoggedIn && isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

// ===== FUZZY SEARCH HELPER FUNCTIONS =====
/**
 * Normalize string untuk pencarian fuzzy
 * Menghapus karakter khusus, spasi berlebih, dan lowercase
 */
function normalizeString($str) {
    $str = strtolower(trim($str));
    // Hapus karakter non-alfanumerik kecuali dash dan underscore
    $str = preg_replace('/[^a-z0-9\-_]/', '', $str);
    return $str;
}

/**
 * Generate pattern pencarian fuzzy untuk LIKE query
 * Contoh: "o-w-ffd1_jk-1" -> "%o%w%ffd1%jk%1%"
 */
function generateFuzzyPattern($keyword) {
    $normalized = normalizeString($keyword);
    if (empty($normalized)) return '';
    
    // Split by dash dan underscore, lalu join dengan wildcard
    $parts = preg_split('/[\-_]+/', $normalized);
    $parts = array_filter($parts); // Remove empty
    
    if (empty($parts)) return '%' . $normalized . '%';
    
    return '%' . implode('%', $parts) . '%';
}

/**
 * Generate multiple search patterns untuk meningkatkan akurasi
 */
function generateSearchPatterns($keyword) {
    $patterns = [];
    $normalized = normalizeString($keyword);
    
    // Pattern 1: Exact dengan wildcard di awal dan akhir
    $patterns[] = '%' . mysqli_real_escape_string($GLOBALS['link'], $keyword) . '%';
    
    // Pattern 2: Normalized exact
    $patterns[] = '%' . mysqli_real_escape_string($GLOBALS['link'], $normalized) . '%';
    
    // Pattern 3: Fuzzy pattern (split by separator)
    $fuzzy = generateFuzzyPattern($keyword);
    if (!in_array($fuzzy, $patterns)) {
        $patterns[] = mysqli_real_escape_string($GLOBALS['link'], $fuzzy);
    }
    
    // Pattern 4: Each character separated by wildcard (untuk typo berat)
    if (strlen($normalized) >= 3 && strlen($normalized) <= 15) {
        $chars = str_split($normalized);
        $charPattern = '%' . implode('%', $chars) . '%';
        $patterns[] = mysqli_real_escape_string($GLOBALS['link'], $charPattern);
    }
    
    return array_unique($patterns);
}

/**
 * Build WHERE clause untuk fuzzy search pada field tertentu
 */
function buildFuzzyWhereClause($field, $keyword, $link) {
    if (empty(trim($keyword))) return '';
    
    $patterns = generateSearchPatterns($keyword);
    $conditions = [];
    
    foreach ($patterns as $pattern) {
        // Search pada field asli
        $conditions[] = "($field LIKE '$pattern')";
        
        // Search pada normalized field (lowercase, no special chars)
        $conditions[] = "(LOWER(REPLACE(REPLACE(REPLACE($field, '-', ''), '_', ''), ' ', '')) LIKE '" . 
                       str_replace(['%', '_'], ['%%', '__'], strtolower(str_replace(['%', '-', '_', ' '], '', $pattern))) . "')";
    }
    
    return '(' . implode(' OR ', $conditions) . ')';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search Dokumen</title>

    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="jquery-ui-1.10.3/themes/base/jquery.ui.all.css">
    <script src="bootstrap/js/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="jquery-ui-1.10.3/ui/jquery.ui.autocomplete.js"></script>

    <style>
        body { 
            background-image: url("images/white.jpeg"); 
            background-color: #cccccc; 
            background-repeat: no-repeat; 
            background-attachment: fixed;
            padding-top: 120px;
        }
        .search-card { 
            background: #fff; 
            border-radius: 12px; 
            padding: 20px; 
            box-shadow: 0 6px 20px rgba(31,45,61,0.08); 
            margin-bottom: 18px; 
        }
        .controls { margin-top: 10px; }
        .muted-small { font-size: 12px; color:#6c757d; }
        .badge-info-custom {
            background-color: #f0f2f5;
            color: #555;
            border: 1px solid #e0e0e0;
            border-radius: 999px;
            padding: 6px 12px;
            font-weight: 600;
        }
        .btn-perpage .btn { margin-right:6px; }
        .perpage-active { box-shadow: inset 0 -3px 0 rgba(0,0,0,0.08); }
        
        .table-modern {
            width: 100%;
            background: transparent;
            box-shadow: none;
        }
        .table-modern thead {
            background: #f8f9fa;
            color: #333;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .table-modern thead th {
            border: 1px solid #ddd;
            padding: 8px;
            font-weight: 600;
            font-size: 13px;
        }
        .table-modern tbody td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            font-size: 13px;
            vertical-align: middle;
            background: transparent;
        }
        .table-modern tbody tr:hover {
            background: rgba(0,0,0,0.03);
        }
        .btn-xs {
            padding: 2px 6px;
            font-size: 11px;
            line-height: 1.5;
            border-radius: 3px;
            margin: 1px;
        }
        .fuzzy-hint {
            font-size: 11px;
            color: #28a745;
            margin-top: 4px;
        }
        
        @media(max-width:767px){
            body { padding-top: 150px; }
            .search-card { padding:12px; }
            .controls .btn { margin-bottom:8px; }
        }
    </style>

    <script>
    $(document).ready(function() {
        // REMOVED: Auto-replace space with dash untuk No. Document
        // Biarkan user mengetik dengan natural, sistem akan handle fuzzy search
        
        // Sanitasi untuk DRF: hanya izinkan angka
        $('input[name="drf"]').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
    </script>
</head>
<body>

<div class="container">
    <div class="search-card">
        <h4 style="margin-top:0">Search Dokumen <small class="muted-small">(No Doc, Title, Employee ID, Type, Month, Year, DRF)</small></h4>

        <?php
        $types = [];
        $qtypes = "SELECT DISTINCT doc_type FROM docu WHERE doc_type IS NOT NULL AND doc_type <> '' ORDER BY doc_type";
        $rtypes = mysqli_query($link, $qtypes);
        if ($rtypes) {
            while ($t = mysqli_fetch_assoc($rtypes)) {
                $types[] = $t['doc_type'];
            }
            mysqli_free_result($rtypes);
        }
        $currentPerPage = $_GET['perPage'] ?? '20';
        $currentSort = $_GET['sort'] ?? 'oldest';
        ?>

        <form id="searchForm" method="GET" action="" class="form-horizontal">
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label">No Document</label>
                        <input type="text" id="doc_no_input" name="doc_no" class="form-control" value="<?php echo htmlspecialchars($_GET['doc_no'] ?? ''); ?>" placeholder="e.g DC-001 atau DC 001 atau dc001">

                    </div>

                    <div class="form-group">
                        <label class="control-label">Title</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($_GET['title'] ?? ''); ?>" placeholder="Masukkan kata kunci title">
                    </div>

                    <div class="form-group">
                        <label class="control-label">DRF (No. DRF)</label>
                        <input type="text" id="drf_input" name="drf" class="form-control" value="<?php echo htmlspecialchars($_GET['drf'] ?? ''); ?>" placeholder="Masukan No. DRF (mis. 17246)">
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Employee ID</label>
                        <input type="text" name="empid" class="form-control" value="<?php echo htmlspecialchars($_GET['empid'] ?? ''); ?>" placeholder="Employee ID">
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label class="control-label">Month</label>
                            <select name="bulan" class="form-control">
                                <option value="00">Select Month</option>
                                <?php
                                $months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
                                $currentBulan = $_GET['bulan'] ?? '00';
                                foreach($months as $k=>$v){
                                    $sel = ($currentBulan == $k) ? 'selected' : '';
                                    echo "<option value=\"$k\" $sel>$v</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label class="control-label">Year</label>
                            <select name="tahun" class="form-control">
                                <option value="00">Select Year</option>
                                <?php 
                                $currentTahun = $_GET['tahun'] ?? '00';
                                for ($year = 2015; $year <= date('Y') ; $year++): 
                                    $sel = ($currentTahun == $year) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $year;?>" <?php echo $sel;?>><?php echo $year;?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Type Document</label>
                        <select name="doc_type" class="form-control">
                            <option value="">-- Semua Type --</option>
                            <?php 
                            $currentDocType = $_GET['doc_type'] ?? '';
                            foreach ($types as $dt): 
                            ?>
                                <option value="<?php echo htmlspecialchars($dt); ?>" <?php if($currentDocType == $dt) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($dt); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="control-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <?php 
                            $currentStatus = $_GET['status'] ?? '';
                            $statusOptions = ['Approved', 'Secured', 'Obsolete'];
                            foreach ($statusOptions as $st): 
                            ?>
                                <option value="<?php echo $st; ?>" <?php if($currentStatus == $st) echo 'selected'; ?>>
                                    <?php echo $st; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="controls clearfix">
                <input type="hidden" id="perPageInput" name="perPage" value="<?php echo htmlspecialchars($currentPerPage); ?>">
                <input type="hidden" id="sortInput" name="sort" value="<?php echo htmlspecialchars($currentSort); ?>">

                <button type="submit" name="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> Search</button>
                <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="resetBtn" class="btn btn-default"><span class="glyphicon glyphicon-refresh"></span> Reset</a>
                
                <div class="btn-perpage pull-right" style="margin-left:10px;">
                    <div class="btn-group" role="group" aria-label="Per page">
                        <?php
                        $perOptions = ['10','20','30','50','all']; 
                        foreach ($perOptions as $opt) {
                            $label = ($opt === 'all') ? 'All' : $opt;
                            $active = ($currentPerPage === $opt) ? ' btn-primary' : ' btn-default'; 
                            echo '<button type="button" data-per="'.$opt.'" class="btn btn-sm'.$active.'">'.$label.'</button>';
                        }
                        ?>
                    </div>
                </div>

                <div class="pull-right">
                    <label style="display:inline-block;margin: 5px 8px 0 0;font-weight:normal;" class="muted-small">Sort by</label>
                    <select id="sortSelect" class="form-control input-sm" style="display:inline-block;width:auto;">
                        <option value="oldest" <?php if($currentSort=='oldest') echo 'selected'; ?>>Oldest first (terlama → terbaru)</option>
                        <option value="newest" <?php if($currentSort=='newest') echo 'selected'; ?>>Newest first (terbaru → terlama)</option>                      
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
// ===== 🔥 HELPER FUNCTION: BUILD FILE PATH (HYBRID) =====
function build_hybrid_file_path($row) {
    $drf = intval($row['no_drf']);
    $doc_type = $row['doc_type'];
    $filename = $row['file'];
    
    // Legacy files (DRF ≤ 12955): Type/file.pdf
    if ($drf <= 12955) {
        return htmlspecialchars($doc_type . '/' . $filename);
    }
    
    // New files (DRF > 12955): documents/Type/file.pdf
    return htmlspecialchars('documents/' . $doc_type . '/' . $filename);
}

// Processing search
if (isset($_GET['submit']) || isset($_GET['perPage']) || isset($_GET['page']) || isset($_GET['sort']) || isset($_GET['drf'])) {
    // Sanitize input dengan FUZZY SEARCH support
    $doc_no_raw = isset($_GET['doc_no']) ? trim($_GET['doc_no']) : '';
    $title  = isset($_GET['title']) ? mysqli_real_escape_string($link, trim($_GET['title'])) : '';
    $empid  = isset($_GET['empid']) ? mysqli_real_escape_string($link, trim($_GET['empid'])) : '';
    $doc_type = isset($_GET['doc_type']) ? mysqli_real_escape_string($link, trim($_GET['doc_type'])) : '';
    $bulan  = isset($_GET['bulan']) ? mysqli_real_escape_string($link, trim($_GET['bulan'])) : '00';
    $tahun  = isset($_GET['tahun']) ? mysqli_real_escape_string($link, trim($_GET['tahun'])) : '00';
    $status_filter = isset($_GET['status']) ? mysqli_real_escape_string($link, trim($_GET['status'])) : '';
    $perPageRaw = $_GET['perPage'] ?? '20';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'oldest';
    if (!in_array($sort, ['oldest','newest'])) $sort = 'oldest';

    $drf_search = isset($_GET['drf']) ? mysqli_real_escape_string($link, trim($_GET['drf'])) : '';

    $whereParts = [];
    
    // ===== FUZZY SEARCH untuk No Document =====
    if ($doc_no_raw !== '') {
        $fuzzyWhere = buildFuzzyWhereClause('no_doc', $doc_no_raw, $link);
        if (!empty($fuzzyWhere)) {
            $whereParts[] = $fuzzyWhere;
        }
    }
    
    if ($title !== '') {
        $whereParts[] = "(title LIKE '%$title%')";
    }
    if ($empid !== '') {
        $whereParts[] = "(user_id = '$empid')";
    }
    if ($doc_type !== '') {
        $whereParts[] = "(doc_type = '$doc_type')";
    }
    if ($status_filter !== '') {
        $whereParts[] = "(status = '$status_filter')";
    }
    if ($drf_search !== '') {
        $whereParts[] = "(no_drf LIKE '%$drf_search%')";
    }
    if ($bulan !== '00' && $tahun !== '00') {
        $whereParts[] = "(MID(tgl_upload,4,2) = '$bulan' AND RIGHT(tgl_upload,4) = '$tahun')";
    }

    $where = (count($whereParts) > 0) ? ' WHERE ' . implode(' AND ', $whereParts) : '';

    $isAll = ($perPageRaw === 'all');
    $perPage = $isAll ? 0 : (int)$perPageRaw;
    if (!$isAll && $perPage <= 0) $perPage = 20;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $perPage;

    $countSql = "SELECT COUNT(*) AS total FROM docu $where";
    $countRes = mysqli_query($link, $countSql);
    $totalRows = 0;
    if ($countRes) {
        $totalRows = (int)mysqli_fetch_assoc($countRes)['total'];
        mysqli_free_result($countRes);
    }

    // Main query with sorting
    $orderDir = ($sort === 'oldest') ? 'ASC' : 'DESC';
    $sql = "SELECT * FROM docu $where ORDER BY no_drf $orderDir";
    if (!$isAll) $sql .= " LIMIT $offset,$perPage";
    $res = mysqli_query($link, $sql);

    function build_page_url($page_number) {
        $params = $_GET;
        $params['page'] = $page_number;
        if (!isset($params['perPage'])) $params['perPage'] = '20';
        if (!isset($params['sort'])) $params['sort'] = 'oldest';
        return htmlspecialchars($_SERVER['PHP_SELF'] . '?' . http_build_query($params));
    }

    if ($totalRows > 0) {
        $startRow = $isAll ? 1 : $offset + 1;
        $endRow   = $isAll ? $totalRows : min($offset + $perPage, $totalRows);
        echo '<div class="container">';
        echo '<div style="margin-bottom: 18px; padding: 0 15px;">';
        echo '<span class="badge-info-custom">Results</span> Menampilkan <strong>'.$startRow.'</strong> - <strong>'.$endRow.'</strong> dari <strong>'.$totalRows.'</strong>';
        if (!empty($doc_no_raw)) {
            echo ' <small class="muted-small">(Pencarian fuzzy untuk: "'.htmlspecialchars($doc_no_raw).'")</small>';
        }
        echo ' &nbsp; <small class="muted-small">Sort: '.htmlspecialchars(($sort==='oldest'?'Oldest first':'Newest first')).'</small>';
        echo '</div>';
        echo '</div>';
    } else {
        echo '<div class="container"><div style="margin-bottom: 18px; padding: 0 15px;"><div class="alert alert-warning">Tidak ada hasil untuk filter tersebut.</div></div></div>';
    }
?>

<?php if ($totalRows > 0): ?>
<div class="container">
<div id="resultsContainer" style="padding:0 15px;">
    <div class="table-responsive">
        <table class="table table-hover table-modern">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Date</th>
                    <th>No Document</th>
                    <th>No Rev.</th>
                    <th>No Drf</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Process</th>
                    <th>Section</th>
                    <th>Device</th>
                    <th>Detail</th>
                    <?php if ($isAdmin): ?>
                    <th>Action</th>
                    <?php endif; ?>
                    <?php if ($isLoggedIn): /* ✅ Kolom Evidence tampil untuk SEMUA user yang login */ ?>
                    <th>Evidence</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = $isAll ? 1 : $offset + 1;
                while ($row = mysqli_fetch_assoc($res)) {
                    $has_sos = !empty($row['sos_file']);
                    
                    echo '<tr>';
                    echo '<td>'. $i .'</td>';
                    echo '<td>'. htmlspecialchars($row['tgl_upload']) .'</td>';
                    echo '<td>'. htmlspecialchars($row['no_doc']) .'</td>';
                    echo '<td>'. htmlspecialchars($row['no_rev']) .'</td>';
                    echo '<td>'. htmlspecialchars($row['no_drf']) .'</td>';

                    // ===== 🔥 GUNAKAN HYBRID PATH =====
                    $filePath = build_hybrid_file_path($row);

                    echo '<td><a href="'. $filePath .'" target="_blank">'. htmlspecialchars($row['title']) .'</a></td>';
                    echo '<td>'. htmlspecialchars($row['status']) .'</td>';
                    echo '<td>'. htmlspecialchars($row['doc_type']) .'</td>';
                    echo '<td>'. htmlspecialchars($row['process']) .'</td>';
                    echo '<td>'. htmlspecialchars($row['section']) .'</td>';
                    echo '<td>'. htmlspecialchars($row['device']) .'</td>';
                    
                    // ===== DETAIL COLUMN (UNTUK SEMUA USER) =====
                    echo '<td style="white-space:nowrap;">';
                    echo '<a class="btn btn-xs btn-info" title="Lihat Detail" href="detail.php?drf='.urlencode($row['no_drf']).'&no_doc='.urlencode($row['no_doc']).'">
                            <span class="glyphicon glyphicon-search"></span>
                          </a>';
                    echo '<a class="btn btn-xs btn-info" title="Lihat RADF" href="radf.php?drf='.urlencode($row['no_drf']).'&section='.urlencode($row['section']).'">
                            <span class="glyphicon glyphicon-eye-open"></span>
                          </a>';
                    echo '<a class="btn btn-xs btn-info" title="Lihat Approver" href="lihat_approver.php?drf='.urlencode($row['no_drf']).'">
                            <span class="glyphicon glyphicon-user"></span>
                          </a>';
                    echo '</td>';
                    
                    // ===== ACTION COLUMN (HANYA ADMIN) =====
                    if ($isAdmin) {
                        echo '<td style="white-space:nowrap;">';
                        
                        echo '<a href="edit_doc.php?drf='.urlencode($row['no_drf']).'" class="btn btn-xs btn-primary" title="Edit Doc">
                                <span class="glyphicon glyphicon-pencil"></span>
                              </a>';
                        
                        echo '<a href="del_doc.php?drf='.urlencode($row['no_drf']).'" class="btn btn-xs btn-danger" onClick="return confirm(\'Delete document '.htmlspecialchars($row['no_doc']).'?\')" title="Delete Doc">
                                <span class="glyphicon glyphicon-remove"></span>
                              </a>';
                        
                        if (isset($row['status']) && $row['status'] == 'Approved') {
                            echo '<a data-toggle="modal" data-target="#myModal2" 
                                     data-id="'.htmlspecialchars($row['no_drf']).'" 
                                     data-nodoc="'.htmlspecialchars($row['no_doc']).'"
                                     data-lama="'.htmlspecialchars($row['file']).'"
                                     data-status="'.htmlspecialchars($row['status']).'"
                                     class="btn btn-xs btn-success sec-file" title="Secure Document">
                                    <span class="glyphicon glyphicon-play"></span>
                                  </a>';
                        }
                        
                        echo '<a href="ganti_doc.php?drf='.urlencode($row['no_drf']).'&type='.urlencode($row['doc_type']).'" 
                                class="btn btn-xs btn-warning" title="Ganti Doc">
                                <span class="glyphicon glyphicon-refresh"></span> Ganti
                              </a>';
                        
                        echo '</td>';
                    }
                    
                    // ===== EVIDENCE COLUMN (SEMUA USER YANG LOGIN) =====
                    if ($isLoggedIn) { 
                        echo '<td style="white-space:nowrap;">';
                        
                        if ($has_sos) {
                            // ✅ Jika SUDAH ada file: Semua user yang login bisa "Lihat"
                            echo '<a href="lihat_evidence.php?drf='.urlencode($row['no_drf']).'" class="btn btn-xs btn-primary" title="Lihat Detail Evidence">
                                    <span class="glyphicon glyphicon-file"></span> Lihat
                                  </a>';
                        } else {
                            // ✅ Jika BELUM ada file:
                            if ($isAdmin || $isOriginator) {
                                // Admin & Originator: Tampilkan tombol "Upload"
                                echo '<button type="button"
                                        class="btn btn-xs btn-success btn-upload-sos"
                                        data-drf="'.htmlspecialchars($row['no_drf']).'"
                                        data-nodoc="'.htmlspecialchars($row['no_doc']).'"
                                        title="Upload Evidence">
                                        <span class="glyphicon glyphicon-upload"></span> Upload
                                      </button>';
                            } else {
                                // Approver & PIC: Tampilkan pesan "Belum ada"
                                echo '<span class="text-muted" style="font-size:11px;">Belum ada</span>';
                            }
                        }
                        
                        echo '</td>';
                    }
                    
                    echo '</tr>';
                    $i++;
                }
                mysqli_free_result($res);
                ?>
            </tbody>
        </table>
    </div>

    <?php
    // Pagination
    if (!$isAll && $totalRows > 0) {
        $totalPages = ceil($totalRows / $perPage);
        if ($totalPages > 1) {
            echo '<nav><ul class="pagination justify-content-center">';
            
            if ($page > 1) {
                echo '<li><a href="'.build_page_url($page-1).'">Prev</a></li>';
            }
            
            $range = 2;
            for ($p = max(1, $page - $range); $p <= min($totalPages, $page + $range); $p++) {
                $active = ($p==$page)?' class="active"':'';
                echo '<li'.$active.'><a href="'.build_page_url($p).'">'.$p.'</a></li>';
            }
            
            if ($page < $totalPages) {
                echo '<li><a href="'.build_page_url($page+1).'">Next</a></li>';
            }
            
            echo '</ul></nav>';
        }
    }
    ?>
</div>
</div>
<?php endif; ?>

<?php } // end processing ?>

<?php if ($isAdmin): ?>
<!-- ===== MODAL SECURE DOCUMENT (HANYA ADMIN) ===== -->
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Secure Document</h4>
            </div>
            <div class="modal-body">
                <form name="secure_doc" method="POST" action="process.php" enctype="multipart/form-data">
                    <input type="hidden" name="drf" id="drf" class="form-control" value=""/>
                    <input type="hidden" name="lama" id="lama" class="form-control" value=""/>
                    <input type="hidden" name="status" id="status" class="form-control" value=""/>
                    <div class="form-group">
                        <label>Upload New Secured File:</label>
                        <input type="file" name="baru" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-default" data-dismiss="modal">Cancel</a>
                        <input type="submit" name="upload" value="Update" class="btn btn-primary" onclick="return confirm('Are you sure you want to secure this document?');">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($isAdmin || $isOriginator): /* ✅ Modal HANYA untuk Admin & Originator */ ?>
<!-- ===== MODAL UPLOAD EVIDENCE (ADMIN & ORIGINATOR ONLY) ===== -->
<div class="modal fade" id="modalSosialisasi" tabindex="-1" role="dialog" aria-labelledby="modalSosLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="upload_Evidence.php" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="modalSosLabel">Upload Evidence</h4>
                </div>
                <div class="modal-body">
                    <p>Upload Evidence untuk No. Document: <strong id="modal_upload_nodoc"></strong></p>
                    <input type="hidden" name="drf" id="modal_upload_drf" value="">
                    <?php
                    // CSRF token
                    if (empty($_SESSION['csrf_token'])) {
                        if (function_exists('random_bytes')) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        } else {
                            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
                        }
                    }
                    ?>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label>File bukti (pdf / jpg / png)</label>
                        <input type="file" name="sos_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <div class="form-group">
                        <label>Catatan / Keterangan</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Tuliskan catatan atau keterangan evidence..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" name="upload_evidence" class="btn btn-success">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
(function(){
    const form = document.getElementById('searchForm');
    const resetBtn = document.getElementById('resetBtn');
    const perPageInput = document.getElementById('perPageInput');
    const sortInput = document.getElementById('sortInput');
    const sortSelect = document.getElementById('sortSelect');

    // Sort dropdown change handler
    if (sortSelect && sortInput) {
        sortSelect.addEventListener('change', function(){
            sortInput.value = this.value;
            form.submit();
        });
    }

    // Per page buttons handler
    document.querySelectorAll('.btn-perpage button').forEach(function(b){
        b.addEventListener('click', function(){
            const per = this.getAttribute('data-per');
            if (per === 'all') {
                if (!confirm('Menampilkan semua hasil mungkin akan memakan waktu. Lanjutkan?')) {
                    return;
                }
            }
            if (perPageInput) {
                perPageInput.value = per;
            }
            form.submit();
        });
    });

    // Reset button handler
    if (resetBtn && form) {
        resetBtn.addEventListener('click', function(e){
            e.preventDefault();
            window.location.href = window.location.pathname;
        });
    }

    <?php if ($isAdmin || $isOriginator): /* ✅ Modal handlers HANYA untuk Admin & Originator */ ?>
    // Modal handlers (hanya untuk Admin & Originator)
    document.addEventListener('click', function(e){
        <?php if ($isAdmin): ?>
        // Modal Secure Document (hanya Admin)
        if (e.target.closest('.sec-file')) {
            const el = e.target.closest('.sec-file');
            document.querySelector('#myModal2 #drf').value = el.getAttribute('data-id') || '';
            document.querySelector('#myModal2 #lama').value = el.getAttribute('data-lama') || '';
            document.querySelector('#myModal2 #status').value = el.getAttribute('data-status') || '';
        }
        <?php endif; ?>
        
        // Modal Upload Evidence (Admin & Originator)
        if (e.target.closest('.btn-upload-sos')) {
            e.preventDefault();
            
            // Reset form terlebih dahulu
            $('#modalSosialisasi').find('form')[0].reset();
            
            // Isi data baru
            const btn = e.target.closest('.btn-upload-sos');
            const drf = btn.getAttribute('data-drf');
            const nodoc = btn.getAttribute('data-nodoc');
            
            document.getElementById('modal_upload_drf').value = drf;
            document.getElementById('modal_upload_nodoc').textContent = nodoc;
            
            // Show modal
            $('#modalSosialisasi').modal('show');
        }
    });
    <?php endif; ?>
})();
</script>

</body>
</html>