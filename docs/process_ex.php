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

					if ( $drf < 12967)
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


					$sql="update docu set file=$nma_file where no_drf=$drf";
					$result=mysqli_query($link, $sql);
					
				
				

					// echo $query;
				
				
				
					
					
					?>

					<script language='javascript'>
							alert('Document Updated');
							document.location='search.php';
						</script>
					
					<?php
					
				}
			
			?>
			
			
			</html>
