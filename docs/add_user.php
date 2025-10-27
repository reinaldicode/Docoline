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
 <h2>Add User</h2>

				<form action="" method="GET">
				 <table >
				 	<tr cellpadding="20px">
				 		<td>Nama User &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" name="nama"></td>
				 	</tr>
				 	<tr cellpadding="20px">
				 		<td>Username &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" name="username"></td>
				 	</tr>
				 	<tr>
				 		<td>Email </td>
				 		<td>:</td>
				 		<td><input type="text" class="form-control" name="email"></td>
				 	</tr>
				 	<tr cellpadding="20px">
				 		<td>Password &nbsp;&nbsp;</td>
				 		<td>:&nbsp;	&nbsp; &nbsp;</td>
				 		<td><input type="text" class="form-control" name="password"></td>
				 	</tr>
				 	<tr>
				 		<td>Section</td>
				 		<td>:</td>
				 		<td>
				 			<?php 
						$sect="select * from section";
						$sql_sect=mysqli_query($link, $sect);

					?>
						 <select name="section" class="form-control">
										<option value="0"> --- Select Section --- </option>
										<?php while($data_sec = mysqli_fetch_array( $sql_sect )) 
										{ ?>
										<option value="<?php echo "$data_sec[sect_name]"; ?>"> <?php echo "$data_sec[sect_name]"; ?> </option>
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
				 				<option value="Admin"> Admin </option>
				 				<option value="Originator"> Originator </option>
				 				<option value="Approver"> Approver </option>
				 			</select>
				 		</td>
				 	</tr>
				 	<tr>
				 		<td>Jabatan</td>
				 		<td>:</td>
				 		<td>
				 			<select name="level" class="form-control">
				 				<option value="0"> --- Select Level --- </option>
				 				<option value="1"> Direktur </option>
				 				<option value="2"> Senior Man. </option>
				 				<option value="3"> Manager </option>
				 				<option value="4"> Assist. Man </option>
				 				<option value="5"> Supervisor </option>
				 				<option value="6"> Asst. Sup. </option>
				 				<option value="7"> Leader/Officer </option>
				 				<option value="8"> Foreman/Staff </option>
				 				<option value="9"> Opertor </option>
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


//include 'koneksi.php';

$nama=$_GET['nama'];
$username=$_GET['username'];
$email=$_GET['email'];
$password=$_GET['password'];
$section=$_GET['section'];
$state=$_GET['state'];
$level=$_GET['level'];







$sql="insert into users (id,username,name,email,password,section,state,level) value ('','$username','$nama','$email','$password','$section','$state','$level')";

$res=mysqli_query($link, $sql);

if ($res){
echo"<script>
     document.location='conf_user.php';
     </script>";
}
}
 ?>