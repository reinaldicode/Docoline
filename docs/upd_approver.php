<?php

include('header.php');
include('koneksi.php');
extract($_REQUEST);

?>

<br />
<br />
<br />
<br />
<br />
<br />

<div class="row">
<div class="col-xs-1"></div>
<div class="col-xs-6 well well-lg">
<h2>Select Reviewer</h2>

<form action="" method="post" enctype="multipart/form-data">
<table class="table table-hover">
<?php 




	$sql2="select * from users where state='approver' order by section,level";
	// echo $sql2;
	$q2=mysqli_query($link, $sql2);
	while ($row2=mysqli_fetch_array($q2)) { 
		echo "

	<div class='input-group'>
					<tr>
						<td>
						<span class='input-group-addon2'>
						<input type='checkbox' name='item[]' id='item[]' value='$row2[username]|$row2[name]|$row2[email]'>
						
						</td>
						<td>$row2[name] &nbsp;</td>
						<td>$row2[section] &nbsp;</td>
						<td><input type='hidden' name='email[]' id='email[]' value='$row2[email]' ></input></td>
						<td><input type='hidden' name='name[]' id='name[]' value='$row2[name]' ></input></td>
						<td><input type='hidden' name='id_doc' id='id_doc' value='$id_doc' ></input></td>
						
					</tr> 
											</div>
											";
	 }
	 
 ?>
			<td><input type='hidden' name='type' id='type' value=<?php echo $type;?> ></input></td>
			<td><input type='hidden' name='nodoc' id='nodoc' value=<?php echo $nodoc;?> ></input></td>
			<td><input type='hidden' name='title' id='title' value=<?php echo $title;?>  ></input></td>
</table>
<input type="submit" value="save" class="btn btn-success" name="save">
</form>


</div>

<?php 
if (isset($_POST['save'])){

	$jumlah = count($_POST["item"]);
echo $jumlah;

$id_doc=$_POST['id_doc'];




	//require_once("class.smtp.php");
	//require_once("class.phpmailer.php");
	require 'PHPMailer/PHPMailerAutoload.php';

	$mail = new PHPMailer();

	// setting
	$mail->IsSMTP();// send via SMTP
	include 'smtp.php';
	// $mail->Host     = "mail.ssi.global.sharp.co.jp"; // SMTP servers
	//$mail->Port = "3080"; 
	// $mail->SMTPAuth = true;// turn on SMTP authentication
	// $mail->Username = "int\gzb310003";// SMTP username
	// $mail->Password = "20132013";// SMTP password

	// pengirim
	$mail->setFrom('dc_admin@ssi.sharp-world.com');
	//$mail->From     = "dc_admin@ssi.sharp-world.com";
	$mail->FromName = "Admin Document Online System";


	for($i=0; $i < $jumlah; $i++) 
{
    $id=$_POST["item"][$i];
    $name=$_POST["name"][$i];
	$email=$_POST["email"][$i];
	$type=$_POST["type"];
	// $iso=$_POST["iso"];

//echo $id;	
$pecah=explode('|', $id);

echo "<br />";
//echo $pecah[0];
$id=$pecah[0];
echo "<br />";
//echo $pecah[1];
echo "<br />";
//echo $pecah[2];
//echo $id_doc;
	
	$sql_in="insert into rev_doc(id,id_doc,nrp,status,tgl_approve,reason) values (0,'$id_doc','$id','Review','-','')";
 $q=mysqli_query($link, $sql_in); 
 // echo $sql_in;

	// penerima
	$mail->AddAddress("$pecah[2]","$pecah[1]");

}



	//$mail->AddCC($mail8,$name8);
	//$mail->AddCC($mail9);
	/**$mail->AddAddress($mail8,$name8);
	$mail->AddCC($mail9);
	$mail->AddCC($user_mail); **/

	// kirim balik
	//$mail->AddReplyTo("irfan@ssi.sharp-world.com","Aryo Sanjaya");

	//$mail->WordWrap = 50;                              // set word wrap
	//$mail->AddAttachment(getcwd()."/document/Review/".$jenis_doc."/".$file_name);      // attachment
//$mail->AddAttachment(getcwd() . "/file2.zip", "file_kedua.zip");
	$mail->IsHTML(true);  

	$mail->Subject  =  "Document to Review" ;
	$mail->Body     =  "Attention Mr./Mrs. : Reviewer <br /> This following <span style='color:green'>".$type."</span> Document need to be <span style='color:blue'>reviewed</span> <br /> No. Document : ".$nodoc."<br /> Document Title : ".$title."<br />Please Login into <a href='192.168.132.15/document'>Document Online System</a>, Thank You";
	$mail->AltBody  =  "This research is supported by MIS";

	if(!$mail->Send())
	{
		echo "Message was not sent <p>";
		echo "Mailer Error: " . $mail->ErrorInfo;
	}	
		
		





?>
						<script language='javascript'>
							alert('Approver Updated');
							document.location='my_doc.php';
						</script>
						
						
				<?php


}
?>