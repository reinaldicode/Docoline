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
<h2>Manage Device</h2>

<br />
	<a href="add_device.php" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-plus"></span></a>

<?php







	$sql="select * from device order by group_dev,name limit 50";
	


?>
<br />

<?php

$res=mysqli_query($link, $sql);
//$rows = mysql_num_rows($res);

//echo $sql;


?>
<br />

<table class="table table-hover">
<thead bgcolor="#00FFFF">
<tr>
	<td>No</td>
	<td>Device Name</td>
	<td>Group</td>
	<td>Status</td>
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
		<?php echo "$info[group_dev]";?>
	</td>
	<td>
	<?php if ($info['status']=="Aktif"){?><span class="label label-success">
		<?php echo "$info[status]";?></span> <?php } 
		else {?>
		<span class="label label-danger">
		<?php echo "$info[status]";?></span>
		 <?php }?>
	</td>
	
	<td>
		<a href="edit_device.php?id=<?php echo "$info[id_device]" ?>" class="btn btn-info"><span class="glyphicon glyphicon-edit"></span> Edit </a>
		<a href="del_device.php?id=<?php echo "$info[id_device]" ?>" class="btn btn-danger" onClick="return confirm('Delete Device?')"  ><span class="glyphicon glyphicon-remove"></span> Delete </a>
	</td>
	
</tr>
</tbody>
<div>
<?php 
$i++;} 





?> 
</div>