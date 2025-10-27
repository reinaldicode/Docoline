<?php
$host="localhost";
$user="root";
$password="123456789";
$koneksi=mysqli_connect($host,$user,$password,"doc") or die("Gagal Koneksi Database");
//mysql_select_db("doc");
// write your SQL query here (you may use parameters from $_GET or $_POST if you need them)
$query = mysqli_query($koneksi, 'SELECT section_dept,count(sect_name) as jumlah FROM section group by section_dept');
$table = array();
$table['cols'] = array(
/* Disini kita mendefinisikan fata pada tabel database
* masing-masing kolom akan kita ubah menjadi array
* Kolom tersebut adalah parameter (string) dan nilai (integer/number)
* Pada bagian ini kita juga memberi penamaan pada hasil chart nanti
*/
array('label' => 'section', 'type' => 'string'),
array('label' => 'jumlah', 'type' => 'number')
);
// melakukan query yang akan menampilkan array data
$rows = array();
while($r = mysqli_fetch_assoc($query)) {
$temp = array();
// masing-masing kolom kita masukkan sebagai array sementara
$temp[] = array('v' => $r['section_dept']);
$temp[] = array('v' => (int) $r['jumlah']);
$rows[] = array('c' => $temp);
}
// mempopulasi row tabel
$table['rows'] = $rows;
// encode tabel ke bentuk json
//$jsonTable = json_encode($table);



    require 'jsonwrapper.php';
   $jsonTable = json_encode($table);
//}

// set up header untuk JSON, wajib.
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
// menampilkan data hasil query ke bentuk json
echo $jsonTable;
?> 