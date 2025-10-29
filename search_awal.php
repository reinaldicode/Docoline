<?php
// search_awal.php
// Filter: no_doc, title, no_drf, doc_type (dropdown), status (dropdown).
// Pagination with per-page buttons (10,20,50,All).
// Table transparan, header sticky, pagination tetap di bawah.

include('index.php');    // header / session bila perlu
include 'koneksi.php';
require_once('Connections/config.php');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Search Awal</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* ===== UI ===== */
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
    .btn-perpage .btn { margin-right:6px; }
    .badge-info-custom {
      background-color: #f0f2f5;
      color: #555;
      border: 1px solid #e0e0e0;
      border-radius: 999px;
      padding: 6px 12px;
      font-weight: 600;
    }
    .muted-small { font-size: 12px; color:#6c757d; }
    .perpage-active { box-shadow: inset 0 -3px 0 rgba(0,0,0,0.08); }
    .pagination > li > a, .pagination > li > span { border-radius:6px; }
    .search-input .input-group-addon { background: #fff; border-right:0; }
    .search-input .form-control { border-left:0; }

    /* ===== Table ===== */
    .table-modern {
      width: 100%;
      background: transparent;
    }
    .table-modern thead {
      background: #f8f9fa;
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
    .table-modern tbody tr:hover { background: rgba(0,0,0,0.03); }
    .btn-xs {
      padding: 2px 6px;
      font-size: 11px;
      line-height: 1.5;
      border-radius: 3px;
      margin: 1px;
    }

    @media(max-width:767px){
      body { padding-top: 150px; }
      .search-card { padding:12px; }
      .controls .btn { margin-bottom:8px; }
    }
  </style>
</head>
<body>
<br/><br/>

<div class="container">
  <div class="search-card">
    <h4 style="margin-top:0">Search Dokumen <small class="muted-small">(No Doc, Title, No. Drf, Type, Status)</small></h4>

    <?php
    // Ambil daftar doc_type untuk dropdown
    $types = [];
    $qtypes = "SELECT DISTINCT doc_type FROM docu WHERE doc_type IS NOT NULL AND doc_type <> '' ORDER BY doc_type";
    $rtypes = mysqli_query($link, $qtypes);
    if ($rtypes) {
      while ($t = mysqli_fetch_assoc($rtypes)) {
        $types[] = $t['doc_type'];
      }
      mysqli_free_result($rtypes);
    }

    $currentPerPage = isset($_GET['perPage']) ? $_GET['perPage'] : '20';
    $currentSort = isset($_GET['sort']) ? $_GET['sort'] : 'oldest';
    if (!in_array($currentSort, ['oldest','newest'])) $currentSort = 'oldest';
    ?>

    <!-- FORM -->
    <form id="searchForm" method="GET" action="" class="form-horizontal">
      <div class="row">
        <div class="col-sm-6">
          <div class="form-group search-input">
            <label class="control-label">No Document</label>
            <div class="input-group">
              <span class="input-group-addon"><span class="glyphicon glyphicon-folder-open"></span></span>
              <input type="text" id="doc_no" name="doc_no" class="form-control" value="<?php echo isset($_GET['doc_no']) ? htmlspecialchars($_GET['doc_no']) : ''; ?>" placeholder="e.g. DC-001">
            </div>
          </div>

          <div class="form-group search-input">
            <label class="control-label">Title</label>
            <div class="input-group">
              <span class="input-group-addon"><span class="glyphicon glyphicon-book"></span></span>
              <input type="text" name="title" class="form-control" value="<?php echo isset($_GET['title']) ? htmlspecialchars($_GET['title']) : ''; ?>" placeholder="Masukkan kata kunci title">
            </div>
          </div>

          <div class="form-group search-input">
            <label class="control-label">No. Drf</label>
            <div class="input-group">
              <span class="input-group-addon"><span class="glyphicon glyphicon-list-alt"></span></span>
              <input type="text" id="no_drf" name="no_drf" class="form-control" value="<?php echo isset($_GET['no_drf']) ? htmlspecialchars($_GET['no_drf']) : ''; ?>" placeholder="Nomor DRF (hanya angka)">
            </div>
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            <label class="control-label">Type Document</label>
            <select name="doc_type" class="form-control">
              <option value="">-- Semua Type --</option>
              <?php foreach ($types as $dt): ?>
                <option value="<?php echo htmlspecialchars($dt); ?>" <?php if(isset($_GET['doc_type']) && $_GET['doc_type']==$dt) echo 'selected'; ?>>
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
        <button type="button" id="resetBtn" class="btn btn-default"><span class="glyphicon glyphicon-refresh"></span> Reset</button>

        <!-- per-page buttons -->
        <div class="btn-perpage pull-right" style="margin-left:10px;">
          <div class="btn-group" role="group" aria-label="Per page">
            <?php
              $perOptions = ['10','20','30','50','all'];
              foreach ($perOptions as $opt) {
                $label = ($opt === 'all') ? 'All' : $opt;
                $active = ($currentPerPage === $opt) ? ' perpage-active' : '';
                echo '<button type="button" data-per="'.$opt.'" class="btn btn-sm btn-default'.$active.'">'.$label.'</button>';
              }
            ?>
          </div>
        </div>

        <!-- Sort control -->
        <div class="pull-right" style="margin-right:10px;">
          <label style="display:inline-block;margin-right:8px;" class="muted-small">Sort</label>
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

if (isset($_GET['submit']) || isset($_GET['perPage']) || isset($_GET['page']) || isset($_GET['sort'])) {
  // Sanitasi untuk No Document: replace spasi dengan strip
  $doc_no_raw = trim($_GET['doc_no'] ?? '');
  $doc_no_sanitized = str_replace(' ', '-', $doc_no_raw);
  $doc_no = mysqli_real_escape_string($link, $doc_no_sanitized);
  
  // Sanitasi untuk No. Drf: hanya angka
  $no_drf_raw = trim($_GET['no_drf'] ?? '');
  $no_drf_sanitized = preg_replace('/[^0-9]/', '', $no_drf_raw);
  $no_drf = mysqli_real_escape_string($link, $no_drf_sanitized);
  
  $title  = mysqli_real_escape_string($link, trim($_GET['title'] ?? ''));
  $doc_type = mysqli_real_escape_string($link, trim($_GET['doc_type'] ?? ''));
  $status_filter = mysqli_real_escape_string($link, trim($_GET['status'] ?? ''));
  $perPageRaw = $_GET['perPage'] ?? '20';
  $sort = isset($_GET['sort']) ? $_GET['sort'] : 'oldest';
  if (!in_array($sort, ['oldest','newest'])) $sort = 'oldest';

  $whereParts = [];
  if ($doc_no !== '') $whereParts[] = "no_doc LIKE '%$doc_no%'";
  if ($title !== '')  $whereParts[] = "title LIKE '%$title%'";
  if ($no_drf !== '') $whereParts[] = "no_drf LIKE '%$no_drf%'";
  if ($doc_type !== '') $whereParts[] = "doc_type = '$doc_type'";
  if ($status_filter !== '') $whereParts[] = "status = '$status_filter'";
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

  // ORDER BY using STR_TO_DATE to sort by tgl_upload
  $orderDir = ($sort === 'oldest') ? 'ASC' : 'DESC';
  $sql = "SELECT * FROM docu $where ORDER BY STR_TO_DATE(REPLACE(tgl_upload,'/','-'), '%d-%m-%Y') $orderDir";
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
    echo ' &nbsp; <small class="muted-small">Sort: '.htmlspecialchars(($sort === 'oldest' ? 'Oldest first' : 'Newest first')).'</small>';
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
            <th>No. Drf</th>
            <th>Title</th>
            <th>Status</th>
            <th>Type</th>
            <th>Section</th>
            <th>Device</th>
            <th>Process</th>
            <th>Action</th>
            <th>Evidence</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $i = $isAll ? 1 : $offset + 1;
            while ($row = mysqli_fetch_assoc($res)) {
              // periksa apakah ada bukti sosialisasi
              $has_sos = !empty($row['sos_file']);
              
              echo '<tr>';
              echo '<td>'.$i.'</td>';
              echo '<td>'.htmlspecialchars($row['tgl_upload']).'</td>';
              echo '<td>'.htmlspecialchars($row['no_doc']).'</td>';
              echo '<td>'.htmlspecialchars($row['no_rev']).'</td>';
              echo '<td>'.htmlspecialchars($row['no_drf']).'</td>';
              
              // ===== 🔥 GUNAKAN HYBRID PATH =====
              $filePath = build_hybrid_file_path($row);
              
              echo '<td><a href="'.$filePath.'" target="_blank">'.htmlspecialchars($row['title']).'</a></td>';
              echo '<td>'.htmlspecialchars($row['status']).'</td>';
              echo '<td>'.htmlspecialchars($row['doc_type']).'</td>';
              echo '<td>'.htmlspecialchars($row['section']).'</td>';
              echo '<td>'.htmlspecialchars($row['device']).'</td>';
              echo '<td>'.htmlspecialchars($row['process']).'</td>';
              
              // ===== KOLOM ACTION (DETAIL, RADF, APPROVER) =====
              echo '<td style="white-space:nowrap;">';
              
              // 1. Button Lihat Detail
              echo '<a class="btn btn-xs btn-info" title="Lihat Detail" href="detail.php?drf='.urlencode($row['no_drf']).'&no_doc='.urlencode($row['no_doc']).'&public=1">
                      <span class="glyphicon glyphicon-search"></span>
                    </a>';
              
              // 2. Button Lihat RADF
              echo '<a class="btn btn-xs btn-info" title="Lihat RADF" href="radf.php?drf='.urlencode($row['no_drf']).'&section='.urlencode($row['section']).'">
                      <span class="glyphicon glyphicon-eye-open"></span>
                    </a>';
              
              // 3. Button Lihat Approver (✅ YANG HILANG INI!)
              echo '<a class="btn btn-xs btn-info" title="Lihat Approver" href="lihat_approver.php?drf='.urlencode($row['no_drf']).'">
                      <span class="glyphicon glyphicon-user"></span>
                    </a>';
              
              echo '</td>';
              
              // ===== KOLOM EVIDENCE =====
              echo '<td>';
              if ($has_sos) {
                  echo '<a href="lihat_evidence.php?drf='.urlencode($row['no_drf']).'" class="btn btn-xs btn-primary" title="Lihat Detail Evidence">';
                  echo '<span class="glyphicon glyphicon-file"></span>';
                  echo '</a>';
              } else {
                  echo '<a href="lihat_evidence.php?drf='.urlencode($row['no_drf']).'" class="btn btn-xs btn-default" title="Belum ada evidence">';
                  echo '<span class="glyphicon glyphicon-file"></span>';
                  echo '</a>';
              }
              echo '</td>';
              
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
      if (!$isAll) {
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
<?php } ?>

<script>
(function(){
  const form = document.getElementById('searchForm');
  const resetBtn = document.getElementById('resetBtn');
  const results = document.getElementById('resultsContainer');
  const perPageInput = document.getElementById('perPageInput');
  const sortInput = document.getElementById('sortInput');
  const sortSelect = document.getElementById('sortSelect');
  const docNoInput = document.getElementById('doc_no');
  const noDrfInput = document.getElementById('no_drf');

  // Sanitasi No Document: otomatis ganti spasi dengan strip
  if (docNoInput) {
    docNoInput.addEventListener('input', function(e) {
      let val = this.value;
      val = val.replace(/ /g, '-');
      this.value = val;
    });
  }

  // Sanitasi No. Drf: hanya angka
  if (noDrfInput) {
    noDrfInput.addEventListener('input', function(e) {
      let val = this.value;
      val = val.replace(/[^0-9]/g, '');
      this.value = val;
    });
  }

  // Set initial sortInput from select
  if (sortSelect && sortInput) {
    sortInput.value = sortSelect.value;
    sortSelect.addEventListener('change', function(){
      sortInput.value = this.value;
      form.submit();
    });
  }

  // Event untuk tombol perPage
  document.querySelectorAll('.btn-perpage button').forEach(function(b){
    b.addEventListener('click', function(){
      const per = this.getAttribute('data-per');
      if (per === 'all') {
        if (!confirm('Menampilkan semua hasil dapat berat. Lanjutkan?')) return;
      }
      if (perPageInput) perPageInput.value = per;
      document.querySelectorAll('.btn-perpage button').forEach(x => x.classList.remove('perpage-active'));
      this.classList.add('perpage-active');
      form.submit();
    });
  });

  // Reset button
  if (resetBtn && form) {
    resetBtn.addEventListener('click', function(){
      form.querySelectorAll('input[type="text"]').forEach(i => i.value='');
      form.querySelectorAll('select').forEach(s => s.selectedIndex=0);
      if (perPageInput) perPageInput.value = '20';
      if (sortInput) sortInput.value = 'oldest';
      if (sortSelect) sortSelect.value = 'oldest';
      if (results) results.style.display = 'none';
      if (window.history && history.replaceState) {
        const cleanUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
        history.replaceState({}, '', cleanUrl);
      }
    });
  }
})();
</script>

</body>
</html>