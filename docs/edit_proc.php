<?php

include('header.php');
include('config_head.php');
?>

<br />
<br />


</head>



<?php

//include 'index.php';
include 'koneksi.php';

$id=$_GET['id'];
//echo $id;
$sql="select * from process where id_proc='$id'";
$res=mysqli_query($link, $sql);

while($data = mysqli_fetch_array($res)) 
{
 ?>

<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-4 well well-lg">
 <h2>Edit Process</h2>

				<form action="" method="GET">
				 <table >
				 	<tr cellpadding="50px">
				 		<td>Nama Process &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" value="<?php echo "$data[proc_name]" ?>" name="nama"></td>
				 		<input type="hidden" name="id" value=<?php echo $id;?>>
				 	</tr>
				 	
				 	
				 	<tr>
				 		<td></td>
				 		<td></td>
				 		<td>
				 			<input type="submit" value="Update" name="submit" class="btn btn-success">
				 		</td>
				 	</tr>
				</form>
				<?php } ?>
				 </table>
				 </div>

 </div>


 <?php
if (isset($_GET['submit'])){


include 'koneksi.php';

$nama=$_GET['nama'];


$id=$_GET['id'];




$sql="update process set proc_name='$nama' where id_proc='$id'";

$res=mysqli_query($link, $sql);

if (!$res) {
    die("Connection failed: " . mysqli_error());} else{
echo"<script>
    document.location='conf_process.php';
     </script>";
 }
}
 ?>