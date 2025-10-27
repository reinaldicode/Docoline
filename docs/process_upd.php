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
					extract($_REQUEST);
					if(empty($_FILES['baru'])){
					die("File tidak bisa diupload");
					}

					$tmp_file = $_FILES['baru']['tmp_name'];
					$nma_file = $_FILES['baru']['name'];

					if (($nodoc=='QC-ML-001' or $nodoc=='QC-ML-002' or $nodoc=='QC-ML-003' or $nodoc=='QC-ML-004' or $nodoc=='QC-ML-005' or
						$nodoc=='QC-ML-006' or $nodoc=='QC-ML-007' or $nodoc=='QC-ML008') or $section=='DCU' )
					{
						if(copy($tmp_file, "document/".$nma_file)){
					echo "File $nma_file Berhasil Disalin";
					}
					else {
					echo "File gagal ".$type." disalin";
					}

					}

					// $tmp_file = $_FILES['baru']['tmp_name'];
					// $nma_file = $_FILES['baru']['name'];
					// unlink("document/".$filelama);
					else {

					if ($type=="WI"){
					if(copy($tmp_file, "WI/".$nma_file)){
					echo "File $nma_file Berhasil Disalin";
					}
					else {
					echo "File gagal ".$type." disalin";
					}
					}

					if ($type=="Form"){
					if(copy($tmp_file, "Form/".$nma_file)){
					echo "File $nma_file Berhasil Disalin";
					}
					else {
					echo "File gagal ".$type." disalin";
					}
					}

					if ($type=="Procedure"){
					if(copy($tmp_file, "Procedure/".$nma_file)){
					echo "File $nma_file Berhasil Disalin";
					}
					else {
					echo "File gagal ".$type." disalin";
					}
					}

					}


					if($status=='Approved')	{
				
				
					if ($rev=='Cancel'){
					 $query="update docu set  status='Obsolate' where no_drf=$drf";	
					$hasil=mysqli_query($link, $query);	
					}
					else {
						 $query="update docu set  status='Secured', file='$nma_file' where no_drf=$drf";
						$hasil=mysqli_query($link, $query);
					}
				
				
				// $hasil=mysql_query($query);
				//echo $query;
				
				}

			

				if ($status=='Secured'){

					if (($nodoc<>'QC-ML-001' and $nodoc<>'QC-ML-002' and $nodoc<>'QC-ML-003' and $nodoc<>'QC-ML-004' and $nodoc<>'QC-ML-005' and
						$nodoc<>'QC-ML-006' and $nodoc<>'QC-ML-007' and $nodoc<>'QC-ML008' and $nodoc<>'DC-022') or $type<>'External'  ) 
					{
							echo $query="update docu set status='Obsolete' where no_drf=$drf";
				
							$hasil=mysqli_query($link, $query);
							
						}
						else if  ($section=='DCU') { echo "berhasil";}
						else {
							echo "berhasil";
						}
				}


				
				

					echo $query;
				
				
				
					
					
					?>

					<script language='javascript'>
							alert('Document Updated');
							document.location='search.php';
						</script>
					
					<?php
					
				}
			
			?>
			
			
			</html>
