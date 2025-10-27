<?php
 include ('koneksi.php');
 extract($_REQUEST);
 $query="delete from docu where no_drf='$drf' ";
// $id_doc=
	$hasil=mysqli_query($link, $query);
	if ($hasil){
	?>
	<script language='javascript'>
							alert('Document removed');
							document.location='my_doc.php';
						</script>
						<?php
	}
	
	
	
	?>