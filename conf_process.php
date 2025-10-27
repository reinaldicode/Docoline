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
<h2>Manage Process</h2>

<br />
	<a href="add_process.php" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-plus"></span></a>

<?php







	$sql="select * from process order by proc_name";
	


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
	<td>Process Name</td>
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
		<?php echo "$info[proc_name]";?>
	</td>
	
	
	
	<td>
		<a href="edit_proc.php?id=<?php echo "$info[id_proc]" ?>" class="btn btn-info"><span class="glyphicon glyphicon-edit"></span> Edit </a>
		<a href="del_proc.php?id=<?php echo "$info[id_proc]" ?>" class="btn btn-danger" onClick="return confirm('Delete Process?')"  ><span class="glyphicon glyphicon-remove"></span> Delete </a>
	</td>
	
</tr>
</tbody>
<div>
<?php 
$i++;} 





?> 
</div>