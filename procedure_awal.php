<link href="bootstrap/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
<link href="bootstrap/css/bootstrap-responsive.min.css" media="all" type="text/css" rel="stylesheet">
<link href="bootstrap/css/facebook.css" media="all" type="text/css" rel="stylesheet">

<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="bootstrap/js/facebook.js"></script>

<link rel="stylesheet" href="bootstrap/css/datepicker.css">
<link rel="stylesheet" href="bootstrap/css/bootstrap-select.min.css">
<link rel="stylesheet" href="bootstrap/css/bootstrap.css">
<script src="bootstrap/js/jquery.min.js"></script>
<script src="bootstrap/js/bootstrap-datepicker.js"></script>
</head>

<?php
include 'index.php';
include 'koneksi.php';
extract($_REQUEST);
?>
<br />
<br />
<h3>Procedure List</h3>
<form action="" method="GET">
    <div class="col-sm-4">

    <?php 
        $sect="select * from section order by sect_name";
        $sql_sect=mysqli_query($link, $sect);
    ?>
        <select name="section" class="form-control">
            <option value="0"> --- Select Section --- </option>
            <?php while($data_sec = mysqli_fetch_array($sql_sect)) 
            { ?>
            <option value="<?php echo htmlspecialchars($data_sec['id_section']); ?>"> <?php echo htmlspecialchars($data_sec['sect_name']); ?> </option>
            <?php } ?>
        </select>
        
        <select name="status" class="form-control">
            <option value="0"> --- Select Status --- </option>
            <option value="Secured" selected> Approved </option>
            <option value="Review"> Review </option>
            <option value="Pending"> Pending </option>
        </select>			
        
        <input type='hidden' name='by' value='no_drf'>
        <input type="submit" name="submit" value="Show" class="btn btn-primary" />
    </div>
    <br />
    <br />
    <br />
</form>

<?php
if (isset($_GET['submit'])){
    $section = $_GET['section'];
    $status = $_GET['status'];
    $by = $_GET['by'];

    if ($section == '0'){
        $sql = "select * from docu where doc_type='Procedure' and status='Secured' order by $by limit 150";	
    } else {
        $sql = "select * from docu where section='$section' and doc_type='Procedure' and status='$status' order by $by limit 150";
    }
?>

<br /><br />

<?php
    $res = mysqli_query($link, $sql);
?>
<br />
<table class="table table-hover">
<h1> Procedure List For Section: <strong><?php echo htmlspecialchars($section);?></strong> , Status: <strong><?php echo htmlspecialchars($status); ?></strong></h1>
<thead bgcolor="#00FFFF">
<tr>
    <td>No</td>
    <td>Date</td>
    <td><a href='procedure_login.php?section=<?php echo urlencode($section); ?>&by=no_doc&status=<?php echo urlencode($status); ?>&submit=Show'>No. Document</a></td>
    <td>No Rev.</td>
    <td><a href='procedure_awal.php?section=<?php echo urlencode($section); ?>&by=no_drf&status=<?php echo urlencode($status); ?>&submit=Show'>drf</a></td>
    <td><a href='procedure_awal.php?section=<?php echo urlencode($section); ?>&by=title&status=<?php echo urlencode($status); ?>&submit=Show'>Title</a></td>
    <td>Process</td>
    <td>Section</td>
    <td>Action</td>
    <td>Sosialisasi</td>
</tr>
</thead>
<?php
$i = 1;
while($info = mysqli_fetch_array($res)) 
{ ?>
<tbody>
<tr>
    <td><?php echo $i; ?></td>
    <td><?php echo htmlspecialchars($info['tgl_upload']);?></td>
    <td><?php echo htmlspecialchars($info['no_doc']);?></td>
    <td><?php echo htmlspecialchars($info['no_rev']);?></td>
    <td><?php echo htmlspecialchars($info['no_drf']);?></td>
    <td>
        <?php if ($info['no_drf'] > 12967) { $tempat = $info['doc_type']; } else { $tempat = 'document'; } ?>
        <a href="<?php echo htmlspecialchars($tempat); ?>/<?php echo htmlspecialchars($info['file']); ?>" >
            <?php echo htmlspecialchars($info['title']);?>
        </a>
    </td>
    <td><?php echo htmlspecialchars($info['process']);?></td>
    <td><?php echo htmlspecialchars($info['section']);?></td>
    <td>
        <a href="detail.php?drf=<?php echo urlencode($info['no_drf']);?>&no_doc=<?php echo urlencode($info['no_doc']);?>&log=1" class="btn btn-xs btn-info" title="lihat detail"><span class="glyphicon glyphicon-search" ></span></a>
        <a href="radf.php?drf=<?php echo urlencode($info['no_drf']);?>&section=<?php echo urlencode($info['section'])?>&log=1" class="btn btn-xs btn-info" title="lihat RADF"><span class="glyphicon glyphicon-eye-open" ></span> </a>
    </td>
    <td>
        <?php 
        // periksa apakah ada bukti sosialisasi
        $has_sos = !empty($info['sos_file']);
        
        if ($has_sos) {
            echo '<a href="lihat_sosialisasi.php?drf='.urlencode($info['no_drf']).'" class="btn btn-xs btn-primary" title="Lihat Detail Sosialisasi">';
            echo '<span class="glyphicon glyphicon-file"></span>';
            echo '</a>';
        } else {
            echo '<a href="lihat_sosialisasi.php?drf='.urlencode($info['no_drf']).'" class="btn btn-xs btn-default" title="Belum ada bukti sosialisasi">';
            echo '<span class="glyphicon glyphicon-file"></span>';
            echo '</a>';
        }
        ?>
    </td>
</tr>
</tbody>
<?php 
$i++;
} 
}
?> 
</table>