<?php
 include ('koneksi.php');
 $query="delete from section where id_section='$id' ";
// $id_doc=
	$hasil=mysqli_query($link, $query);
	if ($hasil){
	?>
	<script language='javascript'>
							alert('Section removed');
							document.location='conf_section.php';
						</script>
						<?php
	}
	
	
	
	?>