<?php

include('header.php');
?>

<br />
<br />
<br />
<br />




	
	
	

		 <script src="bootstrap/js/jquery.min.js"></script>
        <script src="bootstrap/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/script.js"></script>
		<style type="text/css">
		.auto-complete{
			float:left;
			z-index:9999;
		}
		</style>
	
	
</head>





<?php

//include 'index.php';
include 'koneksi.php';

 ?>
    
    


	 <?php require_once('Connections/config.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysqli_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

//mysql_select_db($database_config, $config);
$query_rsData = "SELECT distinct no_doc FROM docu ORDER BY no_doc ASC";
$rsData = mysqli_query($link, $query_rsData) or die(mysqli_error());
$row_rsData = mysqli_fetch_assoc($rsData);
$totalRows_rsData = mysqli_num_rows($rsData);
?>
<!doctype html>

<meta charset="utf-8">

<link rel="stylesheet" href="jquery-ui-1.10.3/themes/base/jquery.ui.all.css">
	<script src="jquery-ui-1.10.3/jquery-1.9.1.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.core.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.widget.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.position.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.menu.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.autocomplete.js"></script>
	
	<script>
	$(function() {
		var availableTags = [
		<?php do { ?>
			"<?php echo $row_rsData['no_doc']; ?>",
		<?php } while($row_rsData = mysqli_fetch_assoc($rsData)); ?>
		];
		$( "#cari" ).autocomplete({
			source: availableTags
		});
	});
	</script>

<br />
<br />

<div class="row">
<div class="col-xs-4" style="margin-left=50px;">
  
  <form name="form1" method="GET" action="">
    <p>
      
      Input Document No. : <input name="doc_no" type="text" id="cari" size="60" class="form-control">
    </p>
    <p>
      <input type="submit" name="submit" id="submit" value="Show" class="btn btn-primary">
      <input type="reset" name="submit2" id="submit2" value="Reset" class="btn btn-warning">
    </p>
  </form>
  </div>
  </div>



<?php
mysqli_free_result($rsData);
?>





    			
    			
    			
	
	

<?php
if (isset($_GET['submit'])){





$no_doc=$_GET['doc_no'];



	$sql="select * from docu where no_doc='$no_doc'  order by no_drf desc limit 1";

?>
<br />

<?php

$res=mysqli_query($link, $sql);
//$rows = mysql_num_rows($res);

// echo $sql;


?>
<table class="table table-hover">
<thead bgcolor="#00FFFF">
<tr>
	<td>No</td>
	<td>Date</td>
	<td>No Document</td>
	<td>No Rev.</td>
	<td>No. Drf</td>
	<td>Title</td>
	<td>Process</td>
	<td>Section</td>
	<td>Detail</td>
	
	
</tr>
</thead>
<?php
$i=1;
while($info = mysqli_fetch_array($res)) 
{ ?>
<tbody>
<tr>
	<td>
		<?php echo $i; ?>
	</td>
	<td>
		<?php echo "$info[tgl_upload]";?>
	</td>
	<td>
		<?php echo "$info[no_doc]";?>
	</td>
	<td>
		<?php echo "$info[no_rev]";?>
	</td>
	<td>
		<?php echo "$info[no_drf]";?>
	</td>
	<td>
		<?php echo "$info[title]";?>
	</td>
	<td>
		<?php echo "$info[process]";?>
	</td>
	<td>
		<?php echo "$info[section_id]";?>
	</td>
	<td>
		<a href="detail_dist.php?drf=<?php echo $info[no_drf];?>&no_doc=<?php echo $info[no_doc];?>&title=<?php echo $info[title]?>" class="btn btn-xs btn-info" title="lihat detail"><span class="glyphicon glyphicon-search" ></span> Detail Distribution</a>
	</td>
	
	
</tr>
</tbody>
<div>
<?php 
$i++;} 


}


?> 
</div>