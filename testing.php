<?php 
include('koneksi.php');
?>

<div class="">
	<div class="row">
		<div class="col-xs-12 well well-lg">
		<?php $tgl = date('d-m-Y'); ?>
		Latest Approved Document(Today &nbsp;<?php echo $tgl;?>):
		<table>
		<tr>
		<?php 
		$query="SELECT no_doc, file FROM docu WHERE doc_type = 'Procedure' AND final = '$tgl' AND STATUS = 'Review' ORDER BY no_drf LIMIT 3 ";
		$res=mysqli_query($link, $query)or die(mysqli_error());
	
	  ?>
		<td>Procedure</td><td>:</td><td>  <?php while ($data=mysqli_fetch_array($res)){ ?><a href="Procedure/<?php echo "$data[file]"; ?>"> <?php echo $data[no_doc];?> </a>,  <?php }?></td>
		</tr>
		<tr>
		<?php 
		$query="SELECT no_doc, file FROM docu WHERE doc_type = 'WI' AND final = '$tgl' AND STATUS = 'Review' ORDER BY no_drf LIMIT 3 ";
		$res=mysqli_query($link, $query)or die(mysqli_error());
	
	  ?>
		<td>Work Instruction</td><td>:</td><td> <?php while ($data=mysqli_fetch_array($res)){ ?> <a href="WI/<?php echo "$data[file]"; ?>"> <?php echo $data[no_doc];?> </a>,  <?php }?></td>
		</tr>
		<tr>
		<?php 
		$query="SELECT no_doc, file FROM docu WHERE doc_type = 'Form' AND final = '$tgl' AND STATUS = 'Review' ORDER BY no_drf LIMIT 3 ";
		$res=mysqli_query($link, $query)or die(mysqli_error());
	
	  ?>
		<td>Form</td><td>:</td><td><?php while ($data=mysqli_fetch_array($res)){ ?> <a href="Form/<?php echo "$data[file]"; ?>"> <?php echo $data[no_doc]; ?> </a>,  <?php }?></td>
		</tr>
		</table>
		</div>
	</div>
</div>