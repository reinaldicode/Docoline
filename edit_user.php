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
$sql="select * from users where id='$id'";
$res=mysqli_query($link, $sql);

while($data = mysqli_fetch_array($res)) 
{
//$sql="select "

 ?>

<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-4 well well-lg">
 <h2>Edit User</h2>

				<form action="" method="GET">
				 <table >
				 	<tr cellpadding="20px">
				 		<td>Nama User &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" name="nama" value="<?php echo "$data[name]" ?>" ></td>
				 	</tr>
				 	<tr cellpadding="20px">
				 		<td>Username &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" name="username" value="<?php echo "$data[username]"?>" ></td>
				 	</tr>
				 	<tr>
				 		<td>Email </td>
				 		<td>:</td>
				 		<td><input type="text" class="form-control" name="email" value=<?php echo "$data[email]"?> ></td>
				 	</tr>
				 	<tr cellpadding="20px">
				 		<td>Password &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" name="password" value=<?php echo "$data[password]"?> ></td>

				 	</tr>
				 	<tr>
				 		<td>Section</td>
				 		<td>:</td>
				 		<td>
				 			<?php 

				 			$id=$_GET['id'];
				 			//echo "$id";
						$sect="select * from section";
						$sql_sect=mysqli_query($link, $sect);

					?>
					<input type="hidden" name="id" value=<?php echo $id;?>>
						 <select name="section" class="form-control">
										<option value="0"> --- Select Section --- </option>
										<?php 
										$section=$data[section];
										while($data_sec = mysqli_fetch_array( $sql_sect )) 
										{ ?>
										<option value="<?php echo "$data_sec[sect_name]"; ?>" <?php if ($data_sec['sect_name']==$section) {echo 'selected';} ?> > <?php echo "$data_sec[sect_name]"; ?> </option>
										<?php } ?>
										</option>
									</select>
									 	</td>
				 	</tr>
				 	<tr>
				 		<td>State</td>
				 		<td>:</td>
				 		<td>
				 			<select name="state" class="form-control">
				 				<option value="0"> --- Select State --- </option>
				 				<option value="Admin" <?php if ($data['state']=="Admin") {echo "selected";} ?> > Admin </option>
				 				<option value="Originator"  <?php if ($data['state']=="Originator") {echo "selected";} ?> > Originator </option>
				 				<option value="Approver"  <?php if ($data['state']=="Approver") {echo "selected";} ?> > Approver </option>
				 			</select>
				 		</td>
				 	</tr>
				 	<tr>
				 		<td>Jabatan</td>
				 		<td>:</td>
				 		<td>
				 			<select name="level" class="form-control">
				 				<option value="0"> --- Select Level --- </option>
				 				<option value="1" <?php if ($data['level']=="9") {echo "selected";} ?> > Direktur </option>
				 				<option value="2" <?php if ($data['level']=="8") {echo "selected";} ?> > Senior Man. </option>
				 				<option value="3" <?php if ($data['level']=="7") {echo "selected";} ?> > Manager </option>
				 				<option value="4" <?php if ($data['level']=="6") {echo "selected";} ?> > Assist. Man </option>
				 				<option value="5" <?php if ($data['level']=="5") {echo "selected";} ?>> Supervisor </option>
				 				<option value="6" <?php if ($data['level']=="4") {echo "selected";} ?> > Asst. Sup. </option>
				 				<option value="7" <?php if ($data['level']=="3") {echo "selected";} ?> > Leader/Officer </option>
				 				<option value="8" <?php if ($data['level']=="2") {echo "selected";} ?> > Foreman/Staff </option>
				 				<option value="9" <?php if ($data['level']=="1") {echo "selected";} ?> > Operator </option>
				 			</select>
				 		</td>
				 	</tr>
				 	<tr>
				 		<td></td>
				 		<td></td>
				 		<?php } ?> 
				 		<td>
				 			<input type="submit" value="Update" name="submit" class="btn btn-success">
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
$username=$_GET['username'];
$email=$_GET['email'];
$password=$_GET['password'];
$section=$_GET['section'];
$state=$_GET['state'];


$id=$_GET['id'];



$sql="update users set name='$nama' , username='$username' , email='$email', password='$password', section='$section', state='$state',level='$level' where id=$id ";

$res=mysqli_query($link, $sql);
//echo $sql;
if (!$res) {
    die("Connection failed: " . mysqli_error());} else{
echo"<script>
     document.location='conf_user.php';
     </script>";
}
}
 ?>