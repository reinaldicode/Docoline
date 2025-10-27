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
$sql="select * from device where id_device='$id'";
$res=mysqli_query($link, $sql);

while($data = mysqli_fetch_array($res)) 
{
 ?>

<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-4 well well-lg">
 <h2>Edit Device</h2>

				<form action="" method="GET">
				 <table >
				 	<tr cellpadding="20px">
				 		<td>Nama Device &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" value="<?php echo "$data[name]" ?>" name="nama"></td>
				 	</tr>
				 	
				 	<tr>
				 		<td>Group</td>
				 		<td>:</td>
				 		<td>
				 			
						 <select name="group" class="form-control">
										<option value="0" > --- Select Group --- </option>
										<option value="Compound" <?php if ($data['group_dev']=="Compound") {echo 'selected';} ?> > Compound </option>
										<option value="Laser" <?php if ($data['group_dev']=="Laser") {echo 'selected';} ?>  > Laser </option>
										<option value="Opto" <?php if ($data['group_dev']=="Opto") {echo 'selected';} ?>  > Opto </option>
										
									</select>
				 		</td>
				 		<?php $id=$_GET['id'];?>
				 		<input type="hidden" name="id" value=<?php echo $id;?>>
				 	</tr>
				 	<tr>
				 		<td>Status</td>
				 		<td>:</td>
				 		<td>
				 			<select name="status" class="form-control">
				 				<option value="0"> --- Select Status --- </option>
				 				<option value="Aktif" <?php if ($data['status']=="Aktif") {echo 'selected';} ?>  > Aktif </option>
				 				<option value="Tidak Aktif" <?php if ($data['status']=="Tidak Aktif") {echo 'selected';} ?> > Tidak Aktif </option>
				 			</select>
				 		</td>
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
$group=$_GET['group'];
$status=$_GET['status'];

//$id=$_GET['id'];




$sql="update device set name='$nama' , group_dev='$group' , status='$status' where id_device='$id'";

$res=mysqli_query($link, $sql);

if (!$res) {
    die("Connection failed: " . mysqli_error());} else{
echo"<script>
    document.location='conf_device.php';
     </script>";
 }
}
 ?>