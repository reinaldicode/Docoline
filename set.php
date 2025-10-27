<?php 
if (isset($_POST['save'])){

	$jumlah = count($_POST["item"]);
echo $jumlah;



for($i=0; $i < $jumlah; $i++) 
{
    $id=$_POST["item"][$i];
    $name=$_POST["name"][$i];
	$email=$_POST["email"][$i];

echo $id;	
echo $name;
echo $email;



}
}
?>