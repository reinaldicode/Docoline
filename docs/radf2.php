<?php

include "koneksi.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">


<head profile="http://www.google.com">

<link href="bootstrap/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
		<link href="bootstrap/css/bootstrap-responsive.min.css" media="all" type="text/css" rel="stylesheet">
		<link href="bootstrap/css/facebook.css" media="all" type="text/css" rel="stylesheet">
		
		<script src="bootstrap/js/bootstrap.min.js"></script>
		<script src="bootstrap/js/bootstrap-dropdown.js"></script>
		<script src="bootstrap/js/facebook.js"></script>
	
	<link rel="stylesheet" href="bootstrap/css/datepicker.css">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
		 <script src="bootstrap/js/jquery.min.js"></script>
        <script src="bootstrap/js/bootstrap-datepicker.js"></script>
        <style type="text/css">
			        	.container-non-responsive {
			  margin-left: auto;
			  margin-right: auto;
			  padding-left: 15px;
			  padding-right: 15px;
			  width: 1170px;
}
        </style>


</head>
<body>


<?php
		$sql_doc="select docu.*,users.name from docu,users where no_drf='$drf' and docu.user_id=users.username";
		$hasil_doc=mysqli_query($link, $sql_doc);
		//echo $sql_doc;

?>

<div class="row" style="margin-left:0px;">
	<div class="col-xs-3">
		
	</div>
	<div class="col-xs-6">
		<h4>REVIEW AND APPROVAL DOCUMENT FORM (RADF)</h4>
	</div>
	<div class="col-xs-3">
		<table border="">
			<tr>
				<td>Document No. &nbsp; DC-055</td>
				
			</tr>
			<tr><td>Rev. No 00</td></tr>
			<tr>
				<td> Eff. Date 	April, 01 2009 </td>
			</tr>
		</table>
		
	</div>
</div>

	<div class="row">
	<br />
	<div class="col-xs-4" style="margin-left:-100px;">
	

	
	<?php
  
   	while ($data_doc=mysqli_fetch_array ($hasil_doc))
   	{
   		$drf=$data_doc[no_drf];
   		$tanggal=$data_doc[tgl_upload];
   		$no_doc=$data_doc[no_doc];
   		$title=$data_doc[title];
   		$rev=$data_doc[no_rev];
   		$nama=$data_doc[name];
   		$iso9001=$data_doc[iso9001];
		$iso14001=$data_doc[iso14001];
	
   		
   	}
	?>	

	<table  class=""style="float:left;" border="0"  cellpadding="" cellspacing="" width="" >



<tr>
<td>No. Drf</td><td> : </td>
 <td>
  	<a href="document/<?php echo "$upd"; ?>" target="_blank">
  	&nbsp;<?php echo $drf;?> </a></td>
 </tr>

<tbody>


<tr>
<td>Issue Date</td><td> : </td>
	<td height="30px">
     &nbsp;<?php echo $tanggal ;?></td>
	
</tr>
<tr>
<td>No. Document</td><td> : </td>
	<td height="30px" >
	
     &nbsp;<?php echo $no_doc;?>
   
      </td>
	
</tr>
<tr>
<td>Doc. Title</td><td> : </td>
	<td height="30px" >
	
      &nbsp;<?php echo $title;?>
   
    </td>
	
</tr>
<tr>
<td>No. Rev.</td><td> : </td>
	<td height="30px" >
	
     &nbsp;<?php echo $rev;?>
   
      </td>
	
</tr>
</tbody>
</table>
<br />
<br />

</div>


	
	</div>

	<div class="row" >

	<div class="col-xs-5" style="margin-top:15px;margin-left:-100px;">

		<table  border="1" align="center" cellpadding="0" cellspacing="0" width="655px">
		<tr> 
		<td  valign="top" rowspan="2"> 
	  	<table  border="0" cellspacing="0" cellpadding="0" >
        <tr> 
       	  <td width="655"><font size="1" face="arial">&nbsp;&nbsp;Reason for issue/cancel/revise document ( alasan mengeluarkan/membatalkan/merevisi dokumen)</font></td>
        </tr>
        <tr> 
        	<td valign="bottom"><p><font size="1" face="arial"><p>&nbsp;</p>&nbsp;&nbsp;</font>
            <p>&nbsp;</p> <p>&nbsp;</p> <p>&nbsp;</p>
                <table border="0" cellpadding="0" cellspacing="0">
                <tr> 
                	<td ><font size="1" face="arial">&nbsp;&nbsp;Proposed by :</font></td>
                    <td ><div align="center"><font size="1" face="arial">&nbsp;&nbsp;<?php echo $nama;?></font></div></td>
                </tr>
                <tr> 
                    <td colspan="2"><div align="center">
					<font size="1" face="arial">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					(Originator)</font></div></td>
                </tr>
                </table>
           </td>
        </tr>
      </table>
      </table>

      </div>

      <div class="col-xs-5" style="margin-top:-213px;margin-left:580px;"> 
      	<table border="1">
      	<tr>
      		 <td  valign="top"> <p><font size="1" face="arial">&nbsp;&nbsp;This document is appropriate 
              with requirment of (dokumen ini sesuai dengan persyaratan) :<strong>       
              </strong></font><br> 
              <font size="1" face="arial">  
              &nbsp;&nbsp;<input name="ch1" type="checkbox" <?php if ($iso9001=='1'){echo "checked";} ?> disabled >
              ISO 9001<br>
              &nbsp;&nbsp;<input name="ch2" type="checkbox" <?php if ($iso14001=='1'){echo "checked";} ?> disabled>
              ISO 14000<br>
			  &nbsp;&nbsp;<input name="ch3" type="checkbox" disabled >
              OHSAS / SMK3<br>
              &nbsp;&nbsp;<input name="ch4" type="checkbox" disabled>
              Indonesian Law<br>
              </font>
    		
      	</tr>

      	<tr> 
	<td valign="top"><p>&nbsp;&nbsp;Transfer system doc :<br>
               
              &nbsp;&nbsp;<input name="ch7" type="checkbox" disabled>
              Sequence training<br>
              &nbsp;&nbsp;<input name="ch8" type="checkbox" disabled> 
              Direct training<br>
              &nbsp;&nbsp;<input name="ch9" type="checkbox"  checked disabled>
              RADF </p>
	</td>
	</tr>

	 <tr>
    <td height="63">&nbsp;</td>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td width="137" height="65">&nbsp;</td>
    <td colspan="2">&nbsp;</td>
  </tr>

      </table>
      </div>
      </div>
      

      <div class="row">
      	<div class="col-xs-4">
      		<table border="1" height="300px" width="200px">
      			
      		</table>
      	</div>
      </div>


	

	