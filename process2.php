<?php

include 'header.php';
include 'koneksi.php';
extract($_REQUEST);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">


<head profile="http://www.google.com">
	
	<html>
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<?php
			include ('koneksi.php');
			
			
			
				
				if(isset($_POST['upload'])){

					if(empty($_FILES['baru'])){
					die("File tidak bisa diupload");
					}

					$tmp_file = $_FILES['baru']['tmp_name'];
					$nma_file = $_FILES['baru']['name'];
					// unlink("document/".$filelama);

					// if ($type=="WI"){
					if(copy($tmp_file, "WI/".$nma_file)){
					echo "File $nma_file Berhasil Disalin";
					}
					else {
					echo "File gagal ".$type." disalin";
					}
					// }

					// if ($type=="Form"){
					// if(copy($tmp_file, "Form/".$nma_file)){
					// echo "File $nma_file Berhasil Disalin";
					// }
					// else {
					// echo "File gagal ".$type." disalin";
					// }
					// }

					// if ($type=="Procedure"){
					// if(copy($tmp_file, "Procedure/".$nma_file)){
					// echo "File $nma_file Berhasil Disalin";
					// }
					// else {
					// echo "File gagal ".$type." disalin";
					// }
					// }

					}


					// if($status=='Approved')	{
				
				
					// if ($rev=='Cancel'){
					// $query="update docu set  status='Obsolate' where no_drf=$drf";	
					// $hasil=mysql_query($query);	
					// }
					// else {
						$query="update docu set  file='$nma_file' where no_drf=$drf";
						$hasil=mysqli_query($link, $query);
					// }
				
				
				//$hasil=mysql_query($query);
				//echo $query;
				
				

			

				// if ($status=='Secured'){

				// 	if ($nodoc<>'QC-ML-001' and $nodoc<>'QC-ML-002' and $nodoc<>'QC-ML-003' and $nodoc<>'QC-ML-004' and $nodoc<>'QC-ML-005' and
				// 		$nodoc<>'QC-ML-006' and $nodoc<>'QC-ML-007' and $nodoc<>'QC-ML006') 
				// 	{

				// 			$query="update docu set status='Obsolete' where no_drf=$drf";
				
				// 			$hasil=mysql_query($query);
				// 		}
				// }


				
				

					
				
				
				
					
					
					?>

					<script language='javascript'>
							alert('Document Updated');
							document.location='wi_prod.php';
						</script>
					
					<?php
					
				
			
			?>
			
			
			</html>
