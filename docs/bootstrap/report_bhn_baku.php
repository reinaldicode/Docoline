 <?php 
//Connects your database
$dbhost = '192.168.132.130';
$dbuser = 'sa';
$dbpass = 'SSItopadmin';
$db = 'BeaApp';
$connect_db = mssql_connect ( $dbhost, $dbuser, $dbpass ) or die(mssql_error());
mssql_select_db ( $db, $connect_db ) or die(mssql_error());

?>
<head profile="http://www.global-sharp.com">
	
	
	
	<link href="bootstrap/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
		<link href="bootstrap/css/bootstrap-responsive.min.css" media="all" type="text/css" rel="stylesheet">
		<link href="bootstrap/css/facebook.css" media="all" type="text/css" rel="stylesheet">
		
		<script src="bootstrap/js/bootstrap.min.js"></script>
		<script src="bootstrap/js/bootstrap-dropdown.js"></script>
		<script src="bootstrap/js/facebook.js"></script>
	
	<link rel="stylesheet" href="bootstrap/css/datepicker.css">
	<link rel="stylesheet" href="bootstrap/css/bootstrap-select.min.css">
        <link rel="stylesheet" href="bootstrap/css/bootstrap.css">
		 <script src="bootstrap/js/jquery.min.js"></script>
        <script src="bootstrap/js/bootstrap-datepicker.js"></script>

	
	
</head>





<?php

include 'head.php';
include 'koneksi.php';

 ?>
<h3>View Report Bahan Baku</h3>
<form action="" method="GET" >
	<div class="col-sm-4">
	<h4>Select Period</h4>
		 <select name="awal" class="form-control">
						<option value="0"> --- Select Month --- </option>
						<option value="1"> January </option>
						<option value="2"> February </option>
						<option value="3"> March </option>
						<option value="4"> April </option>
						<option value="5"> May </option>
						<option value="6"> June </option>
						<option value="7"> July </option>
						<option value="8"> August </option>
						<option value="09"> September </option>
						<option value="10"> October </option>
						<option value="11"> November </option>
						<option value="12"> Desember </option>

						</option>
					</select>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;To

					<select name="akhir" class="form-control">
						<option value="0"> --- Select Month --- </option>
						<option value="1"> January </option>
						<option value="2"> February </option>
						<option value="3"> March </option>
						<option value="4"> April </option>
						<option value="5"> May </option>
						<option value="6"> June </option>
						<option value="7"> July </option>
						<option value="8"> August </option>
						<option value="09"> September </option>
						<option value="10"> October </option>
						<option value="11"> November </option>
						<option value="12"> Desember </option>

						</option>
					</select>

					<br />
	 <select name="tahun" class="form-control">
						<option value="0"> --- Select Year --- </option>
						<option value="2010"> 2010 </option>
						<option value="2011"> 2011 </option>
						<option value="2012"> 2012 </option>
						<option value="2013"> 2013 </option>
						<option value="2014"> 2014 </option>
						<option value="2015"> 2015 </option>
						<option value="2016"> 2016 </option>
						<option value="2017"> 2017 </option>
						
						</option>
					</select>	
					<br />
	<input type="checkbox" name="qty" value="true"> Quantity = 0
		<br />
	 <input type="submit" name="submit" value="Show" class="btn btn-primary" />
    </div>
    	<br />
    		<br />
    			<br />
    			<br />
    		<br />
    			<br />
    			<br />
    		<br />
    			<br />
    			<br />
    		<br />
    			<br />
    			
	 
	 <input type="hidden" name="pagenum" value="<?php if ((!isset($_GET['pagenum'])) || (!is_numeric($_GET['pagenum'])) || ($_GET['pagenum'] < 1)) { echo '1'; }
else {  echo $_GET['pagenum']; } ?>">
	</form>

<?php
if (isset($_GET['submit'])){

//This checks to see if there is a page number, that the number is not 0, and that the number is actually a number. If not, it will set it to page number to 1.
if ((!isset($_GET['pagenum'])) || (!is_numeric($_GET['pagenum'])) || ($_GET['pagenum'] < 1)) { $pagenum = 1; }
else { $pagenum = $_GET['pagenum']; }



$tahun=$_GET['tahun'];
$awal=$_GET['awal'];
$akhir=$_GET['akhir'];
$qual=isset($_GET['qty']);


if($qual=='true'){

	$sql="select distinct ViewBahanBaku.*, BahanBaku_D.SaldoAwal SaldoAwal, BahanBaku_D.SaldoAkhir from [ViewBahanBaku],[BahanBaku_D],[BahanBaku_H] where ViewBahanBaku.kode_brg=BahanBaku_H.kode_brg and BahanBaku_H.idTransaction=BahanBaku_D.idTransaction and ViewBahanBaku.Tahun='$tahun' and ViewBahanBaku.Periode>='$awal' and ViewBahanBaku.Periode<='$akhir' order by kode_brg";
}
else {
	$sql="select distinct ViewBahanBaku.*, BahanBaku_D.SaldoAwal SaldoAwal, BahanBaku_D.SaldoAkhir from [ViewBahanBaku],[BahanBaku_D],[BahanBaku_H] where ViewBahanBaku.kode_brg=BahanBaku_H.kode_brg and BahanBaku_H.idTransaction=BahanBaku_D.idTransaction and ViewBahanBaku.Tahun='$tahun' and ViewBahanBaku.Periode>='$awal' and ViewBahanBaku.Periode<='$akhir' and ViewBahanBaku.Qty<>0 order by kode_brg";
}
?>
<br /><br />
<form action="export.php" method="post">
<input type="hidden" value="<?php echo $sql?>" name="select">
<input type="submit" name="submit2" value="Export to Excel" class="btn btn-success">
</form>
<?

$res=mssql_query($sql);
$rows = mssql_num_rows($res);

//echo $sql;

$page_rows = 50; 


$last = ceil($rows/$page_rows); 


if (($pagenum > $last) && ($last > 0)) { $pagenum = $last; }


$max = ($pagenum - 1) * $page_rows;

if($qual=='true'){
$result2 = mssql_query("select distinct top $page_rows ViewBahanBaku.*, BahanBaku_D.SaldoAwal SaldoAwal, BahanBaku_D.SaldoAkhir from [ViewBahanBaku],[BahanBaku_D],[BahanBaku_H] where ViewBahanBaku.kode_brg=BahanBaku_H.kode_brg and BahanBaku_H.idTransaction=BahanBaku_D.idTransaction and ViewBahanBaku.Tahun='$tahun' and ViewBahanBaku.Periode>='$awal' and ViewBahanBaku.Periode<='$akhir' and ViewBahanBaku.kode_brg not in (select top $max kode_brg from [ViewBahanBaku] order by kode_brg asc) order by kode_brg asc") or die(mssql_error()); 
}
else {
$result2 = mssql_query("select distinct top $page_rows ViewBahanBaku.*, BahanBaku_D.SaldoAwal SaldoAwal, BahanBaku_D.SaldoAkhir from [ViewBahanBaku],[BahanBaku_D],[BahanBaku_H] where ViewBahanBaku.kode_brg=BahanBaku_H.kode_brg and BahanBaku_H.idTransaction=BahanBaku_D.idTransaction and ViewBahanBaku.Tahun='$tahun' and ViewBahanBaku.Periode>='$awal' and ViewBahanBaku.Periode<='$akhir' and ViewBahanBaku.Qty<>0 and ViewBahanBaku.kode_brg not in (select top $max kode_brg from [ViewBahanBaku] order by kode_brg asc) order by kode_brg asc") or die(mssql_error()); 	
}
?>
<br />
<table class="table table-hover">
<thead bgcolor="#00FFFF">
<tr>
	<td>kode</td>
	<td>Nama Barang</td>
	<td>Satuan</td>
	<td>Saldo Awal</td>
	<td>Pemasukan</td>
	<td>Pengeluaran</td>
	<td>Saldo Akhir</td>
	<td>Stock Opname</td>
	<td>Selisih</td>
	
	<td>Keterangan</td>
</tr>
</thead>
<?php

while($info = mssql_fetch_array( $result2 )) 
{ ?>
<tbody>
<tr>
	<td>
		<?php echo "$info[kode_brg]";?>
	</td>
	<td>
		<?php echo "$info[nm_barang]";?>
	</td>
	<td>
		<?php echo "$info[satuan]";?>
	</td>
	<td>
		<?php echo "$info[SaldoAwal]";?>
	</td>
	<td>
		<?php echo "$info[Qty]";?>
	</td>
	<td>
		<?php echo "$info[Pengeluaran]";?>
	</td>
	<td>
		<?php echo "$info[SaldoAkhir]";?>
	</td>
	<td>
		<?php echo "$info[StockOpname]";?>
	</td>
	<td>
		<?php $qty=$info['SaldoAkhir']; $peng=$info['StockOpname']; $selisih=$qty-$peng; echo "$selisih";?>
	</td>
	<td>
		<?php if($info['SaldoAkhir']==$info['StockOpname']) {echo "SESUAI";} else {echo "TIDAK SESUAI";}?>
	</td>
</tr>
</tbody>
<div>
<?php } 
echo "<p>";


echo " --Page $pagenum of $last-- <p>";

// First we check if we are on page one. If we are then we don't need a link to the previous page or the first page so we do nothing. If we aren't then we generate links to the 


if ($pagenum == 1) { } 
else 
{
echo " <a href='{$_SERVER['PHP_SELF']}?pagenum=1&awal=$awal&akhir=$akhir&tahun=$tahun&submit=Show' class='btn btn-primary'> <<-First</a> ";
echo " ";
$previous = $pagenum-1;
echo " <a href='{$_SERVER['PHP_SELF']}?pagenum=$previous&awal=$awal&akhir=$akhir&tahun=$tahun&submit=Show' class='btn btn-primary'> <-Previous</a> ";
} 

//just a spacer
echo " ---- ";

//This does the same as above, only checking if we are on the last page, and then generating the Next and Last links
if ($pagenum == $last) 
{
} 
else {
$next = $pagenum+1;
echo " <a href='{$_SERVER['PHP_SELF']}?pagenum=$next&awal=$awal&akhir=$akhir&tahun=$tahun&submit=Show' class='btn btn-primary'>Next -></a> ";
echo " ";
echo " <a href='{$_SERVER['PHP_SELF']}?pagenum=$last&awal=$awal&akhir=$akhir&tahun=$tahun&submit=Show' class='btn btn-primary'>Last ->></a> ";
}
}
?> 
</div>