<?php

include('header.php');
?>

<br />
<br />
<br />
<br />

</head>

<?php

//include 'index.php';
include 'koneksi.php';

 ?>
<h3>Procedure List</h3>
<form action="" method="GET" >
	<div class="col-sm-4">


	<?php 
		$sect="select * from section";
		$sql_sect=mysqli_query($link, $sect);

	?>
		 <select name="section" class="form-control">
						<option value="0"> --- Select Section --- </option>
						<?php while($data_sec = mysqli_fetch_array( $sql_sect )) 
						{ ?>
						<option value="<?php echo "$data_sec[id_section]"; ?>"> <?php echo "$data_sec[sect_name]"; ?> </option>
						<?php } ?>
						</option>
					</select>
					
		
		<select name="status" class="form-control">
						<option value="0"> --- Select Status --- </option>
						
						<option value="Secured" selected> Approved </option>
						<option value="Review"> Review </option>
						<option value="Pending"> Pending </option>
						
						</option>
					</select>			
					
					<input type='hidden' name='by' value='no_drf'>
	 <input type="submit" name="submit" value="Show" class="btn btn-primary" />
    </div>
    	<br />
    		<br />
    			<br />
    			
	</form>

<?php
if (isset($_GET['submit'])){





$section=$_GET['section'];

$status=$_GET['status'];

if ($section=='0'){
	$sql="select * from docu where doc_type='Procedure' and status='Secured' order by $by";	
}
else{
	$sql="select * from docu where section='$section' and doc_type='Procedure' and status='$status' order by $by limit 50";
}
	
	// echo $sql;
?>
<br /><br />

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
	<td>Date</td>
	<td>No Document</td>
	<td>No Rev.</td>
	<td><a href='procedure_login_sort.php?section=<?php echo $section; ?>&by=no_drf&status=<?php echo $status; ?>&submit=Show'>drf</a></td>
	<td><a href='procedure_login_sort.php?section=<?php echo $section; ?>&by=title&status=<?php echo $status; ?>&submit=Show'>Title</a></td>
	<td>Process</td>
	<td>Section</td>
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
		<?php echo "$info[tgl_upload]";?>
	</td>
	<td>
		<?php echo "$info[no_doc]";?>
	</td>
	<td>
		<?php echo "$info[no_rev]";?>
	</td>
	<td>
		<?php echo "$info[no_drf]";?>
	</td>
	<td>
		<?php echo "$info[title]";?>
	</td>
	<td>
		<?php echo "$info[process]";?>
	</td>
	<td>
		<?php echo "$info[section]";?>
	</td>
	<td>
	<a href="detail.php?drf=<?php echo $info[no_drf];?>&no_doc=<?php echo $info[no_doc];?>" class="btn btn-xs btn-info" title="lihat detail"><span class="glyphicon glyphicon-search" ></span></a>
	<a href="lihat_approver.php?drf=<?php echo $info[no_drf];?>" class="btn btn-xs btn-info" title="lihat approver"><span class="glyphicon glyphicon-user" ></span></a>
	<a href="radf.php?drf=<?php echo $info[no_drf];?>&section=<? echo $info[section]?>" class="btn btn-xs btn-info" title="lihat RADF"><span class="glyphicon glyphicon-eye-open" ></span> </a>
	<?php if ($state=='Admin' or $state="Originator"){?>
	<a href="edit_doc.php?drf=<?php echo $info[no_drf];?>" class="btn btn-xs btn-primary" title="Edit Doc"><span class="glyphicon glyphicon-pencil" ></span> </a>
	<a href="del_doc.php?drf=<?php echo $info[no_drf];?>" class="btn btn-xs btn-danger" onClick="return confirm('Delete document <?php echo $info[no_doc]?>?')" title="Delete Doc"><span class="glyphicon glyphicon-remove" ></span> </a>
	<?php if ($info[status]=='Approved') { ?>
	
	<a data-toggle="modal" data-target="#myModal2" data-id="<?php echo $info[no_drf]?>" data-lama="<?php echo $info[file]?>" data-status="<?php echo $info[status]?>" class="btn btn-xs btn-success sec-file" title="Secure Document">
	<span class="glyphicon glyphicon-play" ></span></a>
	<?php }} ?>	
	</td>
	
	
</tr>
</tbody>
<div>
<?php 
$i++;} 
}
?> 
</div>