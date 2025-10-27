<?php require_once('koneksi.php'); ?>
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
$query_rsData = "SELECT * FROM document ORDER BY no_doc ASC";
$rsData = mysqli_query($link, $query_rsData) or die(mysqli_error());
$row_rsData = mysqli_fetch_assoc($rsData);
$totalRows_rsData = mysqli_num_rows($rsData);
?>
<!doctype html>

<meta charset="utf-8">

<link href="css/autocomplete.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="jquery-ui-1.10.3/themes/base/jquery.ui.all.css">
	<script src="jquery-ui-1.10.3/jquery-1.9.1.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.core.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.widget.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.position.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.menu.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.autocomplete.js"></script>
	<link rel="stylesheet" href="jquery-ui-1.10.3/demos/demos.css">
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



<div id="wrapper">
  
  <form name="form1" method="post" action="">
    <p>
      <label for="cari">Nama institusi<span class="abu"> (autocomplete)</span></label>
      <input name="cari" type="text" id="cari" size="60">
    </p>
    <p>
      <input type="submit" name="submit" id="submit" value="Submit">
      <input type="reset" name="submit2" id="submit2" value="Reset">
    </p>
  </form>
  



<?php
mysqli_free_result($rsData);
?>
