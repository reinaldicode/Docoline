<?php 
include "wi_awal.php"; 

// HINDARI PENGGUNAAN extract($_REQUEST); INI SANGAT BERBAHAYA.
// extract($_REQUEST); // Baris ini telah dihapus.

// Tampilkan semua error kecuali NOTICE untuk membantu debugging.
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
?>
<br />

<script type="text/javascript" src="bootstrap/js/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('#wait_1').hide();
    $('#section').change(function(){
        $('#wait_1').show();
        $('#result_1').hide();
        $.get("func.php", {
            func: "section",
            drop_var: $('#section').val()
        }, function(response){
            $('#result_1').fadeOut();
            // Menggunakan fungsi anonim lebih modern dan aman daripada string
            setTimeout(function() { 
                finishAjax1('result_1', response); 
            }, 400);
        });
        return false;
    });
});

function finishAjax1(id, response) {
    $('#wait_1').hide();
    $('#'+id).html(response); // unescape() sudah tidak direkomendasikan
    $('#'+id).fadeIn();

    $('#wait_2').hide();
    // Event handler untuk 'device' harus didefinisikan di sini
    // agar berfungsi pada elemen yang baru dibuat oleh AJAX.
    $('#device').change(function(){
        $('#wait_2').show();
        $('#result_2').hide();
        $.get("func.php", { // Pastikan file func.php bisa menangani 'device'
            func: "device",
            drop_var2: $('#device').val()
        }, function(response){
            $('#result_2').fadeOut();
            setTimeout(function() {
                finishAjax2('result_2', response);
            }, 400);
        });
        return false;
    });
}

// Nama fungsi diubah agar tidak duplikat
function finishAjax2(id, response) {
    $('#wait_2').hide();
    $('#'+id).html(response); // unescape() sudah tidak direkomendasikan
    $('#'+id).fadeIn();
}
</script>

<div class="row">
    <div class="col-xs-4 well well-lg">
        <h2>Select Device & Process</h2>
        <form action="" method="GET">
            <table>
                <tr>
                    <td>Section</td>
                    <td>:</td>
                    <td>
                        <?php include('func.php'); ?>
                        <select name="section" id="section" class="form-control">
                            <option value="" selected="selected">Select Section</option>
                            <?php getTierOne(); ?>
                        </select> 
                    </td>
                </tr>
                <tr>
                    <td>Device</td>
                    <td>:</td>
                    <td>
                        <span id="wait_1" style="display: none;">
                            <img alt="Please Wait" src="images/wait.gif"/>
                        </span>
                        <span id="result_1" style="display: none;"></span> 
                    </td>
                </tr>
                <tr>
                    <td>Process</td>
                    <td>:</td>
                    <td>
                        <span id="wait_2" style="display: none;">
                            <img alt="Please Wait" src="images/wait.gif"/>
                        </span>
                        <span id="result_2" style="display: none;"></span>
                    </td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>:</td>
                    <td>
                        <select name="status" class="form-control">
                            <option value=""> --- Select Status --- </option>
                            <option value="Secured" selected> Approved </option>
                            <option value="Review"> Review </option>
                            <option value="Pending"> Pending </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Category</td>
                    <td>:</td>
                    <td>
                        <select name="cat" class="form-control">
                            <option value=""> --- Select Category --- </option>
                            <option value="Internal" selected> Internal </option>
                            <option value="External"> External </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>
                        <input type='hidden' name='by' value='no_drf'>
                        <input type="submit" value="Show" name="submit" class="btn btn-info">
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<?php
if (isset($_GET['submit'])) {

    // **PERBAIKAN UTAMA & KEAMANAN:**
    // 1. Ambil nilai dari $_GET dengan aman menggunakan isset().
    // 2. Bersihkan input menggunakan mysqli_real_escape_string() untuk mencegah SQL Injection.
    $dev = isset($_GET['device']) ? mysqli_real_escape_string($link, $_GET['device']) : '';
    $proc = isset($_GET['proc']) ? mysqli_real_escape_string($link, $_GET['proc']) : '';
    $status = isset($_GET['status']) ? mysqli_real_escape_string($link, $_GET['status']) : '';
    $cat = isset($_GET['cat']) ? mysqli_real_escape_string($link, $_GET['cat']) : '';

    // 3. Gunakan 'whitelist' untuk kolom ORDER BY, ini cara paling aman.
    $orderByOptions = ['no_drf', 'no_doc', 'title'];
    $by = isset($_GET['by']) && in_array($_GET['by'], $orderByOptions) ? $_GET['by'] : 'no_drf';

    // Membangun query SQL
    if ($proc == '' || $proc == '-') {
        // PERHATIAN: klausa order by tidak boleh memakai kutip biasa ', tapi backtick ` atau tanpa kutip.
        $sql = "SELECT * FROM docu WHERE device='$dev' AND doc_type='WI' AND status='$status' AND section='Production' AND category='$cat' ORDER BY `$by`";
    } else {
        $sql = "SELECT * FROM docu WHERE device='$dev' AND process='$proc' AND doc_type='WI' AND status='$status' AND section='Production' AND category='$cat' ORDER BY `$by`";
    }

    $res = mysqli_query($link, $sql);
    if (!$res) {
        die("Query Gagal: " . mysqli_error($link));
    }
?>
<br /><br /><br />
<table class="table table-hover">
    <h1>Work Instruction's List For Device: <strong><?php echo htmlspecialchars($dev);?></strong>, Process: <strong><?php echo htmlspecialchars($proc); ?></strong>, Category: <strong><?php echo htmlspecialchars($cat); ?></strong></h1>
    <thead bgcolor="#00FFFF">
        <tr>
            <td>No</td>
            <td>Date</td>
            <td><a href='wi_prod_awal.php?device=<?php echo urlencode($dev); ?>&by=no_doc&status=<?php echo urlencode($status); ?>&proc=<?php echo urlencode($proc);?>&cat=<?php echo urlencode($cat); ?>&submit=Show'>No. Document</a></td>
            <td>No Rev.</td>
            <td><a href='wi_prod_awal.php?device=<?php echo urlencode($dev); ?>&by=no_drf&status=<?php echo urlencode($status); ?>&proc=<?php echo urlencode($proc);?>&cat=<?php echo urlencode($cat); ?>&submit=Show'>DRF</a></td>
            <td><a href='wi_prod_awal.php?device=<?php echo urlencode($dev); ?>&by=title&status=<?php echo urlencode($status); ?>&proc=<?php echo urlencode($proc);?>&cat=<?php echo urlencode($cat); ?>&submit=Show'>Title</a></td>
            <td>Process</td>
            <td>Section</td>
            <td>Action</td>
            <td>Sosialisasi</td>
        </tr>
    </thead>
    <tbody>
    <?php
    $i = 1;
    while ($info = mysqli_fetch_assoc($res)) { // mysqli_fetch_assoc lebih baik
    ?>
        <tr>
            <td><?php echo $i; ?></td>
            <td><?php echo htmlspecialchars($info['tgl_upload']); ?></td>
            <td><?php echo htmlspecialchars($info['no_doc']); ?></td>
            <td><?php echo htmlspecialchars($info['no_rev']); ?></td>
            <td><?php echo htmlspecialchars($info['no_drf']); ?></td>
            <td>
                <?php $tempat = ($info['no_drf'] > 12967) ? $info['doc_type'] : 'document'; ?>
                <a href="<?php echo htmlspecialchars($tempat); ?>/<?php echo htmlspecialchars($info['file']); ?>">
                    <?php echo htmlspecialchars($info['title']); ?>
                </a>
            </td>
            <td><?php echo htmlspecialchars($info['process']); ?></td>
            <td><?php echo htmlspecialchars($info['section']); ?></td>
            <td>
                <a href="detail.php?drf=<?php echo urlencode($info['no_drf']);?>&no_doc=<?php echo urlencode($info['no_doc']);?>&log=1" class="btn btn-xs btn-info" title="lihat detail"><span class="glyphicon glyphicon-search"></span></a>
                <a href="radf.php?drf=<?php echo urlencode($info['no_drf']);?>&section=<?php echo urlencode($info['section'])?>&log=1" class="btn btn-xs btn-info" title="lihat RADF"><span class="glyphicon glyphicon-eye-open"></span></a>
            </td>
            <td>
                <?php 
                $has_sos = !empty($info['sos_file']);
                $sos_class = $has_sos ? 'btn-primary' : 'btn-default';
                $sos_title = $has_sos ? 'Lihat Detail Sosialisasi' : 'Belum ada bukti sosialisasi';
                ?>
                <a href="lihat_sosialisasi.php?drf=<?php echo urlencode($info['no_drf']); ?>" class="btn btn-xs <?php echo $sos_class; ?>" title="<?php echo $sos_title; ?>">
                    <span class="glyphicon glyphicon-file"></span>
                </a>
            </td>
        </tr>
    <?php 
        $i++;
    } // Akhir while
    ?>
    </tbody>
</table>
<?php
} // Akhir if isset submit
?>