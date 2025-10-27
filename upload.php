<?php

include('header.php');

/**
 * ============================================================================
 * SECTION NORMALIZATION FUNCTION
 * ============================================================================
 * Normalize section name untuk konsistensi data
 * Removes " Section" suffix from section names
 */
function normalizeSection($section) {
    if (empty($section) || $section === '-') {
        return $section;
    }
    
    $normalized = trim($section);
    
    // Remove " Section" suffix (case insensitive)
    $normalized = preg_replace('/\s+section$/i', '', $normalized);
    
    // Remove "Section " prefix (case insensitive)  
    $normalized = preg_replace('/^section\s+/i', '', $normalized);
    
    // Clean up extra spaces
    $normalized = preg_replace('/\s+/', ' ', $normalized);
    
    return trim($normalized);
}

?>

<script type="text/javascript">

document.ready = function() {
    document.getElementById('section').onchange = disablefield;
}

function disablefield()
{
    if ( document.getElementById('section').value != 'Production Section' )
    {
        document.getElementById('device').readonly = true;
        document.getElementById('process').readonly = true;
    }
    else{
        document.getElementById('device').readonly = false;
        document.getElementById('process').readonly = false;
    }
}

</script>

<script type="text/javascript">
            $(document).ready(function() {
                $('#wait_1').hide();
                $('#device').change(function(){
                  $('#wait_1').show();
                  $('#result_1').hide();
                  $.get("func2.php", {
                    func: "device",
                    drop_var: $('#device').val()
                  }, function(response){
                    $('#result_1').fadeOut();
                    setTimeout("finishAjax1('result_1', '"+escape(response)+"')", 400);
                  });
                    return false;
                });

            });

            function finishAjax1(id, response) {
              $('#wait_1').hide();
              $('#'+id).html(unescape(response));
              $('#'+id).fadeIn();

              $('#wait_2').hide();
                $('#device').change(function(){
                    
                  $('#wait_2').show();
                  $('#result_2').hide();
                  $.get("func.php", {
                    func: "device",
                    drop_var2: $('#device').val()
                  }, function(response){
                    $('#result_2').fadeOut();
                    setTimeout("finishAjax('result_2', '"+escape(response)+"')", 400);
                  });
                    return false;
                });

                function finishAjax(id, response) {
              $('#wait_2').hide();
              $('#'+id).html(unescape(response));
              $('#'+id).fadeIn();

            }

            }

            function finishAjax(id, response) {
              $('#wait_2').hide();
              $('#'+id).html(unescape(response));
              $('#'+id).fadeIn();

            }
            </script>

<script>
    // Function to prevent spaces in No. Document field
    function preventSpaces(event) {
        if (event.which === 32 || event.keyCode === 32) {
            event.preventDefault();
            alert('Spasi tidak diperbolehkan pada field No. Document');
            return false;
        }
    }

    // Function to remove spaces if pasted
    function removeSpaces(element) {
        element.value = element.value.replace(/\s/g, '');
    }

    // Function to allow only numbers in No. Revision field
    function allowOnlyNumbers(event) {
        // Allow: backspace, delete, tab, escape, enter
        if ([46, 8, 9, 27, 13].indexOf(event.keyCode) !== -1 ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (event.keyCode === 65 && event.ctrlKey === true) ||
            (event.keyCode === 67 && event.ctrlKey === true) ||
            (event.keyCode === 86 && event.ctrlKey === true) ||
            (event.keyCode === 88 && event.ctrlKey === true) ||
            // Allow: home, end, left, right, down, up
            (event.keyCode >= 35 && event.keyCode <= 40)) {
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
            event.preventDefault();
            alert('Hanya angka yang diperbolehkan pada field No. Revision');
        }
    }

    // Function to remove non-numeric characters if pasted
    function removeNonNumeric(element) {
        element.value = element.value.replace(/[^0-9]/g, '');
    }

    function validasi(){
        // var namaValid    = /^[a-zA-Z]+(([\'\,\.\- ][a-zA-Z ])?[a-zA-Z])$/;
        // var emailValid   = /^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/;
        var nodoc         = formulir.nodoc.value;
        var norev         = formulir.norev.value;
            var revto        = formulir.revto.value;
            var type         = formulir.type1.value;
            var cat     = formulir.cat.value;
            var title       = formulir.title1.value;
             var desc       = formulir.desc.value;
           
        // File validation
        var file = formulir.file.files[0];
        var master = formulir.master.files[0];

        var pesan = '';
        
        

        if (nodoc== ''){
            pesan += '-Data Nomor Dokumen belum diisi\n';   
        }
        
        // Check for spaces in No. Document
        if (nodoc.indexOf(' ') !== -1) {
            pesan += '-Nomor Dokumen tidak boleh mengandung spasi\n';
        }

         if (norev== ''){
            pesan += '-Data Nomor revisi belum diisi\n';    
        }

        // Check if No. Revision contains only numbers
        if (norev !== '' && !/^\d+$/.test(norev)) {
            pesan += '-Nomor Revisi hanya boleh berisi angka\n';
        }

         if (revto== '-'){
            pesan += '-Data Revision to belum diisi\n'; 
        }
         if (type== '-'){
            pesan += '-Data type Dokumen belum diisi\n';    
        }
         if (cat== '-'){
            pesan += '-Data Kategori Dokumen belum diisi\n';    
        }
         if (title== ''){
            pesan += '-Data Judul Dokumen belum diisi\n';   
        }
         if (desc== '-'){
            pesan += '-Data Description Dokumen belum diisi\n'; 
        }

        // File validation - both files are required
        if (!file) {
            pesan += '-File Document belum dipilih\n';
        }
        
        if (!master) {
            pesan += '-File Master belum dipilih\n';
        }

        if (pesan != '') {
            alert('Data yang diisikan belum lengkap : \n'+pesan);
            return false;
        }
    return true
    }
</script>

</head>

<?php

//include 'index.php';
include 'koneksi.php';

// ---------- NEW: load document types from JSON and check prefill ----------
$jsonFile = __DIR__ . '/data/document_types.json';
$docTypes = [];
if (file_exists($jsonFile)) {
    $tmp = json_decode(file_get_contents($jsonFile), true);
    if (is_array($tmp)) $docTypes = $tmp;
}
// prefill from GET if provided (documents.php?type=...)
$prefill_type = '';
if (isset($_GET['type']) && trim($_GET['type']) !== '') {
    $prefill_type = trim($_GET['type']);
}

?>

<br />
<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-6 well well-lg">
 <h2>Add New Document</h2>

                <form action="" method="POST" name="formulir" enctype="multipart/form-data">
                 <table class="table">
                    <tr cellpadding="50px">
                        <td>User ID &nbsp;&nbsp;</td>
                        <td>:&nbsp; &nbsp; &nbsp;</td>
                        <td><input type="text" class="form-control" name="user" readonly="readonly" value="<?php echo htmlspecialchars($nrp);?>"></td>
                    </tr>
                    <tr cellpadding="50px">
                        <td>Email &nbsp;&nbsp;</td>
                        <td>:&nbsp; &nbsp; &nbsp;</td>
                        <td><input type="text" class="form-control" name="email" readonly="readonly" value="<?php echo htmlspecialchars($email);?>" ></td>
                    </tr>
                    <tr>
                    <?php
                    // y NORMALIZE section dari user session sebelum query
                    $sec_normalized = normalizeSection($sec);
                    
                    // Query berdasarkan id_section (yang sudah normalized)
                    $sql1 = "SELECT * FROM section WHERE id_section='" . mysqli_real_escape_string($link, $sec_normalized) . "' LIMIT 1";
                    
                    $ses_sql = mysqli_query($link, $sql1);
                    
                    //  SAFE CHECK: Pastikan query berhasil dan ada data
                    if ($ses_sql && mysqli_num_rows($ses_sql) > 0) {
                        $row1 = mysqli_fetch_array($ses_sql);
                        $se = isset($row1['section_dept']) ? $row1['section_dept'] : '';
                    } else {
                        // Fallback: Jika tidak ketemu, coba cari di sect_name
                        $sql1_fallback = "SELECT * FROM section WHERE sect_name='" . mysqli_real_escape_string($link, $sec) . "' LIMIT 1";
                        $ses_sql_fallback = mysqli_query($link, $sql1_fallback);
                        
                        if ($ses_sql_fallback && mysqli_num_rows($ses_sql_fallback) > 0) {
                            $row1 = mysqli_fetch_array($ses_sql_fallback);
                            $se = isset($row1['section_dept']) ? $row1['section_dept'] : '';
                        } else {
                            // Jika benar-benar tidak ketemu
                            $se = '-';
                            // Optional: Log untuk debugging
                            error_log("Section not found in database: " . $sec . " (normalized: " . $sec_normalized . ")");
                        }
                    }
                    ?>
                        <td>Department </td>
                        <td>:</td>
                        <td><input type="text" class="form-control" name="dep" readonly="readonly" value="<?php echo htmlspecialchars($se);?>"></td>
                    </tr>
                    <tr cellpadding="50px">
                        <td>No. Document &nbsp;&nbsp;</td>
                        <td>:&nbsp; &nbsp; &nbsp;</td>
                        <td><input type="text" class="form-control" name="nodoc" onkeypress="return preventSpaces(event)" oninput="removeSpaces(this)" placeholder="Tidak boleh menggunakan spasi"></td>
                    </tr>
                    <tr cellpadding="50px">
                        <td>No. Revision &nbsp;&nbsp;</td>
                        <td>:&nbsp; &nbsp; &nbsp;</td>
                        <td><input type="text" class="form-control" name="norev" onkeydown="allowOnlyNumbers(event)" oninput="removeNonNumeric(this)" placeholder="Hanya angka yang diperbolehkan">
                        <input type="hidden" name="state" value="<?php echo htmlspecialchars($state);?>" ></td>
                    </tr>

                    <tr>
                        <td>Review To</td>
                        <td>:</td>
                        <td>
                            
                         <select name="revto" class="form-control">
                                        <option value="-"> --- Select --- </option>
                                        
                                        <option value="Issue"> Issue </option>
                                        <option value="Revision"> Revision </option>
                                        <option value="Cancel"> Cancel </option>
                                    
                                        </option>
                                    </select>
                        </td>
                    </tr>

                    <tr>
                        <td>Category</td>
                        <td>:</td>
                        <td>
                            
                         <select name="cat" class="form-control">
                                        <option value="-"> --- Select --- </option>
                                        
                                        <option value="Internal"> Internal </option>
                                        <option value="External"> External </option>
                                        
                                    
                                        </option>
                                    </select>
                        </td>
                    </tr>

                    <tr>
                        <td>Document Type</td>
                        <td>:</td>
                        <td>
                            <?php
                            // Generate select from $docTypes. If no JSON, show default options (backward compat).
                            if (!empty($docTypes)) {
                                echo '<select name="type1" class="form-control">';
                                echo '<option value="-"> --- Select Type --- </option>';
                                // $docTypes may be category=>array; flatten if needed
                                // Accept both simple list and categorized structure
                                $flat = [];
                                $is_assoc = array_keys($docTypes) !== range(0, count($docTypes)-1);
                                if ($is_assoc) {
                                    // categorized: pick items from all categories
                                    foreach ($docTypes as $catk => $items) {
                                        if (is_array($items)) {
                                            foreach ($items as $it) {
                                                if (is_string($it)) $flat[] = $it;
                                                elseif (is_array($it) && isset($it['name'])) $flat[] = $it['name'];
                                            }
                                        }
                                    }
                                } else {
                                    // simple list
                                    foreach ($docTypes as $it) {
                                        if (is_string($it)) $flat[] = $it;
                                        elseif (is_array($it) && isset($it['name'])) $flat[] = $it['name'];
                                    }
                                }
                                // deduplicate while preserving order
                                $seen = [];
                                foreach ($flat as $dt) {
                                    if ($dt === '' || in_array($dt, $seen)) continue;
                                    $seen[] = $dt;
                                    $sel = '';
                                    if ($prefill_type !== '' && strcasecmp($prefill_type, $dt) === 0) $sel = 'selected';
                                    echo '<option value="'.htmlspecialchars($dt).'" '. $sel .'>'.htmlspecialchars($dt).'</option>';
                                }
                                echo '</select>';
                            } else {
                                // fallback to previous static list
                                ?>
                                <select name="type1" class="form-control">
                                    <option value="-"> --- Select Type --- </option>
                                    <option value="Form"> Form </option>
                                    <option value="Procedure"> Procedure </option>
                                    <option value="WI"> WI </option>
                                    <option value="Monitor Sample"> Monitor Sample </option>
                                    <option value="MSDS"> MSDS </option>
                                    <option value="Material Spec"> Material Spec </option>
                                    <option value="ROHS"> ROHS </option>
                                </select>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>

                    <tr>
                        <td>Section</td>
                        <td>:</td>
                        <td>
                            <?php 
                        $sect="select * from section order by id_section";
                        $sql_sect=mysqli_query($link, $sect);

                    ?>
                         <select id="section" name="section" class="form-control" >
                                        <option value="-"> --- Select Section --- </option>
                                        <?php while($data_sec = mysqli_fetch_array( $sql_sect )) 
                                            { 
                                                // Gunakan id_section yang sudah normalized
                                                $section_id = htmlspecialchars($data_sec['id_section']);
                                                $section_display = htmlspecialchars($data_sec['sect_name']); // Display tetap yang lengkap
                                            ?>
                                            <option value="<?php echo $section_id; ?>"> 
                                                <?php echo $section_display; ?> 
                                            </option>
                                        <?php } ?>
                                        </option>
                                    </select>
                        </td>
                    </tr>

                    <tr>
                        <td>Device</td>
                        <td>:</td>
                        <td>
                            <?php 
                        $sect="select * from device where status='Aktif' order by name";
                        $sql_sect=mysqli_query($link, $sect);

                    ?>
                         <select id="device" name="device" class="form-control" >
                                        <option value="-"> --- Select Device --- </option>
                                        <option value="General Production"> General Production </option>
                                        <?php while($data_sec = mysqli_fetch_array( $sql_sect )) 
                                        { ?>
                                        <option value="<?php echo htmlspecialchars($data_sec['name']); ?>"> <?php echo htmlspecialchars($data_sec['name']); ?> </option>
                                        <?php } ?>
                                        </option>
                                    </select>
                        </td>
                    </tr>

                    <tr>
                        <td>Process</td>
                        <td>:</td>
                        <td>
                             <span id="wait_1" style="display: none;">
                    <img alt="Please Wait" src="images/wait.gif"/>
                    </span>
                    <span id="result_1" style="display: none;">
                        
                    </span>
                        </td>
                    </tr>

                    <tr>
                        <td>Doc. Title</td>
                        <td>:</td>
                        <td><input type="text" class="form-control" name="title1"></td>
                    </tr>

                    <tr>
                        <td>Doc. Description</td>
                        <td>:</td>
                        <td >
                        <textarea  class="form-control" name="desc" cols="40" rows="10" wrap="physical"></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td>Requirement Document</td>
                        <td>:</td>
                        <td >
                            <table>
                                <tr><input type="radio" aria-label="ISO 9001" name="iso" value="1" >&nbsp; ISO 9001 <br /></tr>
                                <tr><input type="radio" aria-label="ISO 14001" name="iso" value="2" >&nbsp; ISO 14001 <br /></tr>
                                <tr><input type="radio" aria-label="ISO 45001" name="iso" value="4" >&nbsp; ISO 45001 <br /></tr>
                                <tr><input type="radio" aria-label="OHSAS" name="iso" value="3" >&nbsp; OHSAS / SMK3<br /></tr>
                                <tr><input type="radio" aria-label="indlaw" name="iso" value="5" >&nbsp; Indonesian Law <br /></tr>
                            </table>
                        </td>
                    </tr>

                     <tr>
                        <td>Document Transfer System</td>
                        <td>:</td>
                        <td >
                            <table>                             
                                <tr><input type="checkbox" aria-label="RADF" name="radf" value="1" checked disabled>&nbsp; RADF (mandatory) <br /></tr>
                                <tr><input type="checkbox" aria-label="Sequence Training" name="seqtrain" value="1" >&nbsp; Sequence Training <br /></tr>
                                <tr><input type="checkbox" aria-label="Direct Training" name="dirtrain" value="1" >&nbsp; Direct Training <br /></tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td>Related/Addopted Document</td>
                        <td>:</td>
                        <td>
                        <input type="text" class="form-control" name="rel[1]">
                        <input type="text" class="form-control" name="rel[2]">
                        <input type="text" class="form-control" name="rel[3]">
                        <input type="text" class="form-control" name="rel[4]">
                        <input type="text" class="form-control" name="rel[5]">
                        </td>
                    </tr>

                    <tr>
                        <td>Upload File Document (*Di-Review)<br />
                        (File .pdf untuk WI/Procedure)<br />
                        (File .xlsx untuk Form) <span style="color: red;">*Required</span>
                        </td>
                        <td>:</td>
                        <td>
                        <input type="file" class="form-control" name="file" required>
                        </td>
                    </tr>

                    <tr>
                        <td>Upload File Master(.xlsx/.docx) <span style="color: red;">*Required</span></td>
                        <td>:</td>
                        <td>
                        <input type="file" class="form-control" name="master" required>
                        </td>
                    </tr>

                    <tr>
                        <td>Revision reason/history</td>
                        <td>:</td>
                        <td >
                        <textarea  class="form-control" name="hist" cols="40" rows="10" wrap="physical"></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td></td>
                        <td></td>
                        <td>
                            <input type="submit" value="Save" name="submit" class="btn btn-success" onclick="return validasi()">
                        </td>
                    </tr>
                </form>
                 </table>
                 </div>

 </div>


 <?php
if (isset($_POST['submit']))
{
    include 'koneksi.php';

    // sanitize inputs (use mysqli_real_escape_string after koneksi)
    $nama = isset($_POST['user']) ? mysqli_real_escape_string($link, trim($_POST['user'])) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($link, trim($_POST['email'])) : '';
    $dep = isset($_POST['dep']) ? mysqli_real_escape_string($link, trim($_POST['dep'])) : '';
    $nodoc = isset($_POST['nodoc']) ? mysqli_real_escape_string($link, trim($_POST['nodoc'])) : '';
    $norev = isset($_POST['norev']) ? mysqli_real_escape_string($link, trim($_POST['norev'])) : '';
    $revto = isset($_POST['revto']) ? mysqli_real_escape_string($link, trim($_POST['revto'])) : '';
    
    // Type handling with GET fallback
    $type = isset($_POST['type1']) ? trim($_POST['type1']) : '';
    if (($type == '' || $type == '-') && isset($_GET['type']) && trim($_GET['type']) !== '') {
        $type = trim($_GET['type']);
    }
    $type = mysqli_real_escape_string($link, $type);
    
    // ========== NORMALISASI SECTION (CRITICAL UPDATE) ==========
    $section_raw = isset($_POST['section']) ? trim($_POST['section']) : '';
    $section = normalizeSection($section_raw); // Normalize section name!
    $section = mysqli_real_escape_string($link, $section);
    // ========== END NORMALISASI SECTION ==========
    
    $device = isset($_POST['device']) ? mysqli_real_escape_string($link, trim($_POST['device'])) : '';
    $process = isset($_POST['proc']) ? mysqli_real_escape_string($link, trim($_POST['proc'])) : '';
    $title = isset($_POST['title1']) ? mysqli_real_escape_string($link, trim($_POST['title1'])) : '';
    $desc = isset($_POST['desc']) ? mysqli_real_escape_string($link, trim($_POST['desc'])) : '';
    $cat = isset($_POST['cat']) ? mysqli_real_escape_string($link, trim($_POST['cat'])) : '';
    $iso = isset($_POST['iso']) ? intval($_POST['iso']) : 0;
    $seqtrain = isset($_POST['seqtrain'])? intval($_POST['seqtrain']):0;
    $dirtrain = isset($_POST['dirtrain'])? intval($_POST['dirtrain']):0;
    $tgl = date('d-m-Y');
    $state = isset($_POST['state']) ? mysqli_real_escape_string($link, trim($_POST['state'])) : '';

    // Server-side validation for spaces in nodoc
    if (strpos($nodoc, ' ') !== false) {
        ?>
        <script language='javascript'>
            alert('Nomor Dokumen tidak boleh mengandung spasi');
            document.location='upload.php';
        </script>
        <?php
        exit;
    }

    // Server-side validation for numeric-only norev
    if (!empty($norev) && !preg_match('/^\d+$/', $norev)) {
        ?>
        <script language='javascript'>
            alert('Nomor Revisi hanya boleh berisi angka');
            document.location='upload.php';
        </script>
        <?php
        exit;
    }

    // Server-side validation for file uploads
    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] == UPLOAD_ERR_NO_FILE) {
        ?>
        <script language='javascript'>
            alert('File Document harus diupload');
            document.location='upload.php';
        </script>
        <?php
        exit;
    }

    if (!isset($_FILES["master"]) || $_FILES["master"]["error"] == UPLOAD_ERR_NO_FILE) {
        ?>
        <script language='javascript'>
            alert('File Master harus diupload');
            document.location='upload.php';
        </script>
        <?php
        exit;
    }

    // Get uploader name AND original section from users table
    $user_query = "SELECT name, section FROM users WHERE username = '".mysqli_real_escape_string($link, $nama)."' LIMIT 1";
    $user_result = mysqli_query($link, $user_query);
    $user_data = mysqli_fetch_array($user_result);
    $uploader_name = isset($user_data['name']) ? mysqli_real_escape_string($link, $user_data['name']) : mysqli_real_escape_string($link, $nama);
    
    // ========== NORMALISASI ORIGINAL_SECTION (CRITICAL UPDATE) ==========
    $original_section_raw = isset($user_data['section']) ? $user_data['section'] : '';
    $original_section = normalizeSection($original_section_raw); // Normalize!
    $original_section = mysqli_real_escape_string($link, $original_section);
    // ========== END NORMALISASI ORIGINAL_SECTION ==========

    // Create snapshots of current data for historical tracking
    $original_dept = $dep;

    if ($cat=='External')
    {
        $sta_doc='Secured';
    }
    else
    {
        $sta_doc='Review';
    }

    $cek_opl = substr($nodoc, 0,3);

    if($cek_opl=='OPL')
    {
        $sta_doc='Secured';
    }
    if($type=='Monitor Sample')
    {
        $sta_doc='Secured';
    }
    if($type=='MSDS')
    {
        $sta_doc='Secured'; 
    }

    if($type=='Material Spec')
    {
        $sta_doc='Secured'; 
    }
    if($type=='ROHS')
    {
        $sta_doc='Secured'; 
    }

    $hist = isset($_POST['hist']) ? mysqli_real_escape_string($link, trim($_POST['hist'])) : '';

    // sanitize type for folder name: allow only letters, numbers, space, -, _ and &
    $safe_type = trim($type);
    $safe_type = preg_replace('/[^A-Za-z0-9 _\-&]/', '', $safe_type);
    if ($safe_type === '') $safe_type = 'others';
    $safe_dir_name = str_replace(' ', '_', $safe_type) . '/';

    // Ensure target dir exists
    $target_dir = $safe_dir_name;
    if (!is_dir($target_dir)) {
        if (!@mkdir($target_dir, 0755, true)) {
            // fallback to generic folder
            $target_dir = 'others/';
            if (!is_dir($target_dir)) @mkdir($target_dir, 0755, true);
        }
    }

    // process document file
    $doc_filename = basename($_FILES["file"]["name"]);
    $doc_filename = str_replace("'", "", $doc_filename); // basic sanitize
    $target_file_doc = $target_dir . $doc_filename;

    if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file_doc)) {
        ?>
        <script language='javascript'>
            alert('Upload File Document gagal. Periksa permission folder.');
            document.location='upload.php';
        </script>
        <?php
        exit;
    }

    // process master file
    $target_dir_master = "master/";
    if (!is_dir($target_dir_master)) @mkdir($target_dir_master, 0755, true);
    $master_filename = basename($_FILES["master"]["name"]);
    $master_filename = str_replace("'", "", $master_filename);
    $target_file_master = $target_dir_master . $master_filename;

    if ($_FILES["master"]["size"] > 15000000)
    {
        ?>
    
        <script language='javascript'>
                                alert('Ukuran File Master Terlalu Besar, Max 15Mb');
                                document.location='upload.php';
                            </script>

        <?php
        exit;
    }
    if (!move_uploaded_file($_FILES["master"]["tmp_name"], $target_file_master)) {
        ?>
        <script language='javascript'>
            alert('Upload File Master gagal. Periksa permission folder.');
            document.location='upload.php';
        </script>
        <?php
        exit;
    }

    // Prepare and run INSERT (use escaped values)
    $nama_file = mysqli_real_escape_string($link, $doc_filename);
    $nama_master = mysqli_real_escape_string($link, $master_filename);

    // ========== INSERT WITH NORMALIZED SECTION ==========
    $sql="INSERT INTO docu(no_drf,user_id,uploader_name,email,dept,original_dept,no_doc,no_rev,rev_to,doc_type,section,original_section,device,process,title,descript,iso,seqtrain,dirtrain,file,history,status,tgl_upload,category,final,file_asli,reminder)
    VALUES (0,'$nama','$uploader_name','$email','$dep','$original_dept','$nodoc','$norev','$revto','$type','$section','$original_section','$device','$process','$title','$desc',$iso,$seqtrain,$dirtrain,'$nama_file','$hist','$sta_doc','$tgl','$cat','','$nama_master',0)";

    $res=mysqli_query($link, $sql);
    $drf=mysqli_insert_id($link);

    if($res)
    {
        // Decide post-insert navigation
        if(($state != 'Admin' or $cat != 'External' ) and ($type!='Material Spec' and $type!='ROHS' and $type!='MSDS'))
        {
            ?>
            <script language='javascript'>
                alert('Document Uploaded , please set the approvers');
                document.location='set_approver.php?id_doc=<?php echo $drf?>&section=<?php echo urlencode($section)?>&type=<?php echo urlencode($type)?>&iso=<?php echo $iso?>&nodoc=<?php echo urlencode($nodoc);?>&title=<?php echo urlencode($title);?>';
            </script>
            <?php 
        }
        else
        {
            ?>
            <script language='javascript'>
                alert('Document Uploaded');
                document.location='upload.php';
            </script>
            <?php
        }

        // Send notification email (non-blocking)
        require 'PHPMailer/PHPMailerAutoload.php';
        
        $mail = new PHPMailer();

        // setting          
        $mail->IsSMTP();// send via SMTP
        include 'smtp.php';

        // pengirim
        $mail->setFrom('dc_admin@ssi.sharp-world.com');
        $mail->FromName = "Admin Document Online System";

        // penerima
        $mail->addAddress($email);
        if($cat=='External')
        {
            $mail->addAddress("qa01@ssi.sharp-world.com");
        }
        
        $mail->WordWrap = 50;                              // set word wrap
        $mail->IsHTML(true);                               // send as HTML
            
        $mail->Subject  = "Document Uploaded" ;
        $mail->Body     =  "Attention Mr./Mrs. : Originator <br /> This following <span style='color:green'>".htmlspecialchars($type)."</span> document was 
        <span style='color:green'>Uploaded</span> into the System <br /> No. Document : ".htmlspecialchars($nodoc)."<br /> Revision History : ".nl2br(htmlspecialchars($hist))."<br />
        Please Login into <a href='192.168.132.34/document'>Document Online System</a> to monitor the Document, Thank You";

        // try send but don't break user flow on failure
        if(!$mail->Send()) {
            // log if needed: error_log("Mailer error: ".$mail->ErrorInfo);
        }
    }
    else
    {
        ?>
        <script language='javascript'>
        // alert('Document Upload Failed');
        document.location='upload.php';
        </script>
        <?php
    }

    // process related docs
    if (isset($_POST["rel"]) && is_array($_POST["rel"])) {
        $jumlah = count($_POST["rel"]);
        for($i=0; $i < $jumlah; $i++) 
        {
            $no_doc = mysqli_real_escape_string($link, trim($_POST["rel"][$i]));

            if ($no_doc <> '' )
            {
                $q=mysqli_query($link, "INSERT INTO rel_doc(id,no_drf,no_doc) VALUES ('',$drf,'$no_doc')"); 
            }
        }
    }

    if ($norev=='0')
    {
        $insert="INSERT INTO distribusi(id_dis,no_drf,pic,give,date_give,location,receiver,retrieve,retrieve_from,retrieve_date)
        VALUES('',$drf,'','','','','','','','')";
        $result=mysqli_query($link, $insert);
    }
}
?>