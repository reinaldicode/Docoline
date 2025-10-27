<?php
//connect to your database
  $link = mysqli_connect("192.168.132.130","root","123456789","doc");
  //mysql_select_db("doc");
//harus selalu gunakan variabel term saat memakai autocomplete,
//jika variable term tidak bisa, gunakan variabel q
$term = trim(strip_tags($_GET['term']));
 
// $qstring = "SELECT id_master,part,part_name,unit FROM master_price WHERE part LIKE '".$term."%'";


$tgl=date('Y-m-d');

	$pecah2 = explode("-", $tgl);
					$date2 = $pecah2[2];
					$month2 = $pecah2[1];
					$year2 =  $pecah2[0];



$qstring = "SELECT * from docu where no_doc LIKE '".$term."%'";
//query database untuk mengecek tabel Country 
$result = mysqli_query($link, $qstring);
$row=mysqli_num_rows($result);

// if($row<1) {
//   if($month2=='01'){$month2=12;$year2=$year2-1;}
//   // else{$month2=$month2-1;}
//   if($month2=='02'){$month2='01';}
//   if($month2=='03'){$month2='02';}
//   if($month2=='04'){$month2='03';}
//   if($month2=='05'){$month2='04';}
//   if($month2=='06'){$month2='05';}
//   if($month2=='07'){$month2='06';}
//   if($month2=='08'){$month2='07';}
//   if($month2=='09'){$month2='08';}
//   if($month2=='10'){$month2='09';}
//   if($month2=='11'){$month2='10';}
//   if($month2=='12'){$month2='11';}
//   $qstring = "SELECT id_master,part,part_name,master_price.qty,master_price.unit,min_order.jumlah,min_order.packing,master_price.unit_price 
// FROM master_price,min_order WHERE master_price.part=min_order.kode_material and mid(date,6,2)='$month2' and left(date,4)='$year2' and part LIKE '".$term."%'";
// //query database untuk mengecek tabel Country 
// $result = mysql_query($qstring);
// }

 
while ($row = mysqli_fetch_array($result))
{
    $row['value']=htmlentities(stripslashes($row['no_doc']));
   
    // $row['satuan']=htmlentities(stripslashes($row['unit']));
    // $row['jumlah']=htmlentities(stripslashes($row['jumlah']));
    // $row['harga']=htmlentities(stripslashes($row['unit_price']));
    // $row['id']=(int)$row['id_input'];
//buat array yang nantinya akan di konversi ke json
    $row_set[] = $row;
}
//data hasil query yang dikirim kembali dalam format json
echo json_encode($row_set);
?>