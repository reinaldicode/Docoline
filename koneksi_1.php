<?php
$mssqlHost = "192.168.132.36";
$mssqlUser = "admin";
$mssqlPass = "SSItop123!";
$mssqlDB = "doc";
$link = mysqli_connect($mssqlHost,$mssqlUser,$mssqlPass,$mssqlDB) or die ('Tidak dapat melakukan koneksi Server on '.$mssqlHost);
//$db = mysql_select_db($mssqlDB, $link) or die("Tidak dapat menggunakan database");

//if ($link){echo "berhasil";}
?>