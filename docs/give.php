<?php 
include "koneksi.php";

			// $id_doc=$_Post['drf'];
			// $id_dis=$_Post['id_dis'];
			// $pic=$_Post['pic'];
			// $drf=$_Post['drf'];
			// $nama=$_Post['nama'];
			// $no_doc=$_Post['no_doc'];
			// $qty=$_Post['qty'];

				$tglsekarang = date('d-m-Y');
			$sql = "UPDATE distribusi SET give='$qty',location='$location' ,pic='$pic',receiver='$nama', date_give='$tglsekarang',remarks='$remarks' WHERE id_dis=$id_dis and no_drf=$drf" ; 
						// echo $sql;
						mysqli_query($link, $sql); 			// Mengeksekusi syntak UPDDate nya ..
						
					

			require_once("class.smtp.php");
			require_once("class.phpmailer.php");

				$mail = new PHPMailer();

				// setting
				$mail->IsSMTP();// send via SMTP
				$mail->IsSMTP();// send via SMTP
				$mail->Host     = "mail.ssi.global.sharp.co.jp"; // SMTP servers
				//$mail->Port = "3080"; 
				$mail->SMTPAuth = true;// turn on SMTP authentication
				$mail->Username = "int\gzb310003";// SMTP username
				$mail->Password = "20132013";// SMTP password

				// pengirim
				$mail->From     = "dc_admin@ssi.sharp-world.com";
				$mail->FromName = "Admin Document Online System System";

				$sql2="SELECT DISTINCT users.name, users.email FROM  users WHERE name ='$pic'";
				$res2=mysqli_query($link, $sql2)or die(mysqli_error());  
				mysqli_num_rows($res2);
				// echo $sql2;
				
			if ($res2>0){	 

			while ($data2=mysqli_fetch_row($res2))
			{


				// penerima
				 $mail->AddAddress($data2[1],$data2[0]);
				 // echo $data2[1];
				 // echo $data2[0];
				}

				
				$mail->WordWrap = 50;                              // set word wrap
				//$mail->AddAttachment(getcwd()."/document/".$file_name);      // attachment
	//$mail->AddAttachment(getcwd() . "/file2.zip", "file_kedua.zip");
				$mail->IsHTML(true);                               // send as HTML
			
				//$link="http://www.ssi.global.sharp.co.jp/AglSystemOpto/";
				$mail->Subject  = "Document Distributed" ;
				$mail->Body     =  "Document : ".$no_doc."<br />Title : ".$title." <br /> Distributed to : ".$pic." <br /> Amount : ".$qty." copy <br /> Location : ".$location."";
				//$mail->AltBody  =  "This research is supported by MIS";

				if(!$mail->Send())
				{
			   		echo "Message was not sent <p>";
			   		echo "Mailer Error: " . $mail->ErrorInfo;
			   		echo $sql2;
   					exit;
				}
			
			
			
		}



			
			




										
				?>
						<script language='javascript'>
							alert('Document Was Distributed');
							document.location='detail_dist.php?drf=<?php echo $drf;?>&no_doc=<?php echo $no_doc;?>&title=<?php echo $title;?>';
						</script>
						
						
