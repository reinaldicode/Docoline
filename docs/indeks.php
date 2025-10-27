<?php
include ('koneksi.php');
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<?php
$tgl = date('d-m-Y');
$query="SELECT no_doc, file FROM docu WHERE tgl_upload = '$tgl' AND STATUS = 'Review' ORDER BY doc_type ";
echo $query;
$res=mysqli_query($link, $query)or die(mysqli_error());
$limit = 3;
$arProc = array();
$arForm = array();
$arWI = array();
while ($data=mysqli_fetch_array($res)){ 
	echo $data['doc_type'];
	if($data['doc_type']=='Form' && sizeof($arForm < $limit)){ 
		$arForm[] = $data['file'];
	} else if($data['doc_type']=='WI' && sizeof($arWI < $limit)){ 
		$arWI[] = $data['file'];
	} else if($data['doc_type']=='Procedure' && sizeof($arProc < $limit)){ 
		$arProc[] = $data['file'];
	}
}
?>
<ul>
	<li>Form: <?php echo implode(', ', $arrForm); ?></li>
	<li>WI: <?php echo implode(', ', $arrWI); ?></li>
	<li>Procedure: <?php echo implode(', ', $arrProc); ?></li>
</ul>
</body>
</html>