<?php
include('header.php');
include 'koneksi.php';

// 🆕 LOAD SMART WRAPPER
require_once('legacy_wrapper.php');
$wrapper = new LegacyFilterWrapper('Manual', $link);

// Ambil session state
$state = isset($_SESSION['state']) ? $_SESSION['state'] : '';
$nrp = isset($_SESSION['nrp']) ? $_SESSION['nrp'] : '';
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

<h3>Company Manual List</h3>

<?php
// 🆕 RENDER FILTER FORM (1 baris!)
// Catatan: Manual punya filter khusus 'lang', bukan menggunakan filter standard
// Jadi kita render manual form untuk backward compatibility
?>

<div class="row">
    <div class="col-xs-4 well well-lg">
        <h2>Filter Document - Manual</h2>
        
        <form action="" method="GET">
            <table>
                <tr>
                    <td>Language</td>
                    <td>:</td>
                    <td>
                        <select name="lang" class="form-control">
                            <option value="0"> --- Select Language --- </option>
                            <option value="Indonesian" <?php if(isset($_GET['lang']) && $_GET['lang']=='Indonesian') echo 'selected'; ?>> Indonesian </option>
                            <option value="English" <?php if(isset($_GET['lang']) && $_GET['lang']=='English') echo 'selected'; ?>> English </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>
                        <input type="hidden" name="by" value="no_doc">
                        <input type="submit" value="Show" name="submit" class="btn btn-info">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<?php
// Build query jika form disubmit
if (isset($_GET['submit'])) {
    $lang = isset($_GET['lang']) ? mysqli_real_escape_string($link, $_GET['lang']) : '0';
    
    if ($lang == '0') {
        echo "<div class='alert alert-warning'>Please select a language first.</div>";
    } else {
        // Query khusus untuk Manual (berbeda dari doc types lain)
        $sql = "SELECT * FROM docu WHERE section='Manual' AND title='$lang' ORDER BY no_doc";
        
        $result = mysqli_query($link, $sql);
        
        if (!$result) {
            echo "<div class='alert alert-danger'>Query error: ". htmlspecialchars(mysqli_error($link)) ."</div>";
            exit;
        }
        
        $rowCount = mysqli_num_rows($result);
?>

<br /><br />
<h3>Manual List: <strong>Language: <?php echo htmlspecialchars($lang); ?></strong></h3>

<?php if ($rowCount == 0): ?>
    <div class='alert alert-warning' style='margin:20px;'>
        <h4><span class='glyphicon glyphicon-search'></span> No documents found</h4>
        <p>No Manual documents found for language: <strong><?php echo htmlspecialchars($lang); ?></strong></p>
    </div>
<?php else: ?>

<div class="table-responsive">
    <table class="table table-hover">
        <thead style="background:#00FFFF;">
            <tr>
                <th>No</th>
                <th>Title</th>
                <th>Language</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        while ($info = mysqli_fetch_assoc($result)) {
            // Tentukan folder tempat file
            if ($info['no_drf'] > 12955) { 
                $tempat = $info['doc_type']; 
            } else { 
                $tempat = 'document'; 
            }
        ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td>
                    <a href="<?php echo htmlspecialchars($tempat . '/' . $info['file']); ?>" target="_blank">
                        <?php echo htmlspecialchars($info['no_doc']);?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($info['title']);?></td>
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
    } // end else lang selected
} else {
    // Tampilkan instruksi jika belum submit
    echo "<div class='alert alert-info' style='margin-top:20px;'>";
    echo "<h4><span class='glyphicon glyphicon-info-sign'></span> How to Use</h4>";
    echo "<p>Select a <strong>Language</strong> and click <strong>Show</strong> to display company manuals.</p>";
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