<?php
include "wi_login.php";

// 🆕 LOAD SMART WRAPPER
require_once('legacy_wrapper.php');
$wrapper = new LegacyFilterWrapper('WI', $link);

// Ambil session state
$state = isset($_SESSION['state']) ? $_SESSION['state'] : '';
$nrp = isset($_SESSION['nrp']) ? $_SESSION['nrp'] : '';
?>

<!-- jQuery -->
<script type="text/javascript" src="bootstrap/js/jquery.min.js"></script>
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

<style>
.btn-upload-sos { margin-left:6px; }
.modal .form-control { margin-bottom:10px; }
</style>

<?php if ($wrapper->needsCascadeScript()): ?>
<!-- CASCADE DROPDOWN SCRIPT -->
<script type="text/javascript">
$(document).ready(function() {
    $('#wait_1').hide();
    $('#wait_2').hide();
    
    // Section Production → Device (CASCADE)
    $('#section').change(function(){
        var section_value = $(this).val();
        
        console.log('Section selected:', section_value);
        
        if(section_value == '') {
            $('#result_1').html('<select name="device" id="device" class="form-control"><option value="">--- Select Device ---</option></select>').show();
            $('#result_2').html('<select name="proc" id="proc" class="form-control"><option value="">--- Select Process ---</option></select>').show();
            return;
        }
        
        $('#wait_1').show();
        $('#result_1').hide();
        $('#result_2').hide();
        
        $.ajax({
            url: 'func.php',
            type: 'GET',
            data: { 
                func: 'section', 
                drop_var: section_value 
            },
            success: function(response){
                console.log('Device response:', response);
                
                $('#wait_1').hide();
                $('#result_1').html(response).fadeIn();
                $('#result_2').html('<select name="proc" id="proc" class="form-control"><option value="">--- Select Process ---</option></select>').show();
                
                // Attach event untuk device
                attachDeviceEvent();
            },
            error: function(xhr, status, error){
                console.error('AJAX Error:', error);
                $('#wait_1').hide();
                $('#result_1').html('<div class="alert alert-danger">Error loading devices</div>').show();
            }
        });
    });
    
    function attachDeviceEvent() {
        $('#device').off('change').on('change', function(){
            var device_value = $(this).val();
            
            console.log('Device selected:', device_value);
            
            if(device_value == '' || device_value == 'General production' || device_value == 'General PC') {
                $('#result_2').html('<select name="proc" id="proc" class="form-control"><option value="">--- Select Process ---</option><option value="General Process">General Process</option></select>').show();
                return;
            }
            
            $('#wait_2').show();
            $('#result_2').hide();
            
            $.ajax({
                url: 'func.php',
                type: 'GET',
                data: { 
                    func: 'device', 
                    drop_var2: device_value 
                },
                success: function(response){
                    console.log('Process response:', response);
                    
                    $('#wait_2').hide();
                    $('#result_2').html(response).fadeIn();
                },
                error: function(xhr, status, error){
                    console.error('AJAX Error:', error);
                    $('#wait_2').hide();
                    $('#result_2').html('<div class="alert alert-danger">Error loading processes</div>').show();
                }
            });
        });
    }
    
    // Modal handlers
    $(document).on('click', '.sec-file', function(e) {
        e.preventDefault();
        var drf = $(this).data('id') || '';
        var lama = $(this).data('lama') || '';
        var status = $(this).data('status') || '';
        
        $('#myModal2 #drf').val(drf);
        $('#myModal2 #lama').val(lama);
        $('#myModal2 #status').val(status);
        $('#myModal2').modal('show');
    });

    $(document).on('click', '.btn-upload-sos', function(e){
        e.preventDefault();
        var drf = $(this).data('drf');
        var nodoc = $(this).data('nodoc');
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
<?php else: ?>
<!-- STANDARD SCRIPT -->
<script type="text/javascript">
$(document).ready(function () {
    $(document).on('click', '.sec-file', function(e) {
        e.preventDefault();
        var drf = $(this).data('id') || '';
        var lama = $(this).data('lama') || '';
        var status = $(this).data('status') || '';
        
        $('#myModal2 #drf').val(drf);
        $('#myModal2 #lama').val(lama);
        $('#myModal2 #status').val(status);
        $('#myModal2').modal('show');
    });

    $(document).on('click', '.btn-upload-sos', function(e){
        e.preventDefault();
        var drf = $(this).data('drf');
        var nodoc = $(this).data('nodoc');
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

<br />

<h3>Work Instruction Production</h3>

<?php
// 🆕 RENDER FILTER FORM MENGGUNAKAN WRAPPER
$wrapper->renderFilterForm();
?>

<?php
// Build query jika form disubmit
if (isset($_GET['submit'])) {
    // 🆕 BUILD QUERY MENGGUNAKAN WRAPPER
    $sql = $wrapper->buildQuery();
    
    $result = mysqli_query($link, $sql);
    
    if (!$result) {
        echo "<div class='alert alert-danger'>Query error: ". htmlspecialchars(mysqli_error($link)) ."</div>";
        echo "<div class='alert alert-info'><strong>Debug Query:</strong><br><code>" . htmlspecialchars($sql) . "</code></div>";
        exit;
    }
    
    $rowCount = mysqli_num_rows($result);
    
    // Ambil nilai filter untuk display
    $section_prod = isset($_GET['section']) ? $_GET['section'] : '';
    $device = isset($_GET['device']) ? $_GET['device'] : '';
    $process = isset($_GET['proc']) ? $_GET['proc'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '0';
    $category = isset($_GET['cat']) ? $_GET['cat'] : '0';
?>

<br /><br />
<h1>Work Instruction's List For 
    <?php if (!empty($section_prod)): ?>
        Section: <strong><?php echo htmlspecialchars($section_prod);?></strong>
    <?php endif; ?>
    <?php if (!empty($device)): ?>
        , Device: <strong><?php echo htmlspecialchars($device);?></strong>
    <?php endif; ?>
    <?php if (!empty($process) && $process != '-'): ?>
        , Process: <strong><?php echo htmlspecialchars($process); ?></strong>
    <?php endif; ?>
    <?php if ($category != '0'): ?>
        , Category: <strong><?php echo htmlspecialchars($category); ?></strong>
    <?php endif; ?>
</h1>

<?php if ($rowCount == 0): ?>
    <div class='alert alert-warning' style='margin:20px;'>
        <h4><span class='glyphicon glyphicon-search'></span> Tidak ada dokumen yang ditemukan</h4>
        <p>Tidak ada dokumen Work Instruction dengan filter yang Anda pilih.</p>
        <hr>
        <p class='text-left'><strong>Filter yang aktif:</strong></p>
        <ul class='text-left'>
            <?php if (!empty($section_prod)): ?>
            <li><strong>Section:</strong> <?php echo htmlspecialchars($section_prod); ?></li>
            <?php endif; ?>
            <?php if (!empty($device)): ?>
            <li><strong>Device:</strong> <?php echo htmlspecialchars($device); ?></li>
            <?php endif; ?>
            <?php if (!empty($process) && $process != '-'): ?>
            <li><strong>Process:</strong> <?php echo htmlspecialchars($process); ?></li>
            <?php endif; ?>
            <?php if ($status != '0'): ?>
            <li><strong>Status:</strong> <?php echo htmlspecialchars($status); ?></li>
            <?php endif; ?>
            <?php if ($category != '0'): ?>
            <li><strong>Category:</strong> <?php echo htmlspecialchars($category); ?></li>
            <?php endif; ?>
        </ul>
        <p class='text-muted'><small>Coba gunakan filter yang berbeda atau pilih filter yang lebih umum.</small></p>
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
            
            if ($info['no_drf'] > 12800) {
                $tempat = $info['doc_type'];
            } else {
                $tempat = 'document';
            }
        ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td><?php echo htmlspecialchars($info['tgl_upload']);?></td>
                <td><?php echo htmlspecialchars($info['no_doc']);?></td>
                <td><?php echo htmlspecialchars($info['no_rev']);?></td>
                <td><?php echo htmlspecialchars($info['no_drf']);?></td>
                <td>
                    <a href="<?php echo htmlspecialchars($tempat . '/' . $info['file']); ?>" target="_blank">
                        <?php echo htmlspecialchars($info['title']);?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($info['process']);?></td>
                <td><?php echo htmlspecialchars($info['section']);?></td>

                <!-- Action -->
                <td style="white-space:nowrap;">
                    <a href="detail.php?drf=<?php echo urlencode($info['no_drf']);?>&no_doc=<?php echo urlencode($info['no_doc']);?>" 
                       class="btn btn-xs btn-info" title="lihat detail">
                        <span class="glyphicon glyphicon-search"></span>
                    </a>
                    
                    <a href="lihat_approver.php?drf=<?php echo urlencode($info['no_drf']);?>" 
                       class="btn btn-xs btn-info" title="lihat approver">
                        <span class="glyphicon glyphicon-user"></span>
                    </a>
                    
                    <a href="radf.php?drf=<?php echo urlencode($info['no_drf']);?>&section=<?php echo urlencode($info['section']);?>" 
                       class="btn btn-xs btn-info" title="lihat RADF">
                        <span class="glyphicon glyphicon-eye-open"></span>
                    </a>

                    <?php if ($state == 'Admin' || $state == 'Originator') { ?>
                        <a href="edit_doc.php?drf=<?php echo urlencode($info['no_drf']);?>" 
                           class="btn btn-xs btn-primary" title="Edit Doc">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </a>
                        
                        <a href="del_doc.php?drf=<?php echo urlencode($info['no_drf']);?>" 
                           class="btn btn-xs btn-danger" 
                           onClick="return confirm('Delete document <?php echo addslashes($info['no_doc']); ?>?')" 
                           title="Delete Doc">
                            <span class="glyphicon glyphicon-remove"></span>
                        </a>

                        <a data-toggle="modal" data-target="#myModal2"
                            data-id="<?php echo htmlspecialchars($info['no_drf']); ?>"
                            data-lama="<?php echo htmlspecialchars($info['file']); ?>"
                            data-status="<?php echo htmlspecialchars($info['status']); ?>"
                            class="btn btn-xs btn-success sec-file" 
                            title="Secure Document">
                            <span class="glyphicon glyphicon-play"></span>
                        </a>
                    <?php } ?>
                </td>

                <!-- Kolom Sosialisasi -->
                <td>
                    <?php if ($has_sos) { ?>
                        <a href="lihat_sosialisasi.php?drf=<?php echo urlencode($info['no_drf']); ?>" 
                           class="btn btn-xs btn-primary" title="Lihat Bukti Sosialisasi">
                            Lihat
                            <span class="glyphicon glyphicon-file"></span>
                        </a>
                    <?php } else { ?>
                        <a href="#" 
                           class="btn btn-xs btn-success btn-upload-sos"
                           data-drf="<?php echo htmlspecialchars($info['no_drf']); ?>"
                           data-nodoc="<?php echo htmlspecialchars($info['no_doc']); ?>"
                           title="Upload Bukti Sosialisasi">
                            Upload
                            <span class="glyphicon glyphicon-upload"></span>
                        </a>
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
    endif; // end if rowCount > 0
    mysqli_free_result($result);
} else {
    // Tampilkan instruksi jika belum submit
    echo "<div class='alert alert-info' style='margin-top:20px;'>";
    echo "<h4><span class='glyphicon glyphicon-info-sign'></span> Cara Menggunakan</h4>";
    echo "<p>Untuk melihat dokumen <strong>Work Instruction Production</strong>:</p>";
    echo "<ol>";
    echo "<li>Pilih <strong>Section (Production)</strong> terlebih dahulu</li>";
    echo "<li>Pilih <strong>Device</strong> (akan muncul otomatis setelah pilih section)</li>";
    echo "<li>Pilih <strong>Process</strong> (opsional, akan muncul setelah pilih device)</li>";
    echo "<li>Pilih filter tambahan lainnya jika diperlukan (Status, Category)</li>";
    echo "<li>Klik tombol <strong>Show</strong></li>";
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
                    <input type="hidden" name="drf" id="drf" class="form-control" value=""/>
                    <input type="hidden" name="lama" id="lama" class="form-control" value=""/>
                    <input type="hidden" name="status" id="status" class="form-control" value=""/>
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