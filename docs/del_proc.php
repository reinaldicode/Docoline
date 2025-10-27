<?php
 include ('koneksi.php');
 $query="delete from process where id_proc='$id' ";
// $id_doc=
	$hasil=mysqli_query($link, $query);
	if ($hasil){
	?>
	<script language='javascript'>
							alert('Process removed');
							document.location='conf_process.php';
						</script>
						<?php
	}
	
	
	
	?>