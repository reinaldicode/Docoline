<?php

include('header.php');
?>
<!DOCTYPE html>
<head>

 
<script src="js/highcharts.js"></script>
 <script src="js/exporting.js"></script>

<script type="text/javascript" src="bootstrap/js/jquery.min.js"></script>
</head>






<br />
<br />
<br />
<br />


 
  




	
		<script type="text/javascript">

 var chart1; // globally available
 $(document).ready(function() {
 chart1 = new Highcharts.Chart({
 chart: {
 renderTo: 'container',
 type: 'column'
 },
 title: {
 text: 'Grafik Jumlah Dokumen Aktif'
 },
 xAxis: {
 categories:
 			['Section'] 
 },
 yAxis: {
 title: {
 text: 'Dokumen '
 }
 },
 series:
 [

<?php
include 'koneksi.php';
$sql = "select DISTINCT section as section from docu order by section"; 
$query = mysqli_query($link, $sql) or die(mysqli_error());
while ($ret = mysqli_fetch_array($query)) {
 $section = $ret['section'];
 //$tanggal = $ret['tanggal'];
 $sql_jumlah = "select distinct count(section) As jumlah from docu where section='$section' and section<>'Manual' and status<>'Obsolate'";

 $query_jumlah = mysqli_query($link, $sql_jumlah) or die(mysqli_error());
 while ($data = mysqli_fetch_array($query_jumlah)) {
 $jumlah = $data['jumlah'];
 }
 ?>
 {
 name: '<?php 	
 		echo $section;
 		?>',
 data: [<?php echo $jumlah; ?>]
 },
<?php } ?>
 ]
 });
 });
 </script>
	<div class="row">
	<div id='container' class="col-xs-12 well well-lg">
	</div>
</div>

</body>
</html>