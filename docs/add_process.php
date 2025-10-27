<?php
session_start();
include('header.php');
include('config_head.php');
?>

<br />
<br />


</head>



<?php
 ?>

<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-4 well well-lg">
 <h2>Tambah Process</h2>

				<form action="" method="GET">
				 <table >
				 	<tr cellpadding="50px">
				 		<td>Nama Process &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" name="namaproc"></td>
				 		
				 	</tr>
				 	
				 	
				 	<tr>
				 		<td></td>
				 		<td></td>
				 		<td>
				 			<input type="submit" value="Save" name="submit" class="btn btn-success">
				 		</td>
				 	</tr>
				</form>
				
				 </table>
				 </div>

 </div>


 <?php
if (isset($_GET['submit']))
{
	include 'koneksi.php';

	$nama=$_GET['namaproc'];

	$sql="INSERT INTO process(proc_name) VALUES('$nama')";

	$res=mysqli_query($link, $sql);

	if (!$res)
	{
		echo "Error message : " . mysqli_errno($link) . ": " . mysqli_error($link) . "\n";
	}
	else
	{
		echo"<script>
		document.location='conf_process.php';
		</script>";
	}
}
 ?>