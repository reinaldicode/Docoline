
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
	<title>Login Management System</title>	
	<link rel="shortcut icon" href="image/favicon.ico" />
	<link rel="stylesheet" href="style.css" type="text/css" media="screen" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="en-gb" />
	<meta http-equiv="imagetoolbar" content="false" />
	<meta name="author" content="Christopher Robinson" />
	<meta name="copyright" content="Copyright (c) Christopher Robinson 2005 - 2007" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />	
	<meta name="last-modified" content="Sat, 01 Jan 2007 00:00:00 GMT" />
	<meta name="mssmarttagspreventparsing" content="true" />	
	<meta name="robots" content="index, follow, noarchive" />
	<meta name="revisit-after" content="7 days" />
	
	
			
	<link href="bootstrap/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
		<link href="bootstrap/css/bootstrap-responsive.min.css" media="all" type="text/css" rel="stylesheet">
		<link href="bootstrap/css/facebook.css" media="all" type="text/css" rel="stylesheet">
		<script src="bootstrap/js/jquery.min.js"></script>
		<script src="bootstrap/js/bootstrap.min.js"></script>
		<script src="bootstrap/js/bootstrap-dropdown.js"></script>
		<script src="bootstrap/js/facebook.js"></script>
		<link href="css_login.css" media="all" type="text/css" rel="stylesheet">
	
	<style> 
body {
    background-image: url("images/silver.png");
    background-color: #cccccc;
}
</style>
</head>

<body>

<div id="content">
	<div id="content_inside">
	  	<div id="content_inside_main">
			
			<p>
			<div class="container" style="background-image='image/xp.jpg';"> 
		
		
		<div class="clr"></div>
  </div>
            </p>
<p>&nbsp;</p>
	  </div>	
		</div>	
</div>


<div class="container" style="background-image=url('images/xp.jpg');">

<div class="row" style="margin-top:20px">
    <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
		<form role="form" action="" method="post">
			<fieldset>
			<div class="row">
				<div class="col-xs-8"><h2>Catering Control System</h2></div>
				<div class="col-xs-3"></div>
				<div class="col-xs-3"></div>
				</div>
				<hr class="colorgraph">

				<div >
                    <input type="hidden" value="003721" name="id" id="id" class="form-control input-lg" visible="false" placeholder="Username ">
				</div>
				<div class="form-group">
                    <input type="password" name="pass" id="password" class="form-control input-lg" placeholder="Tempelkan ID Card Anda ke Scanner!" autofocus>
				</div>

				
				<div class="form-group">
				<input type="hidden" name="cate" value="internal">
				</div>
				<hr class="colorgraph">
				<div class="row">
					<div class="col-xs-3 col-sm-3 col-md-3">
                        
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6">
                        <input type="submit"   class="btn btn-lg btn-success btn-block"  name="submit" value="Sign In">
					</div>
					
				</div>
			</fieldset>
		</form>
	</div>
</div>
<br />
<div class="login-text">
			<div class="ctr style14"></div>
			
			
			<p align="center">&nbsp;</p>
			</div>
</div>


</body>

</html>

<?php 



		if (isset($_POST['submit'])) {
			extract($_REQUEST);

			$username=$_POST['id'];
			$password=$_POST['pass'];
			$serverName = "192.168.132.130"; //serverName\instanceName
			$empidnya="";
			$idcardnya="";
				// TMS //

				$connectionTMS = array( "Database"=>"db_TMS", "UID"=>"sa", "PWD"=>"SSItopadmin");
				$connTMS = sqlsrv_connect( $serverName, $connectionTMS);

				if( $connTMS ) {
				     // echo "Connection established.<br />";
				}else{
				     echo "Connection could not be established.<br />";
				     die( print_r( sqlsrv_errors(), true));
				}
				
				$sql="select emp.cardid, emp.empid from tbtcardemp emp where cardid='$password' and empid<>'' ";
				$res_new=sqlsrv_query($connTMS,$sql);


				$rownum=sqlsrv_num_rows($res_new);
				$data=sqlsrv_fetch_array( $res_new, SQLSRV_FETCH_ASSOC );
				$empidnya=$data["empid"];
				$idcardnya=$data["cardid"];
				$dateday=date("Y-m-d");

				if ($res_new) {
				   $rownum = sqlsrv_has_rows( $res_new );
				   if ($rownum === true)
				    {
				    	/*$_SESSION['login_user']=$username; // Initializing Session
						header("location:edit.php?submit=Show"); // Redirecting To Other Page
*/
						$sql2="select emp.empid from tbarawdata_fasta emp where emp.empid='$empidnya' and datet='$dateday'";
						$res_new2=sqlsrv_query($connTMS,$sql2);
						$rownum2=sqlsrv_num_rows($res_new2);

				  if ($res_new2) {
				   $rownum2 = sqlsrv_has_rows( $res_new2 );
				   if ($rownum2 === true)
				    {
				    	// $error = $idcardnya;
							echo $error;

							?>
					<script language='javascript'>
							// alert('Anda tidak berhak');
							// document.location='index.php';
						</script>
						<audio autoplay>
								<source src="sound/false.mp3" type="audio/mpeg">
						</audio>
						<font size="10" color="red"> <?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; echo $empidnya; ?> Tidak Berhak Makan Siang! </font>
					<?php


				    }
				    else {
				    		// $error = $idcardnya;
							echo $error;

							?>

					<script language='javascript'>
							// alert('Anda berhak');
							// document.location='index.php';
						</script>
						<audio autoplay>
								<source src="sound/true.mp3" type="audio/mpeg">
						</audio>
							<font size="10" color="green"><?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; echo $empidnya; ?> Berhak Makan Siang!</font>

					<?php
				    }


							// $error = "Kartu Terdaftar ";
							// echo $error;
					}
				    }				   	
				   else 
				   {
				   	$error = "Kartu Tidak Terdaftar";
					echo $error;	
				   }
				      
				}


		}



?>