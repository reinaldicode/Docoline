<?php
include 'header.php';
include 'koneksi.php';

// 🆕 LOAD SMART WRAPPER
require_once('legacy_wrapper.php');
$wrapper = new LegacyFilterWrapper('MS & ROHS', $link);

// Ambil session state
$state = $_SESSION['state'] ?? '';
$nrp   = $_SESSION['nrp'] ?? '';
?>

<br />

<!-- jQuery -->
<script type="text/javascript" src="bootstrap/js/jquery.min.js"></script>

<style>
.btn-upload-sos { margin-left:6px; }
.modal .form-control { margin-bottom:10px; }
</style>

<?php if ($wrapper->needsCascadeScript()): ?>
<!-- CASCADE SCRIPT -->
<script type="text/javascript">
$(document).ready(function() {
    $('#wait_1').hide();
    $('#wait_2').hide();
    
    $('#section').change(function(){
        var section_value = $(this).val();
        
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
            data: { func: 'section', drop_var: section_value },
            success: function(response){
                $('#wait_1').hide();
                $('#result_1').html(response).fadeIn();
                $('#result_2').html('<select name="proc" id="proc" class="form-control"><option value="">--- Select Process ---</option></select>').show();
                attachDeviceEvent();
            }
        });
    });
    
    function attachDeviceEvent() {
        $('#device').off('change').on('change', function(){
            var device_value = $(this).val();
            
            if(device_value == '' || device_value == 'General production' || device_value == 'General PC') {
                $('#result_2').html('<select name="proc" id="proc" class="form-control"><option value="">--- Select Process ---</option><option value="General Process">General Process</option></select>').show();
                return;
            }
            
            $('#wait_2').show();
            $('#result_2').hide();
            
            $.ajax({
                url: 'func.php',
                type: 'GET',
                data: { func: 'device', drop_var2: device_value },
                success: function(response){
                    $('#wait_2').hide();
                    $('#result_2').html(response).fadeIn();
                }
            });
        });
    }
    
    $(document).on('click', '.sec-file', function(e) {
        e.preventDefault();
        var drf     = $(this).data('id') || '';
        var lama    = $(this).data('lama') || '';
        var type    = $(this).data('type') || '';
        var rev     = $(this).data('rev') || '';
        var section = $(this).data('section') || '';
        var status  = $(this).data('status') || '';
        var tipe    = $(this).data('tipe') || '';

        $('#myModal2 #drf').val(drf);
        $('#myModal2 #lama').val(lama);
        $('#myModal2 #type').val(type);
        $('#myModal2 #rev').val(rev);
        $('#myModal2 #section').val(section);
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
<?php else: ?>
<!-- STANDARD SCRIPT -->
<script type="text/javascript">
$(document).ready(function () {
    $(document).on('click', '.sec-file', function(e) {
        e.preventDefault();
        var drf     = $(this).data('id') || '';
        var lama    = $(this).data('lama') || '';
        var type    = $(this).data('type') || '';
        var rev     = $(this).data('rev') || '';
        var section = $(this).data('section') || '';
        var status  = $(this).data('status') || '';
        var tipe    = $(this).data('tipe') || '';

        $('#myModal2 #drf').val(drf);
        $('#myModal2 #lama').val(lama);
        $('#myModal2 #type').val(type);
        $('#myModal2 #rev').val(rev);
        $('#myModal2 #section').val(section);
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

<h3>MS & ROHS Documents</h3>

<?php
// 🆕 RENDER FILTER FORM MENGGUNAKAN WRAPPER
$wrapper->renderFilterForm();
?>

<?php
if (isset($_GET['submit'])){
    // 🆕 BUILD QUERY MENGGUNAKAN WRAPPER
    $sql = $wrapper->buildQuery();
    
    $result = mysqli_query($link, $sql);
    
    if (!$result) {
        echo "<div class='alert alert-danger'>Query error: ".htmlspecialchars(mysqli_error($link))."</div>";
        exit;
    }
    
    $rowCount = mysqli_num_rows($result);
    
    $section = $_GET['section'] ?? '';
    $type = $_GET['type'] ?? '';
    $status = $_GET['status'] ?? '';
    $category = $_GET['category'] ?? '';
?>

<br /><br />
<h1>Documents List For Section: <strong><?php echo htmlspecialchars($section);?></strong>
    <?php if (!empty($type)): ?>
        , Type: <strong><?php echo htmlspecialchars($type); ?></strong>
    <?php endif; ?>
</h1>

<?php if ($rowCount == 0): ?>
    <div class='alert alert-warning' style='margin:20px;'>
        <h4><span class='glyphicon glyphicon-search'></span> Tidak ada dokumen yang ditemukan</h4>
        <p>Tidak ada dokumen MS & ROHS dengan filter yang Anda pilih.</p>
    </div>
<?php else: ?>

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
$i=1;
while($info = mysqli_fetch_array($result)) 
{ 
    $has_sos = !empty($info['sos_file']);
    $tempat = ($info['no_drf'] > 12967) ? $info['doc_type'] : 'document';
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
    <td style="white-space:nowrap;">
        <a href="detail.php?drf=<?php echo urlencode($info['no_drf']);?>&no_doc=<?php echo urlencode($info['no_doc']); ?>" class="btn btn-xs btn-info" title="lihat detail"><span class="glyphicon glyphicon-search" ></span> </a>
        <a href="lihat_approver.php?drf=<?php echo urlencode($info['no_drf']);?>" class="btn btn-xs btn-info" title="lihat approver"><span class="glyphicon glyphicon-user" ></span> </a>	
        <a href="radf.php?drf=<?php echo urlencode($info['no_drf']);?>&section=<?php echo urlencode($info['section']) ?>" class="btn btn-xs btn-info" title="lihat RADF"><span class="glyphicon glyphicon-eye-open" ></span> </a>	
        <?php if ($state=='Admin' or ($state=="Originator" and $info['user_id']==$nrp)){ ?>
            <a href="edit_doc.php?drf=<?php echo urlencode($info['no_drf']);?>" class="btn btn-xs btn-primary" title="Edit Doc"><span class="glyphicon glyphicon-pencil" ></span> </a>
            <a href="del_doc.php?drf=<?php echo urlencode($info['no_drf']);?>" class="btn btn-xs btn-danger" onClick="return confirm('Delete document <?php echo addslashes($info['no_doc'])?>?')" title="Delete Doc"><span class="glyphicon glyphicon-remove" ></span> </a>
            <?php if ($info['status']=='Secured') {?>
                <a data-toggle="modal" data-target="#myModal2" 
                   data-id="<?php echo htmlspecialchars($info['no_drf']); ?>" 
                   data-lama="<?php echo htmlspecialchars($info['file']); ?>" 
                   data-type="<?php echo htmlspecialchars($info['doc_type']); ?>"
                   data-rev="<?php echo htmlspecialchars($info['no_rev']); ?>"
                   data-section="<?php echo htmlspecialchars($info['section']); ?>"
                   data-status="<?php echo htmlspecialchars($info['status']); ?>" 
                   data-tipe="<?php echo htmlspecialchars($info['category']); ?>" 
                   class="btn btn-xs btn-success sec-file" title="Secure Document">
                    <span class="glyphicon glyphicon-play" ></span>
                </a>
            <?php } ?>
        <?php } ?>
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

<?php
    endif;
    mysqli_free_result($result);
} else {
    echo "<div class='alert alert-info' style='margin-top:20px;'>";
    echo "<h4><span class='glyphicon glyphicon-info-sign'></span> Cara Menggunakan</h4>";
    echo "<p>Pilih <strong>Section</strong>, <strong>Type</strong>, <strong>Status</strong>, dan <strong>Category</strong>, lalu klik <strong>Show</strong>.</p>";
    echo "</div>";
}
?>

<!-- Modal Secure Document -->
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Update Document</h4>
            </div>
            <div class="modal-body">
                <form name="secure_doc" method="POST" action="process.php" enctype="multipart/form-data">
                    <input type="hidden" name="drf" id="drf" class="form-control" value=""/>
                    <input type="hidden" name="lama" id="lama" class="form-control" value=""/>
                    <input type="hidden" name="rev" id="rev" class="form-control" value=""/>
                    <input type="hidden" name="type" id="type" class="form-control" value=""/>
                    <input type="hidden" name="section" id="section" class="form-control" value=""/>
                    <input type="hidden" name="status" id="status" class="form-control" value=""/>
                    <input type="hidden" name="tipe" id="tipe" class="form-control" value=""/>
                    <div class="form-group">
                        <label>Upload New Secured File:</label>
                        <input type="file" name="baru" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <input type="submit" name="upload" value="Update" class="btn btn-primary">
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