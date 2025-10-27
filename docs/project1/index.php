

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
    <title>Document</title>
</head>
<body>
    <div class="container">
		<div class="w-75 p-5" style="">	
		</div>
	</div>
    <div class="container-fluid">
    	<div class="row">
    		<div class="col"></div>
    		<div class="col-4">
			    <form action="submit.php" method="POST" class="modal-content form-horizontal">
			    	<div class="modal-header">
			    		<h4 class="modal-title">
			    			Forex This Month
			    		</h4>
			    	</div>
			    	<div class="modal-body">
			    		<label for="rupiah" class="col-sm-3"><b>Rupiah </b></label>
			    		<input class="col-sm-8" type="text" name="rupiah" id="rupiah">
			    		<label for="sgd" class="col-sm-3"><b>SGD </b></label>
			    		<input class="col-sm-8" type="text" name="sgd" id="sgd">
			    		<label for="yen" class="col-sm-3"><b>YEN </b></label>
			    		<input class="col-sm-8" type="text" name="yen" id="yen">
			    	</div>
			    	<div class="modal-footer">
			    		<button type="submit" name="submit" class="btn btn-primary">
			    			Update
			    		</button>
			    	</div>
			    </form>
			</div>
			<div class="col"></div>
    	</div>
    </div>
</body>
</html>