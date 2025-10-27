<?php include "form_awal.php"; 
extract($_REQUEST); ?>
<br />







<div class="row">

<div class="col-xs-4 well well-lg">
 <h2>Select Section</h2>

				<form action="" method="GET">
				 <table >
				 

				 	<tr>
				 	<?php 
					$sect="select * from section order by sect_name";
					$sql_sect=mysqli_query($link, $sect);
						?>
				 		<td>Section</td>
				 		<td>:</td>
				 		<td>
				 			<select name="section" class="form-control">
				 				<option value="0"> --- Select Section --- </option>
				 				<?php while($data_sec = mysqli_fetch_array( $sql_sect )) 
						{ ?>
						<option value="<?php echo "$data_sec[id_section]"; ?>"> <?php echo "$data_sec[sect_name]"; ?> </option>
						<?php } ?>
				 			</select>
				 		</td>
				 	</tr>

				 	<tr>
				 		<td>Status</td>
				 		<td>:</td>
				 		<td>
				 			<select name="status" class="form-control">
						<option value="0"> --- Select Status --- </option>
						
						<option value="Secured" selected> Approved </option>
						<option value="Review"> Review </option>
						<option value="Pending"> Pending </option>
						
						</option>
					</select>			
				 		</td>
				 	</tr>
				 	<tr>
				 		<td>Category</td>
				 		<td>:</td>
				 		<td>
				 			<select name="category" class="form-control">
						<option value="0"> --- Select Category --- </option>
						
						<option value="Internal" selected> Internal </option>
						<option value="External"> External </option>
						
						
						</option>
					</select>			
				 		</td>
				 	</tr>
				 	<tr>
				 		<td></td>
				 		<td></td>
				 		<td><input type='hidden' name='by' value='no_drf'>
				 			<input type="submit" value="Show" name="submit" class="btn btn-info">
				 		</td>
				 	</tr>
				</form>
				 </table>
				 </div>

 </div>

 <?php
if (isset($_GET['submit'])){





$section=$_GET['section'];
$status=$_GET['status'];

if ($section=='Engineering'){
	$sql="select * from docu where section='$section' and doc_type='Form' and status='$status' and category='$category' order by $by";
}
else {
	$sql="select * from docu where section='$section' and doc_type='Form' and status='$status' and category='$category' order by $by";	
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
<h1> Work Instruction's List For Section: <strong><?php echo $section;?></strong></h1>
<table class="table table-hover">
<thead bgcolor="#00FFFF">
<tr>
	<td>No</td>
	<td>Date</td>
	<td><a href='form_other_awal.php?section=<?php echo $section; ?>&by=No_doc&status=<?php echo $status; ?>&category=<?php echo $category;?>&submit=Show'>No. Document</a></td>
	<td>No Rev.</td>
	<td><a href='form_other_awal.php?section=<?php echo $section; ?>&by=no_drf&status=<?php echo $status; ?>&category=<?php echo $category?>&submit=Show'>drf</a></td>
	<td><a href='form_other_awal.php?section=<?php echo $section; ?>&by=title&status=<?php echo $status; ?>&category=<?php echo $category?>&submit=Show'>Title</a></td>
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
	<?php if ($info['no_drf']>12967){$tempat=$info['doc_type'];} else {$tempat='document';}?>
	<a href="<?php echo $tempat; ?>/<?php echo "$info[file]"; ?>" >
		<?php echo "$info[title]";?>
		</a>
	</td>
	<td>
		<?php echo "$info[process]";?>
	</td>
	<td>
		<?php echo "$info[section]";?>
	</td>
	<td>
	<a href="detail.php?drf=<?php echo $info['no_drf'];?>&no_doc=<?php echo $info['no_doc'];?>&log=1" class="btn btn-xs btn-info" title="lihat detail"><span class="glyphicon glyphicon-search" ></span> </a>
	
	<a href="radf.php?drf=<?php echo $info['no_drf'];?>&section=<?php echo $info['section']?>&log=1" class="btn btn-xs btn-info" title="lihat RADF"><span class="glyphicon glyphicon-eye-open" ></span> </a>	
	
	</td>
	
	
</tr>
</tbody>
<div>
<?php 
$i++;} 


}


?> 