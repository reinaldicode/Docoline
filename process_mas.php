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
						$nodoc=='QC-ML-006' or $nodoc=='QC-ML-007' or $nodoc=='QC-ML-008' or $nodoc=='DC-022' or $nodoc=='DC-023') or $section=='DCU' )
					{
						if(copy($tmp_file, "document/".$nma_file)){
					echo "File $nma_file Berhasil Disalin";
					}
					else {
					echo "File gagal diupload ".$tmp_file." disalin";
					}

					}

					// $tmp_file = $_FILES['baru']['tmp_name'];
					// $nma_file = $_FILES['baru']['name'];
					// unlink("document/".$filelama);
					

				
				
					
					
					?>

					<script language='javascript'>
							alert('Document Updated');
							document.location='search.php';
						</script>
					
					<?php
					
				}
			
			?>
			
			
			</html>