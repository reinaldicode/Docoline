<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
<?php 

	$server = "192.168.132.130";
	// $username = "sa";
	// $password = "SSItopadmin";

	$opsi = ["UID" => "sa", "PWD" => "SSItopadmin", "database" => "webformDB"];

	//buka koneksi database
	$conn = sqlsrv_connect($server, $opsi);
	
	if( $conn ) {
     // echo "Connection established.<br />";
	}else{
	     echo "Connection could not be established.<br />";
	     die( print_r( sqlsrv_errors(), true));
	}


	$rupiah = $_POST['rupiah'];
	$sgd = $_POST['sgd'];
	$yen = $_POST['yen'];

	// $sql1 = "UPDATE RateDollarTemp SET 'CurrencyValue' = $rupiah WHERE IDCur = 'IDR'";
	// $sql2 = "UPDATE RateDollarTemp SET 'CurrencyValue' = $sgd WHERE IDCur = 'SGD'";
	// $sql3 = "UPDATE RateDollarTemp SET 'CurrencyValue' = $yen WHERE IDCur = 'YEN'";

	
	// $result2 = sqlsrv_query($conn, $sql2);
	// $result3 = sqlsrv_query($conn, $sql3);

	
	// if( !isset($_POST['submit']) ) {
	// 	die( print_r( sqlsrv_errors(), true) )
	// } else {
	// 	echo "Record Update Successfully";
	// }


	/* Set up the parameterized query. */  
	$tsql = "UPDATE RateDollarTemp   
	         SET CurrencyValue = (?)   
	         WHERE IDCur = (?)";
	 

	/* Assign literal parameter values. */  
	$params1 = array($rupiah, 'IDR');
	$params2 = array($sgd, 'SGD');
	$params3 = array($yen, 'YEN');  

	$result1 = sqlsrv_query($conn, $tsql, $params1);
	$result2 = sqlsrv_query($conn, $tsql, $params2);
	$result3 = sqlsrv_query($conn, $tsql, $params3);
	// $result3 = sqlsrv_query($conn, $sql3);

	/* Execute the query. */  
	// if (sqlsrv_query($conn, $tsql, $params)) {  
	//     echo "Statement executed.\n";  
	// } else {  
	//     echo "Error in statement execution.\n";  
	//     die(print_r(sqlsrv_errors(), true));  
	// }  

	if ( $result1 || $result2 || $result3 ) {
		echo "Statement executed.\n";
	} else {
		echo "Error in statement execution.\n";  
	 	die(print_r(sqlsrv_errors(), true));
	}

	/* Free connection resources. */  
	sqlsrv_close($conn); 

?>


</body>
</html>