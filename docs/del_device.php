<?php
 include ('koneksi.php');
 $id=$_GET['id'];
echo $query="delete from device where id_device='$id' ";
// $id_doc=
	$hasil=mysqli_query($link, $query);
	if ($hasil){
	?>
	<script language='javascript'>
							alert('Device removed');
							document.location='conf_device.php';
						</script>
						<?php
	}
	
	
	
	?>