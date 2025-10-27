<?php
if($_GET['func'] == "device" && isset($_GET['func'])) { 
   device($_GET['drop_var']); 
}

function device($drop_var)
{  
    include_once('koneksi.php');
	//include "connt_db.php";
	$result2 = mysqli_query($link, "SELECT distinct Process FROM docu WHERE device = '$drop_var' and Process<>'0' and Process<>'' order by Process") 
	or die(mysqli_error());
	
	echo '
	<select name="proc" id="proc" class="form-control">
	      <option value=" " disabled="disabled" selected="selected"> --- Select Process --- </option>';

		   while($drop_3 = mysqli_fetch_array( $result2 )) 
			{
			  echo '<option value="'.$drop_3['Process'].'">'.$drop_3['Process'].'</option>';
			}
	
	echo '</select> ';
    //echo '<input type="submit" name="submit" value="Submit" />';
}
?>