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

	if(isset($_POST['upload']))
	{
		if(!empty($_FILES['baru']))
		{
			$tmp_file = $_FILES['baru']['tmp_name'];
			$nma_file = $_FILES['baru']['name'];
			// unlink("document/".$filelama);

			if ($type=="WI")
			{
				if(copy($tmp_file, "WI/".$nma_file))
				{
					echo "File $nma_file Berhasil Disalin";
				}
				else
				{
					echo "File gagal ".$type." disalin";
				}
			}

			if ($type=="Form")
			{
				if(copy($tmp_file, "Form/".$nma_file))
				{
					echo "File $nma_file Berhasil Disalin";
				}
				else
				{
					echo "File gagal ".$type." disalin";
				}
			}

			if ($type=="Procedure")
			{
				if(copy($tmp_file, "Procedure/".$nma_file))
				{
					echo "File $nma_file Berhasil Disalin";
				}
				else
				{
					echo "File gagal ".$type." disalin";
				}
			}
			$query="UPDATE docu SET file='$nma_file' WHERE no_drf='$drf'";
	
			$hasil=mysqli_query($link, $query);
			//echo $query;
			
			if ($hasil)
			{
				?>
				<script language='javascript'>
						alert('File changed');
						document.location='search.php';
					</script>
				<?php			
			}
			else
			{
				echo "<center>";echo "No Database Connection";			
			}
		}
		else
		{
			die("File tidak bisa diupload");			
		}
			
		if(empty($_FILES['masterbaru']))
		{
			die("File tidak bisa diupload");
		}
		else
		{
			$tmp_file2 = $_FILES['masterbaru']['tmp_name'];
			$nma_file2 = $_FILES['masterbaru']['name'];
			
			if(copy($tmp_file2, "master/".$nma_file2))
			{
				echo "File $nma_file2 Berhasil Disalin";
				// unlink("master/".$lama);
			}
			else
			{
				echo "File gagal disalin";
			}
		}		
	}
			
	//echo $sql2;		
?>
</html>
