<?php
include "form_login.php";

// 🆕 LOAD SMART WRAPPER
require_once('legacy_wrapper.php');
$wrapper = new LegacyFilterWrapper('Form', $link);

// Ambil session state
$state = $_SESSION['state'] ?? '';
$nrp = $_SESSION['nrp'] ?? '';
?>

<br />

<!-- jQuery -->
<script type="text/javascript" src="bootstrap/js/jquery.min.js"></script>

<style>
.btn-upload-sos { margin-left:6px; }
.modal .form-control { margin-bottom:10px; }
</style>

<?php if ($wrapper->needsCascadeScript()): ?>
<!-- CASCADE DROPDOWN SCRIPT -->
<script type="text/javascript">
$(document).ready(function() {
    function setupDeviceDropdownListener() {
        $('#result_1').on('change', '#device', function(){
            $('#wait_2').show();
            $('#result_2').hide();
            $.get("func3.php", {
                func: "device",
                drop_var2: $(this).val()
            }, function(response){
                $('#result_2').hide().html(response).fadeIn('slow');
                $('#wait_2').hide();
            });
        });
    }

    $('#wait_1').hide();
    $('#section').on('change', function(){
        $('#wait_1').show();
        $('#result_1').empty();
        $.get("func.php", {
            func: "section",
            drop_var: $(this).val()
        }, function(response){
            $('#result_1').hide().html(response).fadeIn('slow');
            $('#wait_1').hide();
            setupDeviceDropdownListener();
        });
    });

    // Modal Secure Document data binding
    $(document).on('click', '.sec-file', function(e) {
        e.preventDefault();
        var drf = $(this).data('id') || '';
        var lama = $(this).data('lama') || '';
        var type = $(this).data('type') || '';
        var rev = $(this).data('rev') || '';
        var status = $(this).data('status') || '';
        var tipe = $(this).data('tipe') || '';
        
        $('#myModal2 #drf').val(drf);
        $('#myModal2 #lama').val(lama);
        $('#myModal2 #type').val(type);
        $('#myModal2 #rev').val(rev);
        $('#myModal2 #status').val(status);
        $('#myModal2 #tipe').val(tipe);
        $('#myModal2').modal('show');
    });

    // Modal Upload Sosialisasi data binding
    $(document).on('click', '.btn-upload-sos', function(e){
        e.preventDefault();
        var drf = $(this).data('drf') || '';
        var nodoc = $(this).data('nodoc') || '';
        
        $('#modal_upload_drf').val(drf);
        $('#modal_upload_nodoc').text(nodoc);
        $('#modalSosialisasi').modal('show');
    });

    // reset form saat modal ditutup
    $('#modalSosialisasi').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    
    $('#myModal2').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});
</script>
<?php else: ?>
<!-- STANDARD SCRIPT -->
<script type="text/javascript">
$(document).ready(function () {
    $(document).on('click', '.sec-file', function(e) {
        e.preventDefault();
        var drf = $(this).data('id') || '';
        var lama = $(this).data('lama') || '';
        var type = $(this).data('type') || '';
        var rev = $(this).data('rev') || '';
        var status = $(this).data('status') || '';
        var tipe = $(this).data('tipe') || '';
        
        $('#myModal2 #drf').val(drf);
        $('#myModal2 #lama').val(lama);
        $('#myModal2 #type').val(type);
        $('#myModal2 #rev').val(rev);
        $('#myModal2 #status').val(status);
        $('#myModal2 #tipe').val(tipe);
        $('#myModal2').modal('show');
    });

    $(document).on('click', '.btn-upload-sos', function(e){
        e.preventDefault();
        var drf = $(this).data('drf') || '';
        var nodoc = $(this).data('nodoc') || '';
        
        $('#modal_upload_drf').val(drf);
        $('#modal_upload_nodoc').text(nodoc);
        $('#modalSosialisasi').modal('show');
    });

    $('#modalSosialisasi').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
    
    $('#myModal2').on('hidden.bs.modal', function () {
        $(this).find('form')[0].reset();
    });
});
</script>
<?php endif; ?>

<h3>Form Production</h3>

<?php
// 🆕 RENDER FILTER FORM MENGGUNAKAN WRAPPER
$wrapper->renderFilterForm();
?>

<?php
if (isset($_GET['submit'])) {
    // 🆕 BUILD QUERY MENGGUNAKAN WRAPPER
    $sql = $wrapper->buildQuery();
    
    $result = mysqli_query($link, $sql);
    
    if (!$result) {
        die("Query Gagal: " . mysqli_error($link));
    }
    
    $rowCount = mysqli_num_rows($result);
    
    // Ambil nilai filter
    $dev = $_GET['device'] ?? '';
    $proc = $_GET['proc'] ?? '';
    $status = $_GET['status'] ?? '';
    $cat = $_GET['cat'] ?? '';
?>

<br /><br />
<h3>Form's List For Device: <strong><?php echo htmlspecialchars($dev);?></strong>
    <?php if (!empty($proc) && $proc != '-'): ?>
        , Process: <strong><?php echo htmlspecialchars($proc); ?></strong>
    <?php endif; ?>
    , Category: <strong><?php echo htmlspecialchars($cat); ?></strong>
</h3>

<?php if ($rowCount == 0): ?>
    <div class='alert alert-warning' style='margin:20px;'>
        <h4><span class='glyphicon glyphicon-search'></span> Tidak ada dokumen yang ditemukan</h4>
        <p>Tidak ada dokumen Form Production dengan filter yang Anda pilih.</p>
    </div>
<?php else: ?>

<div class="table-responsive">
    <table class="table table-hover">
        <thead style="background:#00FFFF;">
            <tr>
                <th>No</th>
                <th>Date</th>
                <th>No. Document</th>
                <th>No Rev.</th>
                <th>DRF</th>
                <th>Title</th>
                <th>Process</th>
                <th>Section</th>
                <th>Action</th>
                <th>Sosialisasi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        while ($info = mysqli_fetch_assoc($result)) {
            $has_sos = !empty($info['sos_file']);
            $tempat = ($info['no_drf'] > 12967) ? $info['doc_type'] : 'document';
        ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo htmlspecialchars($info['tgl_upload']); ?></td>
                <td><?php echo htmlspecialchars($info['no_doc']); ?></td>
                <td><?php echo htmlspecialchars($info['no_rev']); ?></td>
                <td><?php echo htmlspecialchars($info['no_drf']); ?></td>
                <td>
                    <a href="<?php echo htmlspecialchars($tempat . '/' . $info['file']); ?>" target="_blank">
                        <?php echo htmlspecialchars($info['title']); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($info['process']); ?></td>
                <td><?php echo htmlspecialchars($info['section']); ?></td>
                <td style="white-space:nowrap;">
                    <a href="detail.php?drf=<?php echo urlencode($info['no_drf']);?>&no_doc=<?php echo urlencode($info['no_doc']);?>" 
                       class="btn btn-xs btn-info" title="Lihat Detail">
                        <span class="glyphicon glyphicon-search"></span>
                    </a>
                    
                    <a href="lihat_approver.php?drf=<?php echo urlencode($info['no_drf']);?>" 
                       class="btn btn-xs btn-info" title="Lihat Approver">
                        <span class="glyphicon glyphicon-user"></span>
                    </a>
                    
                    <a href="radf.php?drf=<?php echo urlencode($info['no_drf']);?>&section=<?php echo urlencode($info['section'])?>" 
                       class="btn btn-xs btn-info" title="Lihat RADF">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>
                    
                    <?php if ($state=='Admin' || ($state=="Originator" && $info['user_id']==$nrp)): ?>
                        <a href="edit_doc.php?drf=<?php echo urlencode($info['no_drf']);?>" 
                           class="btn btn-xs btn-primary" title="Edit Doc">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </a>
                        
                        <a href="del_doc.php?drf=<?php echo urlencode($info['no_drf']);?>" 
                           class="btn btn-xs btn-danger" 
                           onClick="return confirm('Delete document <?php echo addslashes($info['no_doc'])?>?')" 
                           title="Delete Doc">
                            <span class="glyphicon glyphicon-remove"></span>
                        </a>
                        
                        <a data-toggle="modal" data-target="#myModal2"
                            data-id="<?php echo htmlspecialchars($info['no_drf']); ?>"
                            data-lama="<?php echo htmlspecialchars($info['file']); ?>"
                            data-type="<?php echo htmlspecialchars($info['doc_type']); ?>"
                            data-rev="<?php echo htmlspecialchars($info['no_rev']); ?>"
                            data-status="<?php echo htmlspecialchars($info['status']); ?>"
                            data-tipe="<?php echo htmlspecialchars($info['category']); ?>"
                            class="btn btn-xs btn-success sec-file" 
                            title="Secure Document">
                            <span class="glyphicon glyphicon-play"></span>
                        </a>
                    <?php endif; ?>
                </td>
                
                <td>
                    <?php if ($has_sos) { ?>
                        <a href="lihat_sosialisasi.php?drf=<?php echo urlencode($info['no_drf']); ?>" 
                           class="btn btn-xs btn-primary" title="Lihat Bukti Sosialisasi">
                            <span class="glyphicon glyphicon-file"></span>
                        </a>
                    <?php } else { ?>
                        <button type="button"
                            class="btn btn-xs btn-success btn-upload-sos"
                            data-drf="<?php echo htmlspecialchars($info['no_drf']);?>"
                            data-nodoc="<?php echo htmlspecialchars($info['no_doc']);?>"
                            title="Upload Bukti Sosialisasi">
                            <span class="glyphicon glyphicon-upload"></span>
                        </button>
                    <?php } ?>
                </td>
            </tr>
        <?php 
            $i++;
        }
        ?>
        </tbody>
    </table>
</div>

<?php
    endif;
    mysqli_free_result($result);
} else {
    echo "<div class='alert alert-info' style='margin-top:20px;'>";
    echo "<h4><span class='glyphicon glyphicon-info-sign'></span> Cara Menggunakan</h4>";
    echo "<p>Untuk melihat dokumen <strong>Form Production</strong>:</p>";
    echo "<ol>";
    echo "<li>Pilih <strong>Section (Production)</strong></li>";
    echo "<li>Pilih <strong>Device</strong> (akan muncul otomatis)</li>";
    echo "<li>Pilih <strong>Process</strong> (opsional)</li>";
    echo "<li>Pilih <strong>Status</strong> dan <strong>Category</strong></li>";
    echo "<li>Klik <strong>Show</strong></li>";
    echo "</ol>";
    echo "</div>";
}
?>

<!-- Modal Secure Document -->
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModal2Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModal2Label">Secure Document</h4>
            </div>
            <div class="modal-body">
                <form name="secure_doc" method="POST" action="process.php" enctype="multipart/form-data">
                    <input type="hidden" name="drf" id="drf" class="form-control" />
                    <input type="hidden" name="lama" id="lama" class="form-control" />
                    <input type="hidden" name="rev" id="rev" class="form-control" />
                    <input type="hidden" name="type" id="type" class="form-control" />
                    <input type="hidden" name="status" id="status" class="form-control" />
                    <input type="hidden" name="tipe" id="tipe" class="form-control" />
                    <div class="form-group">
                        <label>Upload New Secured File:</label>
                        <input type="file" name="baru" class="form-control" required>
                    </div>
                    <div class="modal-footer"> 
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="upload" value="Update" class="btn btn-primary" onclick="return confirm('Are you sure you want to secure this document?');">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Sosialisasi -->
<div class="modal fade" id="modalSosialisasi" tabindex="-1" role="dialog" aria-labelledby="modalSosLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="upload_sosialisasi.php" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="modalSosLabel">Upload Bukti Sosialisasi</h4>
                </div>
                <div class="modal-body">
                    <p>Upload bukti sosialisasi untuk No. Document: <strong id="modal_upload_nodoc"></strong></p>
                    <input type="hidden" name="drf" id="modal_upload_drf" value="">
                    <?php
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
                        <input type="file" name="sos_file" class="form-control" accept=".pdf,image/*" required>
                    </div>
                    <div class="form-group">
                        <label>Catatan / Keterangan</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Batal</button>
                    <button type="submit" name="upload_sosialisasi" class="btn btn-success">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="bootstrap/js/bootstrap.min.js"></script>