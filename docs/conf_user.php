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

 ?>
<h2>Manage User</h2>
<form action="" method="GET" >
	<div class="row" style="margin-left=50px">
	<div class="col-sm-4">


	<?php 
		$sect="select sect_name from section";
		$sql_sect=mysqli_query($link, $sect);

	?>

		<select name="tipe" class="form-control">
						<option value="0"> --- Select Type --- </option>
						<option value="Admin"> Admin </option>
						<option value="Originator"> Originator </option>
						<option value="Approver"> Approver </option>
						<option value="PIC"> PIC </option>
						
						</option>
					</select>
		

		 <select name="section" class="form-control">
						<option value="0"> --- Select Section --- </option>
						
						<?php while($data_sec = mysqli_fetch_array( $sql_sect )) 
						{ ?>
						<option value="<?php echo "$data_sec[sect_name]"; ?>"> <?php echo "$data_sec[sect_name]"; ?> </option>
						<?php } ?>
						
					</select>
					
					
					
					
	 <input type="submit" name="submit" value="Show" class="btn btn-primary" />
    </div>
    	<br />
    		<br />
    			<br />
    			
    			
    			
    			
	
	</form>
	</div>
<br />
	<a href="add_user.php" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-plus"></span></a>

<?php
if(isset($_GET['submit'])){





$section=$_GET['section'];
$tipe=$_GET['tipe'];


if ($tipe=="Admin"){
	$sql="select * from users where state='$tipe' order by name limit 50";
	}
	
if ($section=='0'){
	$sql="select * from users where state='$tipe' order by name limit 50";
}	

if ($tipe=='0'){
	$sql="select * from users where section='$section' order by name limit 50";
}	


?>
<br />

<?php

$res=mysqli_query($link, $sql);
//$rows = mysql_num_rows($res);

// echo $sql;


?>
<br />

<table class="table table-hover">
<thead bgcolor="#00FFFF">
<tr>
	<td>No</td>
	<td>Nama User</td>
	<td>Email</td>
	<td>Section</td>
	<td>State</td>
	<td>Action</td>
	
</tr>
</thead>
<?php
$i=1;
while($info = mysqli_fetch_array($res)) 
{ ?>
<tbody>
<tr>
	<td>
		<?php echo $i; ?>
	</td>
	<td>
		<?php echo "$info[name]";?>
	</td>
	<td>
		<?php echo "$info[email]";?>
	</td>
	<td>
		<?php echo "$info[section]";?>
	</td>
	<td>
		<?php echo "$info[state]";?>
	</td>
	
	<td>
		<a href="edit_user.php?id=<?php echo "$info[id]" ?>" class="btn btn-info"><span class="glyphicon glyphicon-edit"></span> Edit </a>
		<a href="del_user.php?id=<?php echo "$info[id]" ?>" class="btn btn-danger" onClick="return confirm('Delete User?')" ><span class="glyphicon glyphicon-remove"></span> Delete </a>
	</td>
	
	
</tr>
</tbody>
<div>
<?php 
$i++;} 
}
?> 
</div>