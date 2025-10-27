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
    
    


	 <?php require_once('Connections/config.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysqli_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

//mysql_select_db($database_config, $config);
$query_rsData = "SELECT distinct no_doc FROM docu ORDER BY no_doc ASC";
$rsData = mysqli_query($config, $query_rsData) or die(mysqli_error());
$row_rsData = mysqli_fetch_assoc($rsData);
$totalRows_rsData = mysqli_num_rows($rsData);
?>
<!doctype html>

<meta charset="utf-8">

<link rel="stylesheet" href="jquery-ui-1.10.3/themes/base/jquery.ui.all.css">
	<script src="jquery-ui-1.10.3/jquery-1.9.1.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.core.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.widget.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.position.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.menu.js"></script>
	<script src="jquery-ui-1.10.3/ui/jquery.ui.autocomplete.js"></script>
	

<script type="text/javascript">
$(document).ready(function () {

    $('.dis-doc').click(function () {
        $('span.user-id').text($(this).data('id'));
		var Id = $(this).data('id');
     $(".modal-body #id_dis").val( Id );
	 
	 var nama = $(this).data('nama');
     $(".modal-body #nama").val( nama );

      var drf = $(this).data('drf');
     $(".modal-body #drf").val( drf );

       var title = $(this).data('title');
     $(".modal-body #title").val( title );

      var give = $(this).data('give');
     $(".modal-body #qty").val( give );

       var location = $(this).data('location');
     $(".modal-body #location").val( location );

     var pic = $(this).data('pic');
     $(".modal-body #pic").val( pic );

      var no_doc = $(this).data('no_doc');
     $(".modal-body #no_doc").val( no_doc );

      });

});
</script>

<script type="text/javascript">
$(document).ready(function () {

    $('.ret-doc').click(function () {
        $('span.user-id').text($(this).data('id'));
		var Id = $(this).data('id');
     $(".modal-body #id_dis").val( Id );
	 
	 var nama = $(this).data('nama');
     $(".modal-body #nama").val( nama );

      var drf = $(this).data('drf');
     $(".modal-body #drf").val( drf );

       var title = $(this).data('title');
     $(".modal-body #title").val( title );

      var no_doc = $(this).data('no_doc');
     $(".modal-body #no_doc").val( no_doc );

      });

});
</script>

<br />
<br />





<?php
mysqli_free_result($rsData);
?>





    			
    			
    			
	
	

<?php

?>
<br />

<?php
// $sql="select distribusi.*,docu.* from distribusi,docu where no_doc='$no_doc' and distribusi.no_drf=docu.no_drf and no_rev=(select max(no_rev) from docu where no_doc='$no_doc') order by no_rev limit 50";
$sql="SELECT distribusi . * , docu . *
FROM distribusi, docu
WHERE distribusi.no_drf = docu.no_drf
AND docu.no_doc = '$no_doc'";
$res=mysqli_query($link, $sql);
//$rows = mysql_num_rows($res);

echo $sql;


?>
<h1>Document Distribution And Returned List</h1>
<h3>Document No.&nbsp;: <?php Echo $no_doc;?></h3>
<h3>Title &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo $title; ?></h3>
<table class="table table-hover">
<thead bgcolor="#00FFFF">
<tr>
	<td>No</td>
	<td>Rev. No.</td>
	<td>Issue Date</td>
	<td>Issue To</td>
	<td>Location</td>
	<td>Receiver Name</td>
	<td>DC Name</td>
	<td>QTY</td>
	<td>Retrieve Date</td>
	<td>Retrieve From</td>
	<td>Receiver Name</td>
	<td>QTY</td>

	
	
</tr>
</thead>
<?php
$i=1;
while($info = mysqli_fetch_array($res)) 
{ ?>
<tbody>
	<tr>
		<td><?php echo $i;?></td>
		<td><?php echo $info[no_rev] ;?></td>
		<td><?php echo $info[date_give]; ?></td>
		<td><?php echo $info[rev_to];?></td>
		<td><?php echo $info[location];?></td>
		<td><?php echo $info[pic];?></td>
		<td><?php echo $info[receiver]; ?></td>
		<td>
		
			<a data-toggle="modal" data-target="#myModal2" data-id="<?php echo $info[id_dis]?>" 
			data-nama="<?php echo $name?>" data-drf="<?php echo $info[no_drf]?>" data-title="<?php echo $title?>"  data-no_doc="<?php echo $no_doc?>"
			data-give="<?php echo $info[give]?>" data-location="<?php echo $info[location]?>" data-pic="<?php echo $info[pic]?>"
			 class="btn btn-xs btn-success dis-doc" title="Remarks: <?php echo $info[remarks] ?>">
	<span class="glyphicon glyphicon-arrow-right" ></span> <?php echo $info[give];?> </a>
			
		</td>
		<td><?php echo $info[retrieve_date]; ?></td>
		<td><?php echo $info[retrieve_from]; ?></td>
		<td><?php echo $info[receiver] ;?></td>

		<td>
		<?php if ($info[give]<>'-' and $info[retrieve]=='-'){?>
			<a data-toggle="modal" data-target="#myModal3" data-id="<?php echo $info[id_dis]?>" 
			data-title="<?php echo $title?>" data-nama="<?php echo $name?>" data-drf="<?php echo $info[no_drf]?>" data-no_doc="<?php echo $no_doc?>" class="btn btn-xs btn-warning ret-doc" title="Return Document">
	<span class="glyphicon glyphicon-arrow-left" ></span></a>
		<?php }
		else if	($info[give]<>'-' and $info[retrieve]<>'-'){ ?>
			<span class="label label-warning"><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;<?php echo $info[retrieve];?></span>
		<?php }
		?>
		</td>

	</tr>
</tbody>
<div>
<?php 
$i++;} 





?> 
</div>


<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                 <h4 class="modal-title" id="myModalLabel">Distribute Document</h4>

            </div>
            <div class="modal-body">
                <form name="secure_doc" method="POST" action="give.php" enctype="multipart/form-data">

                   	<div class="modal-body">
						
						<input type="hidden" name="id_dis" id="id_dis" class="form-control" value=""/>
						<input type="hidden" name="nama" id="nama" class="form-control" value=""/>
						<input type="hidden" name="drf" id="drf" class="form-control" value=""/>
						<input type="hidden" name="no_doc" id="no_doc" class="form-control" value=""/>
						<input type="hidden" name="title" id="title" class="form-control" value=""/>
						<input type="text" name="location" id="location" placeholder="Location" class="form-control" value=""/>
						<input type="text" name="qty" id="qty" placeholder="QTY" class="form-control">
						
						 <?php 
						$pic="select * from users where state='PIC'";
						$sql_pic=mysqli_query($link, $pic);

						?>
						 <select name="pic" class="form-control">
						<option value="0"> --- Select PIC --- </option>
						<?php while($data_pic = mysqli_fetch_array( $sql_pic )) 
						{ ?>
						<option value="<?php echo "$data_pic[name]"; ?>" > <?php echo "$data_pic[name]"; ?> </option>
						<?php } ?>
						</option>
						</select>
						<textarea  class="form-control" name="remarks" id="remarks" cols="40" rows="10" wrap="physical" ></textarea>
					</div>
					
                    <div class="modal-footer"> <a class="btn btn-default" data-dismiss="modal">Cancel</a>

                        <input type="submit" name="upload" value="Save" class="btn btn-primary">
                    </div>
                </form>
            </div>
           
        </div>
    </div>
</div>

<div class="modal fade" id="myModal3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                 <h4 class="modal-title" id="myModalLabel">Return Document</h4>

            </div>
            <div class="modal-body">
                <form name="secure_doc" method="POST" action="return.php" enctype="multipart/form-data">

                   	<div class="modal-body">
						
						<input type="hidden" name="id_dis" id="id_dis" class="form-control" value=""/>
						<input type="hidden" name="nama" id="nama" class="form-control" value=""/>
						<input type="hidden" name="drf" id="drf" class="form-control" value=""/>
						<input type="hidden" name="no_doc" id="no_doc" class="form-control" value=""/>
						<input type="hidden" name="title" id="title" class="form-control" value=""/>
						<input type="text" name="location" id="location" placeholder="Location" class="form-control" value=""/>
						<input type="text" name="qty" placeholder="QTY" class="form-control">
						
						
									<?php 
						$pic="select * from users where state='PIC'";
						$sql_pic=mysqli_query($link, $pic);

						?>
						 <select name="pic" class="form-control">
						<option value="0"> --- Select PIC --- </option>
						<?php while($data_pic = mysqli_fetch_array( $sql_pic )) 
						{ ?>
						<option value="<?php echo "$data_pic[name]"; ?>"> <?php echo "$data_pic[name]"; ?> </option>
						<?php } ?>
						</option>
						</select>

					</div>
					
                    <div class="modal-footer"> <a class="btn btn-default" data-dismiss="modal">Cancel</a>

                        <input type="submit" name="upload" value="Save" class="btn btn-primary">
                    </div>
                </form>
            </div>
           
        </div>
    </div>
</div>