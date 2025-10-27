<?php 

	$server = "192.168.132.130";
	// $username = "sa";
	// $password = "SSItopadmin";

	$opsi = ["UID" => "sa", "PWD" => "SSItopadmin", "database" => "webformDB"];

	//buka koneksi database
	$conn = sqlsrv_connect($server, $opsi);
	
	if ( $conn ) {
		echo "OK";
	} else {
		echo "KO";
	}

?>