<?php
$log = '';
if ($log==1){include "index.php";} else
{include("header.php");}
include("koneksi.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">



	
	
	
<style type="text/css">
<!--
body {
	background-color: #FFFFFF;
}
.style13 {font-size: 11px}
. {font-size: 8px; }

-->

</style>	
</head>
<br /><br />
<br /><br />
<body>
<div id="content">
	<div id="content_inside">
  	  <div id="content_inside_main">
		<h1>Approver List</h1>
			<p><br />
		
		<script>
function goBack() {
    window.history.back()
}
</script>

<button class="btn btn-primary" onclick="goBack()"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;Back</button>
<br />
	
	
	    	
				<br />
				<?php
				
				 
				  
				   $sql="select users.*, rev_doc.* from users,rev_doc where rev_doc.id_doc='$drf' and rev_doc.nrp=users.username";
				  //order by device asc limit $awal,$dataperhal";
				  // echo $sql;
				  $hasil= mysqli_query($link, $sql)or die("ada error sql:".mysqli_error());
			
				   ?>
<style type="text/css">
<!--
.style1 {font-size: 12px}
.style4 {font-weight: bold}
.style5 {font-weight: bold}
-->
</style>

			<table border="0" cellpadding="3" cellspacing="3" width="780" class="table-responsive">
			<?php
				if (($state==1 or $state==7) or ($level=42 or $level=52 or $level=62))
				{
				?>
			<tr>
			 <td colspan="13" >
			<a href="upd_approver.php?id_doc=<?php echo $drf;?>&nodoc=<?php echo $nodoc?>&title=<?php echo $title?>&type=<?php echo $type?>" title="tambah approver" class="btn btn-primary btn-lg" ><i class="glyphicon glyphicon-plus"></i></a>
			</td>
			</tr>
			</table>
			<?php
			}
			?>
	<table border="0" cellpadding="3" cellspacing="3" width="780" class="table table-hover table-bordered">
<tr>
<thead>	
 <td width="5" height="50" class="btn-primary btn-small" ><div align="center" class="">Number</div></td>
  <td width="140" height="50" class="btn-primary btn-small"><div align="center" class="">Approver Name</div></td>
  <td width="59" height="50" class="btn-primary btn-small"><div align="center" class="">Approval Status</div></td>
  <td width="59" height="50" class="btn-primary btn-small col-sm-5"><div align="center" class="">Reason </div></td>
    <td width="59" height="50" class="btn-primary btn-small"><div align="center" class="">Section </div></td>
    <td width="59" height="50" class="btn-primary btn-small"><div align="center" class="">Approval Date </div></td>
  <td width="40" height="50"  class="btn-primary btn-small"colspan="1"><div align="center" class="">Action</div></td>
</thead>
  </tr>

<tbody>
<?php
  	$no=1;
   	while ($data=mysqli_fetch_array($hasil))
   	{
?>

<tr>
	<td ><div align="justify" class="">
      <?php echo "$no"; ?></div></td>
	<td><div align="justify" class="">
      <?php echo "$data[name]"; ?></div></td>
	<td><div align="justify" class="">

      <?php if ($data['status']=='Review'){?>
	<span class="label label-info"><?php }?>
	<?php if ($data['status']=='Pending'){?>
	<span class="label label-warning"><?php }?>
	<?php if ($data['status']=='Approved'){?>
	<span class="label label-Success"><?php }?>
		<?php echo "$data[status]";?>
		</span>

      </div></td>
	<td><div align="justify" class="">
      <?php echo "$data[reason]"; ?></div></td>
	  <td><div align="justify" class="">
      <?php echo "$data[section]"; ?></div></td>
	  <td><div align="justify" class="">
      <?php echo "$data[tgl_approve]"; ?></div></td>
       
	  <td width="10"><div align="justify"><span class="">
	  <?php
		if ($state=='Admin' or $state=='Originator')
		{
		?>
		
		<a href="del_approver.php?id=<?php echo $data[8];?>" title="Delete approver" class="btn btn-danger btn-xs" onClick="return confirm('Delete Approver?')"><span class="glyphicon glyphicon-remove"> </span> &nbsp; Delete </button>
    
		<?php
		}

		if (($data[8]!="-" and $data[8]!='') and $state==1)
		{ ?>

		
		<a href="add_remark.php?id_app=<?php echo $data[1];?>&id_dok=<?php echo $data[2];?>" title="add remark" class="btn btn-data btn-xs" ><span class="glyphicon glyphicon-plus"> </span> &nbsp; remark </button>
    

		<?php }

		?>
		</span></div></td>
</tr>
<?php
   	$no++;
	}
?>
</tbody>
</table>	
         
            </p>
      </div>
	</div>	
</div>
</body>

</html>

<br />
<br />
<br />
<br />
