<?php
 include ('koneksi.php');
 $query="delete from users where id='$id' ";
// $id_doc=
	$hasil=mysqli_query($link, $query);
	if ($hasil){
	?>
	<script language='javascript'>
							alert('User removed');
							document.location='conf_user.php';
						</script>
						<?php
	}
	
	
	
	?>