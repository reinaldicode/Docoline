 <br />
 <br />
 <br />
 <br />
 <?php

			include "koneksi.php";
			
			// $user=$_POST['user'];
			// $pass=$_POST['pass'];
			// $lama=$_POST['lama'];
			// $baru=$_POST['baru'];
			// $conf=$_POST['conf'];
			if ($lama==$pass){
				if($baru==$conf)
				{
				$sql2="Update users set password='$conf' where username='$user'";
			$res2=mysqli_query($link, $sql2)or die(mysqli_error()); 
			// echo $sql2; 	
				}
			}

				
		

			
		
			
			
			
			
				?>
				<script language='javascript'>
							alert('Password Updated');
							document.location='my_doc.php';
						</script>
