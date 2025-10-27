<?php
session_start();
include('header.php');
include('config_head.php');
?>

<br />
<br />


</head>



<?php

//include 'index.php';
include 'koneksi.php';

 ?>

<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-4 well well-lg">
 <h2>Add Device</h2>

				<form action="" method="GET">
				 <table >
				 	<tr cellpadding="20px">
				 		<td>Nama Device &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" name="nama"></td>
				 	</tr>
				 	
				 	<tr>
				 		<td>Group</td>
				 		<td>:</td>
				 		<td>
				 			
						 <select name="group" class="form-control">
										<option value="0"> --- Select Group --- </option>
										<option value="Compound"> Compound </option>
										<option value="Laser"> Laser </option>
										<option value="Opto"> Opto </option>
										<option value="Masker"> Masker </option>
										
									</select>
				 		</td>
				 	</tr>
				 	<tr>
				 		<td>Status</td>
				 		<td>:</td>
				 		<td>
				 			<select name="status" class="form-control">
				 				<option value="0"> --- Select Status --- </option>
				 				<option value="Aktif"> Aktif </option>
				 				<option value="Tidak Aktif"> Tidak Aktif </option>
				 			</select>
				 		</td>
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
if (isset($_GET['submit'])){


include 'koneksi.php';

$nama=$_GET['nama'];
$group=$_GET['group'];
$status=$_GET['status'];






echo $sql="insert into device (name,group_dev,status) value ('$nama','$group','$status')";

$res=mysqli_query($link, $sql);

if ($res){
echo"<script>
     document.location='conf_device.php';
     </script>";
}
}
 ?>