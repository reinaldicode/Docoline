<?php
//**************************************
//     Page load dropdown results     //
//**************************************
function getTierOne()
{
	include"koneksi.php";
	$result = mysqli_query($link, "SELECT DISTINCT group_dev FROM device") 
	or die(mysqli_error());

	  while($tier = mysqli_fetch_array( $result )) 
  
		{
		   echo '<option value="'.$tier['group_dev'].'">'.$tier['group_dev'].'</option>';
		}

}

//**************************************
//     First selection results     //
//**************************************
if($_GET['func'] == "section" && isset($_GET['func'])) { 
   section($_GET['drop_var']); 
}

function section($drop_var)
{  
    //include_once('koneksi.php');
	include"koneksi.php";
	//include "connt_db.php";
	$result = mysqli_query($link, "SELECT id_device,name,kode FROM device WHERE group_dev='$drop_var'") 
	or die(mysqli_error());
	
	echo '<select name="device" id="device" class="form-control">
	      <option value="-" selected="selected"> --- Select Device --- </option>
	      <option value="General production" > General production </option>
	      ';

		   while($drop_2 = mysqli_fetch_array( $result )) 
			{
			  echo '<option value="'.$drop_2['name'].'">'.$drop_2['name'].'</option>';
			}
	
	echo '</select> ';
    //echo '<input type="submit" name="submit" value="Submit" />';
}



if($_GET['func'] == "device" && isset($_GET['func'])) { 
   device($_GET['drop_var2']); 
}

function device($drop_var2)
{  
    //include_once('koneksi.php');
	include"koneksi.php";
	//include "connt_db.php";
	$result2 = mysqli_query($link, "SELECT distinct Process FROM docu WHERE device = '$drop_var2' and process<>'0' and process<>'' and doc_type='Form' order by process") 
	or die(mysqli_error());
	//echo $result2;
	echo '
	<select name="proc" id="proc" class="form-control">
	      <option value="-"  selected="selected"> --- Select Process --- </option>';

		   while($drop_3 = mysqli_fetch_array( $result2 )) 
			{
			  echo '<option value="'.$drop_3['Process'].'">'.$drop_3['Process'].'</option>';
			}
	
	echo '</select> ';
    //echo '<input type="submit" name="submit" value="Submit" />';
}


?>