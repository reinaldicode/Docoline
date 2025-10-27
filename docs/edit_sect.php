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
$sql="select * from section where id_section='$id'";
$res=mysqli_query($link, $sql);

while($data = mysqli_fetch_array($res)) 
{
 ?>

<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-4 well well-lg">
 <h2>Edit Section</h2>

				<form action="" method="GET">
				 <table >
				 	<tr cellpadding="20px">
				 		<td>Nama Section &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" value="<?php echo "$data[sect_name]" ?>" name="nama"></td>
				 	</tr>
				 	
				 	<tr>
				 		<td>Group</td>
				 		<td>:</td>
				 		<td>
				 			
						 <select name="group" class="form-control">
										<option value="0" > --- Select Department --- </option>
										<option value="Administration" <?php if ($data['section_dept']=="Administration") {echo 'selected';} ?> > Administration </option>
										<option value="Quality Control" <?php if ($data['section_dept']=="Quality Control") {echo 'selected';} ?>  > Quality Control </option>
										<option value="Engineering" <?php if ($data['section_dept']=="Engineering") {echo 'selected';} ?>  > Engineering </option>
										<option value="Production" <?php if ($data['section_dept']=="Production") {echo 'selected';} ?>  > Production </option>
										
									</select>
				 		</td>
				 		<?php $id=$_GET['id'];?>
				 		<input type="hidden" name="id" value="<?php echo $id;?>">
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


$id=$_GET['id'];




$sql="update section set sect_name='$nama' , section_dept='$group' where id_section='$id'";
//echo $sql;
$res=mysqli_query($sql);

if (!$res) {
    die("Connection failed: " . mysqli_error());} else{
echo"<script>
    document.location='conf_section.php';
     </script>";
 }
}
 ?>