<?php
 include ('koneksi.php');
 extract($_REQUEST);
 $query="delete from rev_doc where id='$id' ";
// $id_doc=
	$hasil=mysqli_query($link, $query);
	if ($hasil){
	?>
	<script language='javascript'>
							alert('Approver removed');
							document.location='my_doc.php';
						</script>
						<?php
	}
	
	
	
	?>