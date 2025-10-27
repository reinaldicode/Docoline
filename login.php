<?php
// include('login_proc.php'); // Includes Login Script
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
	<title>:.  Online Doc. Control  .:</title>	
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
		
	
	<style> 
body {
    background-image: url("images/blue.png");
    background-color: #cccccc;
}
</style>
</head>

<body>

<div id="content">
	<div id="content_inside">
	  	<div id="content_inside_main">
			
			<p>
			<div class="container" style="background-image='image/blue.png';"> 
		
		
		<div class="clr"></div>
  </div>
            </p>
<p>&nbsp;</p>
	  </div>	
		</div>	
</div>


<div class="container" style="background-image=url('images/blue.png');">

<div class="row" style="margin-top:20px">
    <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
		


		<form role="form" action="login_proc.php" method="post">
		<?php if (isset($_GET['error'])) { ?>
      	      <div class="alert alert-danger" role="alert">
				  <?=$_GET['error']?>
			  </div>
			  <?php } ?>
              <?php if (isset($_GET['success'])) { ?>
      	      <div class="alert alert-success" role="alert">
				  <?=$_GET['success']?>
			  </div>
			  <?php } ?>
			<fieldset>
				<h2 style="text-align: center;">Login to Document Control</h2>
				<hr class="colorgraph">
				<div class="form-group">
					<label>Username:</label>
					<input type="text" id="username" name="username" class="form-control input-lg" placeholder="Please input username ">
				</div>
				<div class="form-group">
					<label>Password:</label>
                    <input type="password" id="password" name="password" class="form-control input-lg" placeholder="Please input password">
				</div>				
				<hr class="colorgraph">
					<h4 style="text-align: center;">Please use password as for login into your computer</h4>
				<hr class="colorgraph">
				<div class="row">
					<div class="col-xs-3 col-sm-3 col-md-3">
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6">
                        <input type="submit" class="btn btn-lg btn-primary btn-block" name="submit" value="Log In">
					</div>
				</div>
			</fieldset>
		</form>


	</div>
</div>
<br />



</body>

</html>
